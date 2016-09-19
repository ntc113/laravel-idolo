<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Category extends Model
{
	use Sluggable;

    public $table = 'categories';
    public $timestamps = false;
	protected $fillable = array('name');

	/**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

	/* relation */
	public function posts() {
		return $this->hasMany('App\Models\Post', 'id', 'post_id');
	}
}
