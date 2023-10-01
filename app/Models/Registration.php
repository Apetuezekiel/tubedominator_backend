<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Registration extends Model
{
    use HasFactory;
    use HasApiTokens;
    protected $fillable = [
        'channel_name',
        'channel_id',
        'channel_language',
        'description',
        'business_email',
        'accept_terms',
        'keywords',
    ];

}
