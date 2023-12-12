<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Vcard;

class TransactionPiggyBank extends Model
{
    use HasFactory;

    protected $table = 'piggy_banks_transactions';
    public $timestamps = false; // Disable timestamps

    protected $fillable = [
        'vcard', 'date', 'datetime', 'value', 'type', 'old_balance', 'new_balance', 'description'
    ];

    public function vcard() {
        return $this->belongsTo(Vcard::class, 'vcard', 'phone_number');
    }
}
