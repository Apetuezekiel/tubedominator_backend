<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class userSavedKeyword extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'email',
        'keyword',
        'search_volume',
    ];
}
