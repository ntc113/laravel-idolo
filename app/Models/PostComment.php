<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    public function post() {
    	return $this->hasMany('App\Models\Post', 'id', 'post_id');
    }
}
