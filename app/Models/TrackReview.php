<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackReview extends Model
{
    protected $fillable = [
        'user_id',
        'track_mbid',
        'rating'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
