<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\ActiveRentalsWidget;
use App\Filament\Admin\Widgets\ExpiringRentalsWidget;
use App\Filament\Admin\Widgets\FieldStatusWidget;
use App\Filament\Admin\Widgets\InconsistentFieldStatusWidget;
use App\Filament\Admin\Widgets\RecentInquiriesWidget;
use App\Filament\Admin\Widgets\RevenueChartWidget;
use App\Filament\Admin\Widgets\StatsOverviewWidget;
use App\Models\UserDashboardSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserDashboardSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Dashboard Einstellungen';

    protected static ?string $title = 'Dashboard Einstellungen';

    protected string $view = 'filament.admin.pages.user-dashboard-settings-page';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    protected static array $availableWidgets = [
        StatsOverviewWidget::class => 'Statistik Übersicht',
        RecentInquiriesWidget::class => 'Neueste Anfragen',
        ActiveRentalsWidget::class => 'Aktive Vermietungen',
        ExpiringRentalsWidget::class => 'Auslaufende Vermietungen',
        FieldStatusWidget::class => 'Feldstatus',
        InconsistentFieldStatusWidget::class => 'Inkonsistente Felder',
        RevenueChartWidget::class => 'Umsatz Chart',
    ];

    public function mount(): void
    {
        $settings = auth()->user()->dashboardSettings;

        if ($settings) {
            // Prepare widget order for repeater
            $widgetOrder = [];
            if (!empty($settings->widget_order)) {
                foreach ($settings->widget_order as $widgetClass) {
                    if (isset(self::$availableWidgets[$widgetClass])) {
                        $widgetOrder[] = ['widget' => $widgetClass];
                    }
                }
            }

            // If no order is set, use all available widgets
            if (empty($widgetOrder)) {
                foreach (array_keys(self::$availableWidgets) as $widgetClass) {
                    $widgetOrder[] = ['widget' => $widgetClass];
                }
            }

            $this->form->fill([
                'visible_widgets' => $settings->visible_widgets ?? array_keys(self::$availableWidgets),
                'widget_order' => $widgetOrder,
            ]);
        } else {
            // Default: all widgets in default order
            $widgetOrder = [];
            foreach (array_keys(self::$availableWidgets) as $widgetClass) {
                $widgetOrder[] = ['widget' => $widgetClass];
            }

            $this->form->fill([
                'visible_widgets' => array_keys(self::$availableWidgets),
                'widget_order' => $widgetOrder,
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Dashboard Widgets')
                    ->description('Wählen Sie die Widgets aus, die auf Ihrem Dashboard angezeigt werden sollen.')
                    ->schema([
                        CheckboxList::make('visible_widgets')
                            ->label('Sichtbare Widgets')
                            ->options(self::$availableWidgets)
                            ->required()
                            ->minItems(1)
                            ->columns(2)
                            ->gridDirection('row'),
                    ]),
                Section::make('Widget-Reihenfolge')
                    ->description('Legen Sie die Reihenfolge fest, in der die Widgets angezeigt werden sollen. Ziehen Sie die Einträge, um die Reihenfolge zu ändern.')
                    ->collapsed()
                    ->schema([
                        Repeater::make('widget_order')
                            ->label('Reihenfolge')
                            ->schema([
                                Select::make('widget')
                                    ->label('Widget')
                                    ->options(self::$availableWidgets)
                                    ->required()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->native(false),
                            ])
                            ->reorderable()
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => self::$availableWidgets[$state['widget']] ?? null)
                            ->addActionLabel('Widget hinzufügen')
                            ->defaultItems(count(self::$availableWidgets))
                            ->minItems(1)
                            ->grid(1),
                    ]),
                Section::make('Actions')
                    ->heading(false)
                    ->schema($this->getFormActions()),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Einstellungen speichern')
                ->submit('save'),
            Action::make('reset')
                ->label('Zurücksetzen')
                ->color('gray')
                ->action('resetToDefault'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = auth()->user()->dashboardSettings()->firstOrNew([
            UserDashboardSetting::user_id => auth()->id(),
        ]);

        $settings->visible_widgets = $data['visible_widgets'];

        // Extract widget classes from the repeater data
        $widgetOrder = [];
        if (isset($data['widget_order']) && is_array($data['widget_order'])) {
            foreach ($data['widget_order'] as $item) {
                if (isset($item['widget'])) {
                    $widgetOrder[] = $item['widget'];
                }
            }
        }
        $settings->widget_order = $widgetOrder;

        $settings->save();

        Notification::make()
            ->title('Einstellungen gespeichert')
            ->success()
            ->send();
    }

    public function resetToDefault(): void
    {
        $settings = auth()->user()->dashboardSettings;

        if ($settings) {
            $settings->delete();
        }

        // Default: all widgets in default order
        $widgetOrder = [];
        foreach (array_keys(self::$availableWidgets) as $widgetClass) {
            $widgetOrder[] = ['widget' => $widgetClass];
        }

        $this->form->fill([
            'visible_widgets' => array_keys(self::$availableWidgets),
            'widget_order' => $widgetOrder,
        ]);

        Notification::make()
            ->title('Einstellungen zurückgesetzt')
            ->success()
            ->send();
    }

    public static function getAvailableWidgets(): array
    {
        return self::$availableWidgets;
    }
}
