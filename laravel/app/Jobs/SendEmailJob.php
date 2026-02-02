<?php

namespace App\Jobs;

use App\Models\Email;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * The email model instance.
     */
    protected Email $email;

    /**
     * Create a new job instance.
     */
    public function __construct(Email $email)
    {
        $this->email = $email;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Prüfe ob E-Mail bereits gesendet wurde
        if ($this->email->status === Email::STATUS_SENT) {
            Log::info("E-Mail {$this->email->id} wurde bereits gesendet");
            return;
        }

        try {
            // Sende die E-Mail
            Mail::send([], [], function ($message) {
                // Absender setzen
                $message->from(
                    $this->email->from_email,
                    $this->email->from_name ?? config('mail.from.name')
                );

                // Empfänger setzen
                $message->to(
                    $this->email->to_email,
                    $this->email->to_name
                );

                // CC hinzufügen
                if ($this->email->cc) {
                    $ccAddresses = array_map('trim', explode(',', $this->email->cc));
                    foreach ($ccAddresses as $ccAddress) {
                        $message->cc($ccAddress);
                    }
                }

                // BCC hinzufügen
                if ($this->email->bcc) {
                    $bccAddresses = array_map('trim', explode(',', $this->email->bcc));
                    foreach ($bccAddresses as $bccAddress) {
                        $message->bcc($bccAddress);
                    }
                }

                // Reply-To setzen
                if ($this->email->reply_to) {
                    $message->replyTo($this->email->reply_to);
                }

                // Betreff setzen
                $message->subject($this->email->subject);

                // HTML-Body setzen
                if ($this->email->body_html) {
                    $message->html($this->email->body_html);
                }

                // Text-Body setzen
                if ($this->email->body_text) {
                    $message->text($this->email->body_text);
                }

                // Custom Headers setzen
                if ($this->email->headers && is_array($this->email->headers)) {
                    foreach ($this->email->headers as $key => $value) {
                        $message->getHeaders()->addTextHeader($key, $value);
                    }
                }

                // Message-ID für Threading
                if ($this->email->in_reply_to) {
                    $message->getHeaders()->addTextHeader('In-Reply-To', $this->email->in_reply_to);
                }

                if ($this->email->references) {
                    $message->getHeaders()->addTextHeader('References', $this->email->references);
                }

                // Anhänge hinzufügen
                if ($this->email->attachments && is_array($this->email->attachments)) {
                    foreach ($this->email->attachments as $attachment) {
                        if (isset($attachment['path']) && file_exists(storage_path($attachment['path']))) {
                            $message->attach(
                                storage_path($attachment['path']),
                                [
                                    'as' => $attachment['name'] ?? basename($attachment['path']),
                                    'mime' => $attachment['mime'] ?? 'application/octet-stream',
                                ]
                            );
                        }
                    }
                }
            });

            // E-Mail als gesendet markieren
            $this->email->update([
                Email::status => Email::STATUS_SENT,
                Email::sent_at => now(),
                Email::error_message => null,
            ]);

            // Message-ID speichern wenn verfügbar
            if (!$this->email->message_id) {
                $this->email->update([
                    Email::message_id => $this->generateMessageId(),
                ]);
            }

            Log::info("E-Mail {$this->email->id} erfolgreich gesendet an {$this->email->to_email}");

        } catch (Exception $e) {
            // Fehler loggen
            Log::error("Fehler beim Versenden von E-Mail {$this->email->id}: " . $e->getMessage(), [
                'exception' => $e,
                'email_id' => $this->email->id,
                'to_email' => $this->email->to_email,
            ]);

            // E-Mail als fehlgeschlagen markieren
            $this->email->update([
                Email::status => Email::STATUS_FAILED,
                Email::error_message => $e->getMessage(),
            ]);

            // Exception weiterwerfen für Queue-Retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        // Markiere E-Mail als endgültig fehlgeschlagen
        $this->email->update([
            Email::status => Email::STATUS_FAILED,
            Email::error_message => "Endgültig fehlgeschlagen nach {$this->tries} Versuchen: " . $exception->getMessage(),
        ]);

        Log::error("E-Mail {$this->email->id} endgültig fehlgeschlagen", [
            'exception' => $exception,
            'email_id' => $this->email->id,
            'attempts' => $this->tries,
        ]);
    }

    /**
     * Generiere eine eindeutige Message-ID
     */
    protected function generateMessageId(): string
    {
        return sprintf(
            '<%s.%s@%s>',
            base_convert(microtime(false), 10, 36),
            base_convert(bin2hex(random_bytes(8)), 16, 36),
            config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : 'localhost'
        );
    }
}
