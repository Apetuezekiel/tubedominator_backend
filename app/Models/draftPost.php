<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class draftPost extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'search_term',
        'video_title',
        'video_description',
        'video_tags',
        'video_thumbnail',
    ];
}
