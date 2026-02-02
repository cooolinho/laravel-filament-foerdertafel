<?php

namespace App\Filament\Admin\Resources\Inquiries\Pages;

use App\Filament\Admin\Resources\Inquiries\InquiryResource;
use App\Models\Customer;
use App\Models\Field;
use App\Models\Inquiry;
use App\Models\Rental;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewInquiry extends ViewRecord
{
    protected static string $resource = InquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('convert_to_rental')
                ->label('In Vermietung umwandeln')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('In Vermietung umwandeln')
                ->modalDescription('Möchten Sie diese Anfrage in eine Vermietung umwandeln? Es wird automatisch ein Kunde erstellt oder verwendet, falls dieser bereits existiert.')
                ->visible(fn (Inquiry $record) => $record->status !== Inquiry::STATUS_CONVERTED)
                ->action(function (Inquiry $record) {
                    DB::transaction(function () use ($record) {
                        // Find or create customer
                        $customer = Customer::firstOrCreate(
                            [Customer::email => $record->customer_email],
                            [
                                Customer::name => $record->customer_name,
                                Customer::phone => $record->customer_phone,
                                Customer::address => null,
                            ]
                        );

                        // Create rental
                        $rental = Rental::create([
                            Rental::customer_id => $customer->id,
                            Rental::start_date => $record->start_date,
                            Rental::end_date => $record->end_date,
                            Rental::total_price => 0, // Will be calculated based on fields
                            Rental::status => Rental::STATUS_ACTIVE,
                            Rental::notes => $record->message,
                        ]);

                        // Attach fields to rental
                        if (!empty($record->requested_fields)) {
                            $rental->fields()->attach($record->requested_fields);

                            // Calculate total price
                            $totalPrice = $rental->fields()->sum('price_per_month');
                            $rental->update([Rental::total_price => $totalPrice]);

                            $rental->fields()->update([
                                Field::status => Field::STATUS_RENTED,
                            ]);
                        }

                        // Update inquiry status
                        $record->update([
                            Inquiry::status => Inquiry::STATUS_CONVERTED,
                            Inquiry::rental_id => $rental->id,
                        ]);

                        Notification::make()
                            ->title('Erfolgreich umgewandelt')
                            ->success()
                            ->body("Die Anfrage wurde erfolgreich in Vermietung #{$rental->id} umgewandelt.")
                            ->send();
                    });

                    return redirect()->route('filament.admin.resources.rentals.view', ['record' => $this->record->rental_id]);
                }),

            EditAction::make(),
        ];
    }
}
