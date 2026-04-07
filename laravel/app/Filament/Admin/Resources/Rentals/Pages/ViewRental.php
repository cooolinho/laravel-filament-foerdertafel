<?php

namespace App\Filament\Admin\Resources\Rentals\Pages;

use App\Filament\Admin\Resources\Rentals\Actions\RentalActions;
use App\Filament\Admin\Resources\Rentals\RentalResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRental extends ViewRecord
{
    protected static string $resource = RentalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            RentalActions::markAsPaid(),
            RentalActions::resendConfirmationEmail(),

            ActionGroup::make([
                RentalActions::viewAccessCode(),
                RentalActions::regenerateAccessCode(),
                RentalActions::resendAccessCode(),
            ])
                ->label('Zugangscode')
                ->icon('heroicon-o-key')
                ->color('info')
                ->button(),

            ActionGroup::make([
                RentalActions::manageContent(),
                RentalActions::viewContentStatus(),
                RentalActions::initializeContent(),
                RentalActions::deleteContent(),
            ])
                ->label('Content')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->button(),

            RentalActions::openCustomerPortal(),
        ];
    }
}
