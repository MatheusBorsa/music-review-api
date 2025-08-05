<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserArtist extends Pivot
{
    protected $fillable = ['user_id','artist_mbid'];  
}

