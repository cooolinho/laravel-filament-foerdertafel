<?php

namespace App\Filament\App\Pages;

use App\Settings\GeneralSettings;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ImprintPage extends Page
{
    protected string $view = 'filament.app.pages.imprint-page';

    protected static string|null|\BackedEnum $navigationIcon = Heroicon::InformationCircle;

    protected static ?string $navigationLabel = 'Impressum';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Impressum';
    protected static ?string $slug = 'impressum';

    public ?string $imprintText = null;

    public function mount(): void
    {
        /** @var GeneralSettings $settings */
        $settings = app(GeneralSettings::class);
        $this->imprintText = $settings->imprint_text;
    }
}
