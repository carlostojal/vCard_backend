<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DefaultCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    protected $table = 'default_categories';
    protected $dates = ['deleted_at'];

    public $timestamps = false;

    protected $fillable = [
        'name', 'type', 'deleted_at',
    ];
}
