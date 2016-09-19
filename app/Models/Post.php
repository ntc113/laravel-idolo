<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Post extends Model
{
    use Sluggable;

    public $table = 'posts';
    // protected $fillable = ['title', 'content', 'is_published', 'liked', 'youtube_id', 'facebook_id'];
    protected $guarded  = ['id'];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

	/* relation */
    public function tags() {
        return $this->belongsToMany('App\Models\Tag', 'post_tags');//->select(array('name'));
    }

    public function category() {
        return $this->hasMany('App\Models\Category', 'id', 'category_id');
    }

    public function attachments() {
        return $this->hasMany('App\Models\Attachment');
    }

    public function comments() {
        return $this->hasMany('App\Models\PostComment');
    }
    public function user() {
        return $this->hasMany('App\Models\User', 'id', 'user_id');
    }

}
