<?php

namespace App\Models;

class Tweet extends Model
{
    protected $table = 'tweets';

    protected $fillable = [
        'tweet_id',
        'text',
        'username',
        'likes',
        'url',
        'created_at',
    ];
}
