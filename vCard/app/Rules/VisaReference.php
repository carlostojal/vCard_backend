<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class VisaReference implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(strlen($value) !== 16){
            $fail('Malformed reference, needs to be 16 digits long');
        }

        if($value[0] != '4'){
            $fail('Malformed reference, the first digit needs to be 4');
        }

        if(!ctype_digit($value)){
            $fail('Malformed reference, needs to be digits');
        }
    }
}
