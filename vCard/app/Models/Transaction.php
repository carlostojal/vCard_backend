<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'vcard', 'date', 'datetime', 'value', 'type', 'old_balance',
         'new_balance', 'payment_type', 'pair_vcard', 'payment_reference', 'description'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
    
    public function pairTransaction()
    {
        return $this->belongsTo(Transaction::class, 'pair_transaction', 'id');
    }

    function vcards(){
        return $this->belongsTo(Vcard::class, 'vcard', 'phone_number');
    }
}
