<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vcard;
use Illuminate\Support\Facades\Validator;

class VcardPolicy
{
    public function update($user, Vcard $vcard): bool{
        if($user instanceof User){
            $validator = Validator::make(request()->all(), [
                'phone_number' => 'prohibited',
                'name' => 'prohibited',
                'email' => 'prohibited',
                'current_password' => 'prohibited',
                'password' => 'prohibited',
                'current_authorization_code' => 'prohibited',
                'authorization_code' => 'prohibited',
                'max_debit' => 'sometimes|numeric|',
                'blocked' => 'sometimes|numeric|max:1|min:0',
            ]);
            if(!$validator->fails()){
                return true;
            }
        }

        if($user instanceof Vcard){
            $validator = Validator::make(request()->all(), [
                'phone_number' => 'sometimes|numeric|unique:vcards,phone_number,max:999999999',
                'name' => 'sometimes|string|unique:vcards,name',
                'email' => 'sometimes|email',
                'current_password' => 'sometimes|string',
                'password' => 'sometimes|string',
                'current_authorization_code' => 'sometimes|numeric|max:9999',
                'authorization_code' => 'sometimes|numeric|max:9999',
                'max_debit' => 'prohibited',
            ]);

            if(!$validator->fails() && $vcard->phone_number == $user->phone_number){
                return true;
            }

        }
        return false;
    }
}
