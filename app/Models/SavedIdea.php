<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedIdea extends Model
{
    use HasFactory;
    protected $fillable = [
        'video_ideas',
        'search_volume',
        'keyword_diff',
        'potential_views',
    ];
}
