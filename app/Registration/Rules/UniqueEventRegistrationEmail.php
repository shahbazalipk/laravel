<?php

namespace App\Registration\Rules;

use App\Models\Registration;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueEventRegistrationEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = strtolower(trim((string) $value));

        if ($email === '') {
            return;
        }

        $alreadyRegistered = Registration::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->exists();

        if ($alreadyRegistered) {
            $fail('This email is already registered. You cannot use the same email again.');
        }
    }
}
