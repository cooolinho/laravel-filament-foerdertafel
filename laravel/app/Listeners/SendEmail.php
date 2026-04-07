<?php

namespace App\Listeners;

use App\Events\EmailCreated;
use App\Jobs\SendEmailJob;
use App\Models\Email;
use Illuminate\Support\Facades\Log;

class SendEmail extends BaseEmailNotificationListener
{
    /**
     * Handle the event.
     *
     * Bettet den body_html der manuell erstellten E-Mail in das zentrale
     * Blade-Layout ein (gleicher Header/Footer wie bei automatischen E-Mails)
     * und reiht den Versand via SendEmailJob in die Queue ein.
     */
    public function handle(EmailCreated $event): void
    {
        $email = $event->email;

        // Nur DRAFT-E-Mails verarbeiten
        if ($email->status !== Email::STATUS_DRAFT) {
            Log::info("SendEmail-Listener: E-Mail #{$email->id} hat Status '{$email->status}' – kein Versand.");
            return;
        }

        // body_html in das zentrale Layout einbetten
        if ($email->body_html) {
            $wrappedHtml = $this->wrapInLayout(
                content:       $email->body_html,
                subject:       $email->subject ?? '',
                recipientName: $email->to_name ?? '',
                replyTo:       $email->reply_to,
            );

            $email->update([Email::body_html => $wrappedHtml]);
        }

        // Versand via Queue
        SendEmailJob::dispatch($email);

        Log::info("SendEmail-Listener: E-Mail #{$email->id} an {$email->to_email} in Queue eingereiht.");
    }
}
