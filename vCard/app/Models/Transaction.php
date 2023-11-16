<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    function vcards(){
        return $this->belongsTo(Vcard::class, 'vcard', 'phone_number');
    }
}
