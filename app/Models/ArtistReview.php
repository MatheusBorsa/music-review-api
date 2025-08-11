<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtistReview extends Model
{
    protected $fillable = [
        'user_id',
        'artist_mbid',
        'rating'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
