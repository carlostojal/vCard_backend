<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiggyBank extends Model
{
    use HasFactory;

    protected $guard = 'vcard';
    protected $primaryKey = 'id';

    public $timestamps = false; // Disable timestamps

    //Relationships
    public function transactions(){
        return $this->hasMany(TransactionPiggyBank::class, 'vcard', 'vcard_phone_number');
    }


    public function vcard(){
        return $this->hasOne(Vcard::class, 'vcard_phone_number', 'phone_number');
    }


}
