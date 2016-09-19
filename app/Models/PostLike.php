<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostLike extends Model
{
    public function posts() {
        return $this->hasMany('App\Models\Post', 'id', 'post_id');
    }
}
