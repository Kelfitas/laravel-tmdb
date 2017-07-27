<?php
namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MovieGenre extends Pivot
{
    protected $table = 'movie_genre';

    public function __construct() {}
}