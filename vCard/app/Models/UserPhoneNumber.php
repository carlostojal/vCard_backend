<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPhoneNumber extends Model
{
    // use HasFactory;
    protected $table = 'user_phone_numbers';

    protected $fillable = ['user_id', 'phone_number'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
