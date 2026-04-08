<?php

namespace App\Filament\Admin\Resources\Inquiries\Actions;

use App\Models\Customer;
use App\Models\Field;
use App\Models\Inquiry;
use App\Models\Rental;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class InquiryActions
{
    /**
     * Action: Anfrage in Vermietung umwandeln
     */
    public static function convertToRental(): Action
    {
        return Action::make('convert_to_rental')
            ->label('In Vermietung umwandeln')
            ->icon(Heroicon::Cog6Tooth)
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('In Vermietung umwandeln')
            ->modalDescription('Möchten Sie diese Anfrage in eine Vermietung umwandeln? Es wird automatisch ein Kunde erstellt oder verwendet, falls dieser bereits existiert.')
            ->visible(fn (Inquiry $record) => $record->status !== Inquiry::STATUS_CONVERTED)
            ->action(function (Inquiry $record, $livewire) {
                DB::transaction(function () use ($record) {
                    // Find or create customer
                    $customer = Customer::firstOrCreate(
                        [Customer::email => $record->customer_email],
                        [
                            Customer::name                      => $record->customer_name,
                            Customer::phone                     => $record->customer_phone,
                            Customer::is_company                => $record->is_company,
                            Customer::company_name              => $record->company_name,
                            Customer::street                    => $record->street,
                            Customer::street_nr                 => $record->street_nr,
                            Customer::zip                       => $record->zip,
                            Customer::city                      => $record->city,
                            Customer::payment_method            => $record->payment_method,
                            Customer::account_holder            => $record->account_holder,
                            Customer::iban                      => $record->iban,
                            Customer::bic                       => $record->bic,
                            Customer::bank_name                 => $record->bank_name,
                            Customer::sepa_mandate_accepted     => $record->sepa_mandate_accepted,
                            Customer::billing_use_postal_address => $record->billing_use_postal_address,
                            Customer::billing_street            => $record->billing_street,
                            Customer::billing_address2          => $record->billing_address2,
                            Customer::billing_zip               => $record->billing_zip,
                            Customer::billing_city              => $record->billing_city,
                            Customer::billing_country           => $record->billing_country,
                        ]
                    );

                    // Wenn der Kunde bereits existiert, Zahlungsdaten aktualisieren
                    if (!$customer->wasRecentlyCreated) {
                        $customer->update([
                            Customer::payment_method            => $record->payment_method,
                            Customer::account_holder            => $record->account_holder,
                            Customer::iban                      => $record->iban,
                            Customer::bic                       => $record->bic,
                            Customer::bank_name                 => $record->bank_name,
                            Customer::sepa_mandate_accepted     => $record->sepa_mandate_accepted,
                            Customer::billing_use_postal_address => $record->billing_use_postal_address,
                            Customer::billing_street            => $record->billing_street,
                            Customer::billing_address2          => $record->billing_address2,
                            Customer::billing_zip               => $record->billing_zip,
                            Customer::billing_city              => $record->billing_city,
                            Customer::billing_country           => $record->billing_country,
                        ]);
                    }

                    // Create rental
                    $rental = Rental::create([
                        Rental::customer_id   => $customer->id,
                        Rental::start_date    => $record->start_date,
                        Rental::end_date      => $record->end_date,
                        Rental::rental_months => $record->rental_months,
                        Rental::total_price   => 0,
                        Rental::status        => Rental::STATUS_ACTIVE,
                        Rental::notes         => $record->message,
                    ]);

                    // Attach fields and calculate total price
                    if (!empty($record->requested_fields)) {
                        $rental->fields()->attach($record->requested_fields);

                        $totalPrice = $record->calculateGrossTotal();
                        $rental->update([Rental::total_price => $totalPrice]);

                        $rental->fields()->update([Field::status => Field::STATUS_RENTED]);
                    }

                    // Update inquiry status
                    $record->update([
                        Inquiry::status    => Inquiry::STATUS_CONVERTED,
                        Inquiry::rental_id => $rental->id,
                    ]);

                    Notification::make()
                        ->title('Erfolgreich umgewandelt')
                        ->success()
                        ->body("Die Anfrage wurde erfolgreich in Vermietung #{$rental->id} umgewandelt.")
                        ->send();
                });

                return redirect()->route('filament.admin.resources.rentals.view', ['record' => $record->rental_id]);
            });
    }
}

