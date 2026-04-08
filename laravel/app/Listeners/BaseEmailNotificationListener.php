<?php

namespace App\Listeners;

use App\Models\Email;
use App\Models\EmailTemplate;
use App\Settings\GeneralSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Abstrakte Basisklasse für alle E-Mail-Benachrichtigungs-Listener.
 *
 * Implementiert ShouldQueue, damit die Listener in die Queue eingereiht werden.
 * $afterCommit = true stellt sicher, dass der Listener erst ausgeführt wird,
 * nachdem die aktuelle Datenbanktransaktion vollständig committed wurde –
 * so sind alle Beziehungen (z. B. Felder einer Anfrage) garantiert gespeichert.
 */
abstract class BaseEmailNotificationListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Listener erst nach dem DB-Commit ausführen.
     * Löst das Race-Condition-Problem: z. B. inquiry->fields werden erst
     * nach Inquiry::create() via attach() gespeichert.
     */
    public bool $afterCommit = true;

    /** Anzahl Wiederholungsversuche bei Fehler */
    public int $tries = 3;

    /** Wartezeit in Sekunden zwischen den Versuchen */
    public int $backoff = 60;

    /**
     * Prüft ob E-Mail-Benachrichtigungen aktiviert sind.
     */
    protected function notificationsEnabled(?GeneralSettings $settings): bool
    {
        return !($settings && !$settings->email_notifications_enabled);
    }

    /**
     * Lädt das E-Mail-Template anhand des Slugs.
     * Fällt auf das Standard-Template aus den Settings zurück.
     */
    protected function loadTemplate(string $slug, ?GeneralSettings $settings): ?EmailTemplate
    {
        $template = EmailTemplate::where(EmailTemplate::slug, $slug)
            ->where(EmailTemplate::is_active, true)
            ->first();

        if (!$template && $settings?->default_email_template_id) {
            $template = EmailTemplate::find($settings->default_email_template_id);
        }

        return $template;
    }

    /**
     * Erstellt einen E-Mail-Datensatz, verknüpft Dokumente aus dem Template
     * und stellt den Versand via SendEmailJob in die Queue.
     *
     * @param string $templateSlug  Slug des EmailTemplate-Eintrags
     * @param string $toEmail       Empfänger-E-Mail
     * @param string $toName        Empfänger-Name
     * @param array  $variables     Platzhalter-Werte für Betreff / Body
     * @param array  $extraData     Zusätzliche Felder für den Email-Datensatz (z. B. customer_id, rental_id, metadata)
     */
    protected function createEmail(
        string $templateSlug,
        string $toEmail,
        string $toName,
        array  $variables,
        array  $extraData = []
    ): ?Email {
        $settings = app(GeneralSettings::class);

        if (!$this->notificationsEnabled($settings)) {
            Log::info("E-Mail-Benachrichtigungen deaktiviert. Kein Versand für Template '{$templateSlug}'.");
            return null;
        }

        $template = $this->loadTemplate($templateSlug, $settings);

        if (!$template) {
            Log::warning("Kein aktives E-Mail-Template für Slug '{$templateSlug}' gefunden.");
            return null;
        }

        $subject     = $this->replaceVariables($template->subject, $variables);
        $contentHtml = $this->replaceVariables($template->body_html, $variables);
        $bodyText    = $template->body_text
            ? $this->replaceVariables($template->body_text, $variables)
            : null;

        // Inhalt in das zentrale E-Mail-Layout einbetten
        $bodyHtml = $this->wrapInLayout($contentHtml, $subject, $toName, $template->reply_to);

        /** @var Email $email */
        $email = Email::create(array_merge([
            Email::direction         => Email::DIRECTION_OUTBOUND,
            Email::status            => Email::STATUS_DRAFT,
            Email::from_email        => $template->from_email ?? config('mail.from.address'),
            Email::from_name         => $template->from_name ?? config('mail.from.name'),
            Email::to_email          => $toEmail,
            Email::to_name           => $toName,
            Email::reply_to          => $template->reply_to,
            Email::subject           => $subject,
            Email::body_html         => $bodyHtml,
            Email::body_text         => $bodyText,
            Email::email_template_id => $template->id,
        ], $extraData));

        // Dokument-Anhänge aus der Template-Konfiguration verknüpfen
        $templateDocuments = $template->documents;
        if ($templateDocuments->isNotEmpty()) {
            $email->documents()->attach($templateDocuments->pluck('id')->toArray());
        }

        return $email;
    }

    /**
     * Bettet den E-Mail-Inhalt in das zentrale Blade-Layout ein.
     * Dadurch haben alle ausgehenden E-Mails denselben Header/Footer.
     *
     * @param string      $content       Gerenderter HTML-Inhalt des DB-Templates (nach Variablen-Ersatz)
     * @param string      $subject       Betreff der E-Mail (für <title>)
     * @param string      $recipientName Empfänger-Name für die Grußzeile
     * @param string|null $replyTo       Optionale Reply-To-Adresse für den Footer
     */
    protected function wrapInLayout(
        string  $content,
        string  $subject,
        string  $recipientName,
        ?string $replyTo = null
    ): string {
        return view('emails.layout', [
            'content'        => $content,
            'subject'        => $subject,
            'recipientName'  => $recipientName,
            'replyToAddress' => $replyTo ?? config('mail.from.address'),
            'appUrl'         => config('app.url'),
            'logoUrl'        => app(GeneralSettings::class)->getLogoUrl(),
        ])->render();
    }

    /**
     * Ersetzt {{variable}} und {{ variable }}-Platzhalter in einem Text.
     */
    protected function replaceVariables(string $text, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $text = str_replace('{{' . $key . '}}', (string) $value, $text);
            $text = str_replace('{{ ' . $key . ' }}', (string) $value, $text);
        }

        return $text;
    }
}

