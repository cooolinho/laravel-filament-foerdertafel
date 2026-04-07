<?php

namespace App\Filament\Admin\Resources\Emails\Pages;

use App\Events\EmailCreated;
use App\Filament\Admin\Resources\Emails\EmailResource;
use App\Models\Email;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateEmail extends CreateRecord
{
    protected static string $resource = EmailResource::class;

    protected function afterCreate(): void
    {
        /** @var Email $email */
        $email = $this->record;

        if ($email->status === Email::STATUS_DRAFT) {
            // EmailCreated-Event feuern → SendEmail-Listener übernimmt
            // Layout-Wrapping und dispatcht SendEmailJob
            event(new EmailCreated($email));

            Notification::make()
                ->success()
                ->title('E-Mail wird versendet')
                ->body("E-Mail #{$email->id} wurde erstellt und zum Versand in die Queue eingereiht.")
                ->send();
        }
    }
}
