<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'referral_code',
        'referred_by',
        'email_verified_at',
        'password',
        'is_frozen',
        'is_deleted'
    ];

    protected $hidden = [
        'email_verified_at',
        'password',
        'is_deleted'
    ];
}
