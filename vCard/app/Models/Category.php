<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'vcard', 'type', 'name'
    ];

    function vcards(){
        return $this->belongsTo(Vcard::class, 'vcard', 'phone_number');
    }
}
