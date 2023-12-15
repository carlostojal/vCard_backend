<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MbwayReference implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(strlen($value) != 9){
            $fail('Malformed reference, must be 9 digits long');
        }

        if(str($value)[0] !== '9'){
            $fail('Malformed reference, must start with 9');
        }

    }
}
