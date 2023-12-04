<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public function pairTransaction()
    {
        return $this->belongsTo(Transaction::class, 'pair_transaction', 'id');
    }

    function vcards(){
        return $this->belongsTo(Vcard::class, 'vcard', 'phone_number');
    }
}
