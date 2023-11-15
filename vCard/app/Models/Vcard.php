<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class Vcard extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $guard = 'vcard';
    protected $primaryKey = 'phone_number';

    protected $hidden = [
        'password', 'confirmation_code', 'created_at', 'updated_at', 'deleted_at'
    ];

    //Relationships

    public function categories(){
        return $this->belongsToMany(Category::class);
    }

    public function transactions(){
        return $this->hasMany(Transaction::class, 'vcard', 'phone_number');
    }


    //Passport
    public function findForPassport($phone_number): Vcard {
        //This setups username field in post oauth/token
        return $this->where('phone_number', $phone_number)->first();
    }

    public function getUserIdentifier() {
        return $this->phone_number; //Passport needs to make the primary key mapping for tokens
    }

    public function getAuthIdentifier() {
        return $this->phone_number; //Passport needs to make the primary key mapping for tokens
    }


    public function getAuthIdentifierName() {
        return "phone_number";
    }
}
