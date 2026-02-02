<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaxFieldsCount implements ValidationRule
{
    protected int $maxFields;

    public function __construct(int $maxFields = 10)
    {
        $this->maxFields = $maxFields;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            return;
        }

        if (count($value) > $this->maxFields) {
            $fail("Sie können maximal {$this->maxFields} Felder auswählen.");
        }
    }
}
