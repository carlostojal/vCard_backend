<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PaypalReference implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(filter_var($value, FILTER_VALIDATE_EMAIL) == false){
            $fail('Malformed reference, must be a valid email address');
        }
    }
}
