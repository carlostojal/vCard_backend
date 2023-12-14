<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MbReference implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(!strpos($value, '-')){
            $fail('Malformed string, try with xxxx-xxxxxxxxx');
        }

        $ref = explode("-", $value);
        if(strlen($ref[0]) < 5 || strlen($ref[0]) > 5){
            $fail('Entity needs to be 5 digits long');
        }

        if(strlen($ref[1]) < 9 || strlen($ref[1]) > 9){
            $fail('Reference needs to be 5 digits long');
        }

        if(!ctype_digit($ref[0])){
            $fail('Entity needs to be only digits');
        }

        if(!ctype_digit($ref[1])){
            $fail('Reference needs to be only digits');
        }
    }
}
