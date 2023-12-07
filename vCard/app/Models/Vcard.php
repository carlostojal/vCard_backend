<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use App\Models\TransactionPiggyBank;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vcard extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes;

    protected $guard = 'vcard';
    protected $primaryKey = 'phone_number';

    protected $dates = ['deleted_at'];

    protected $hidden = [
        'password', 'confirmation_code', 'created_at', 'updated_at', 'deleted_at'
    ];

    //Relationships
    public function categories(){
        return $this->hasMany(Category::class, 'vcard', 'phone_number');
    }

    public function transactions(){
        return $this->hasMany(Transaction::class, 'vcard', 'phone_number');
    }

    public function piggyBank(){
        return $this->belongsTo(PiggyBank::class,'phone_number', 'vcard_phone_number');
    }

    public function piggyTransactions(){
        return $this->hasMany(TransactionPiggyBank::class, 'vcard', 'phone_number');
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
    //
    // public function getProfilePhoto() {
    //     $photo = $this->photo_url;
    //     if($photo != null){
    //         return asset("/public/vcard_photos/".$this->photo);
    //     }else return null;
    // }

}
