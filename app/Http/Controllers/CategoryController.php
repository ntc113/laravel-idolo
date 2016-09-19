<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Models\Category;

class CategoryController extends Controller
{
	public function __construct() {
	}
	
	/*get list categories*/
    public function getList () {
    	$categories = Category::orderBy('order', 'ASC')->get();
    	$count = Category::count();
    	return response()->json(array('error'=>0, 'count'=>$count, 'data'=>$categories));
    }
}
