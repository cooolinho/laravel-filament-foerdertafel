<?php

namespace App\Rules;

use App\Models\Field;
use App\Models\Setting;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FieldsFormRectangle implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value) || empty($value)) {
            return;
        }

        // Load all selected fields with their positions
        $fields = Field::whereIn('id', $value)
            ->get()
            ->keyBy('id');

        // If not all fields found, let other validation handle it
        if ($fields->count() !== count($value)) {
            return;
        }

        // Extract all grid positions (accounting for multi-cell fields)
        $positions = [];
        foreach ($value as $fieldId) {
            $field = $fields->get($fieldId);
            if (!$field) {
                continue;
            }

            for ($row = $field->row; $row < $field->row + $field->height; $row++) {
                for ($col = $field->column; $col < $field->column + $field->width; $col++) {
                    $positions[] = ['row' => $row, 'col' => $col];
                }
            }
        }

        // Check if positions form a rectangle
        if (!$this->formRectangle($positions)) {
            $fail('Die ausgewählten Felder müssen ein zusammenhängendes Rechteck bilden.');
            return;
        }

        // Check row/column constraints derived from max_fields_per_customer.
        // Skip this check when only a single field is selected – a single field
        // (even a large multi-cell one) is always valid regardless of its dimensions,
        // because it was intentionally configured that way by an administrator.
        if (count($value) > 1) {
            $minRow = min(array_column($positions, 'row'));
            $maxRow = max(array_column($positions, 'row'));
            $minCol = min(array_column($positions, 'col'));
            $maxCol = max(array_column($positions, 'col'));

            $selectionRows = $maxRow - $minRow + 1;
            $selectionCols = $maxCol - $minCol + 1;

            $maxAllowedRows = Setting::getMaxSelectionRows();
            $maxAllowedCols = Setting::getMaxSelectionCols();

            if ($selectionRows > $maxAllowedRows) {
                $fail("Die Auswahl darf maximal {$maxAllowedRows} Zeile(n) umfassen (aktuell: {$selectionRows}).");
                return;
            }

            if ($selectionCols > $maxAllowedCols) {
                $fail("Die Auswahl darf maximal {$maxAllowedCols} Spalte(n) umfassen (aktuell: {$selectionCols}).");
                return;
            }
        }
    }

    /**
     * Check if the given positions form a complete rectangle
     */
    private function formRectangle(array $positions): bool
    {
        if (empty($positions)) {
            return false;
        }

        $minRow = min(array_column($positions, 'row'));
        $maxRow = max(array_column($positions, 'row'));
        $minCol = min(array_column($positions, 'col'));
        $maxCol = max(array_column($positions, 'col'));

        $expectedCount = ($maxRow - $minRow + 1) * ($maxCol - $minCol + 1);

        if (count($positions) !== $expectedCount) {
            return false;
        }

        $positionSet = [];
        foreach ($positions as $pos) {
            $positionSet[$pos['row'] . ',' . $pos['col']] = true;
        }

        for ($row = $minRow; $row <= $maxRow; $row++) {
            for ($col = $minCol; $col <= $maxCol; $col++) {
                if (!isset($positionSet[$row . ',' . $col])) {
                    return false;
                }
            }
        }

        return true;
    }
}
