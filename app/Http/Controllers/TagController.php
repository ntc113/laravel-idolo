<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Models\Tag;

class TagController extends Controller
{
    /** 
    * get list usport tags
    * $offset 	Integer
    * $limit 	Integer
    * $order 	String 	(columnname)
    * $by 		String (ASC|DESC)
    * return array object
    */
    public function getList($offset = 0, $limit = 0, $order='id', $by='ASC') {
    	$count = Tag::count();
    	$query = Tag::orderBy($order, $by);
    	if ($limit > 0 && $limit > $offset) {
    		$tags = $query->offset($offset)->limit($limit)->get();
    	} else {
    		$tags = $query->get();
    	}
    	if (!$tags) {
    		return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'Cannot find any post'));
    	}
    	return response()->json(array('error'=>0,'count'=>$count, 'data'=>$tags, 'message'=>''));
    }
    
    /** 
    * get list post by tag id
    * $categoryId integer
    * $offset 	Integer
    * $limit 	Integer
    * $order 	String 	(columnname)
    * $by 		String (ASC|DESC)
    * return array object
    */
    public function getPostsByTagId ($tagId, $offset = 0, $limit = 0, $order='post_id', $by='ASC') {
    	$query = Tag::where('id', $tagId)->first();
    	if (!$query) {
    		return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'Cannot find tag'));
    	}
    	$query = $query->posts()->orderBy($order, $by);
    	if ($limit > 0 && $limit > $offset) {
    		$posts = $query->offset($offset)->limit($limit)->get();
    	} else {
    		$posts = $query->get();
    	}
    	if (!$posts) {
    		return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'Cannot find any post'));
    	}
    	$count = $query->count();
    	return response()->json(array('error'=>0,'count'=>$count, 'data'=>$posts, 'message'=>''));
    }
    
    /** 
    * get list post by tag slug
    * $categoryId integer
    * $offset 	Integer
    * $limit 	Integer
    * $order 	String 	(columnname)
    * $by 		String (ASC|DESC)
    * return array object
    */
    public function getPostsByTagSlug ($tagSlug, $offset = 0, $limit = 0, $order='post_id', $by='ASC') {
    	$query = Tag::where('slug', $tagSlug)->first();
    	if (!$query) {
    		return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'Cannot find tag'));
		}
		$query = $query->posts()->orderBy($order, $by);
    	if ($limit > 0 && $limit > $offset) {
    		$posts = $query->offset($offset)->limit($limit)->get();
    	} else {
    		$posts = $query->get();
    	}
    	if (!$posts) {
    		return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'Cannot find any post'));
    	}
    	$count = $query->count();
    	return response()->json(array('error'=>0,'count'=>$count, 'data'=>$posts, 'message'=>''));
    }
}
