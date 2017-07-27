<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $table = 'movies';
    public $timestamps = true;

    public function genres() {
        return $this->belongsToMany('App\Genre', 'movie_genre');
    }
}
