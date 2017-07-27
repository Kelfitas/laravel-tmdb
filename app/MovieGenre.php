<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class MovieGenre extends Model
{
    protected $table = 'movie_genre';
    public $timestamps = false;

    public function __construct() {}
}