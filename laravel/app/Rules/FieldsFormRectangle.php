<?php

namespace App\Rules;

use App\Models\Field;
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

        // Extract all positions
        $positions = [];
        foreach ($value as $fieldId) {
            $field = $fields->get($fieldId);
            if (!$field) {
                continue;
            }

            // Get all grid positions this field occupies
            for ($row = $field->row; $row < $field->row + $field->height; $row++) {
                for ($col = $field->column; $col < $field->column + $field->width; $col++) {
                    $positions[] = ['row' => $row, 'col' => $col];
                }
            }
        }

        // Check if positions form a rectangle
        if (!$this->formRectangle($positions)) {
            $fail('Die ausgewählten Felder müssen ein zusammenhängendes Rechteck bilden.');
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

        // Find min/max rows and columns
        $minRow = min(array_column($positions, 'row'));
        $maxRow = max(array_column($positions, 'row'));
        $minCol = min(array_column($positions, 'col'));
        $maxCol = max(array_column($positions, 'col'));

        // Calculate expected number of positions in rectangle
        $expectedCount = ($maxRow - $minRow + 1) * ($maxCol - $minCol + 1);

        // Check if we have the right number of positions
        if (count($positions) !== $expectedCount) {
            return false;
        }

        // Create a set of position strings for quick lookup
        $positionSet = [];
        foreach ($positions as $pos) {
            $positionSet[$pos['row'] . ',' . $pos['col']] = true;
        }

        // Check if all positions in the rectangle are present
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
