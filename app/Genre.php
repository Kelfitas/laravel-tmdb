<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    protected $table = 'genres';
    public $timestamps = false;

    public function movies() {
        return $this->belongsToMany('App\Movie')->using('App\MovieGenre');
    }
}
