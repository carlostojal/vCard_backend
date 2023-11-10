<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // public function findForPassport(string $name): User
    // {
    //     return $this->where('name', $name)->first();
    // }
    public static function boot()
    {
        parent::boot();

        // Event listener to adjust the id before saving
        static::creating(function ($user) {
            // Ensure the id is at least 10 digits
            if (strlen((string) $user->id) < 10) {
                $user->id = str_pad($user->id, 10, '0', STR_PAD_LEFT);
            }
        });
    }
}
