<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
	protected $guarded = ['id'];
	
    public function posts() {
        return $this->hasMany('App\Models\Post');
    }
    public function comments() {
        return $this->hasMany('App\Models\PostComment');
    }
}
