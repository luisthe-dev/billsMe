<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    protected $fillable = [
        'token_type',
        'token_code',
        'token_user',
        'token_exipry',
        'token_status'
    ];
}
