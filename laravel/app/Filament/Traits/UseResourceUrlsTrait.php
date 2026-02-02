<?php

namespace App\Filament\Traits;

use Illuminate\Database\Eloquent\Model;

trait UseResourceUrlsTrait
{
    public static function getCreateUrl(array $parameters = []): string
    {
        return static::getUrl('create', $parameters);
    }

    public static function getViewUrl(Model $record): string
    {
        return static::getUrl('view', ['record' => $record]);
    }

    public static function getEditUrl(Model $record): string
    {
        return static::getUrl('edit', ['record' => $record]);
    }
}
