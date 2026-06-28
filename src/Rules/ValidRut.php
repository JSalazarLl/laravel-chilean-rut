<?php

declare(strict_types=1);

namespace JSalazarLl\Rut\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use JSalazarLl\Rut\Rut;

final class ValidRut implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! Rut::isValid(is_scalar($value) ? (string) $value : null)) {
            $fail('El campo :attribute no es un RUT valido.');
        }
    }
}
