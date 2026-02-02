<?php

namespace App\Filament\Traits;

use Illuminate\Database\Eloquent\Model;

trait UseResourceUrlsTrait
{
    public static function getViewUrl(Model $record): string
    {
        return self::getUrl('view', ['record' => $record]);
    }

    public static function getEditUrl(Model $record): string
    {
        return self::getUrl('edit', ['record' => $record]);
    }
}
