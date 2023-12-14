<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IbanReference implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(strlen($value) !== 25){
            $fail('Malformed reference, needs to be 25 caracters long');
        }

        $ref_letters = substr($value, 0, 2);
        $ref_nums = substr($value, 2);

        if(strlen($ref_letters) !== 2){
            $fail('Malformed reference, use only 2 letters');
        }

        if(strlen($ref_nums) !== 23){
            $fail('Malformed reference, use only 23 digits');
        }

        if(!ctype_alpha($ref_letters)){
            $fail('Malformed reference, The first 2 caracters needs to be letters');
        }
        if(!ctype_digit($ref_nums)){
            $fail('Malformed reference, The last 23 caracters needs to be digits');
        }
    }
}
