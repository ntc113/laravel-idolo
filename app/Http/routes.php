<?php
// header('Access-Control-Allow-Origin: http://arunranga.com');
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', array( 'as'=>'home', 'uses'=>function () {
    return view('app');
}));
Route::get('/hot', array( 'as'=>'hot', 'uses'=>function () {
    return view('app');
}));
Route::get('/category/{categorySlug}', function () {
    return view('app');
});
Route::get('/post/{postId}', function () {
    return view('app');
});
Route::get('/user/{userId}', function () {
    return view('app');
});

Route::group(['middleware' => ['web']], function () {
    Route::resource('post', 'PostCtrl');
});
// Templates
Route::group(array('prefix'=>'/templates/'), function() {
    Route::get('{template}', array(function($template) {
        $template = str_replace(".html", "", $template);
        View::addExtension('html', 'php');
        return View::make('templates.' . $template);
    }));

    Route::get('home/{template}', array(function($template) {
        $template = str_replace(".html","", $template);
        View::addExtension('html', 'php');
        return View::make('templates.home.' . $template);
    }));

    Route::get('user/{template}', array(function($template) {
        $template = str_replace(".html","", $template);
        View::addExtension('html', 'php');
        return View::make('templates.user.' . $template);
    }));

    Route::get('post/{template}', array(function($template) {
        $template = str_replace(".html","", $template);
        View::addExtension('html', 'php');
        return View::make('templates.post.' . $template);
    }));

    Route::get('chat/{template}', array(function($template) {
        $template = str_replace(".html","", $template);
        View::addExtension('html', 'php');
        return View::make('templates.chat.' . $template);
    }));
});

/*API*/
Route::group(array('prefix'=>'/api/'), function() {
	/*User*/
	Route::get('/user/getlistusers/{offset?}/{limit?}/{order?}/{by?}', 'UserController@getList');
    Route::get('/user/getuserbyid/{userId}', 'UserController@getUserById');
    Route::get('/user/getuserbyfbid/{fbId}', 'UserController@getUserByFbId');
    Route::get('/user/sociallogin/{token}', 'UserController@socialLogin');
    Route::get('/user/logout', 'UserController@socialLogout');
    Route::post('/user/savefbuser', 'UserController@saveFacebookUser');
    Route::post('/user/updateuserinfo', 'UserController@updateUserInfo');

	/*Post*/
	Route::get('/post/getlistposts', 'PostController@getPosts');
    Route::get('/post/gettopposts/{offset?}/{limit?}', 'PostController@getTopPosts');
    Route::get('/post/gethotposts/{offset?}/{limit?}', 'PostController@getHotPosts');

    Route::get('/post/getpostbyid/{postId}', 'PostController@getPostById');
    Route::get('/post/getpostbyslug/{postSlug}', 'PostController@getPostBySlug');

    Route::get('/post/gethotpostsbycategoryid/{categoryId}/{offset?}/{limit?}', 'PostController@getHotPosts');
	Route::get('/post/getpostsbycategoryid/{categoryId}/{offset?}/{limit?}/{order?}/{by?}', 'PostController@getPostsByCategoryId');
	Route::get('/post/getpostsbycategoryslug/{categorySlug}/{offset?}/{limit?}/{order?}/{by?}', 'PostController@getPostsByCategorySlug');

    Route::get('/post/getpostsbyuserid/{userId}/{offset?}/{limit?}', 'PostController@getPostByUserId');    

    Route::post('/post/createpost', 'PostController@createPost');
    Route::post('/post/newpost', 'PostController@newPost');
    Route::post('/post/deletepost', 'PostController@deletePost');

	/*Category*/
	Route::get('/category/getlistcategories', 'CategoryController@getList');

	/*Tags*/
	Route::get('/tag/getlisttags', 'TagController@getList');
	Route::get('/tag/getpostsbytagid/{tagId}', 'TagController@getPostsByTagId');
	Route::get('/tag/getpostsbytagslug/{tagSlug}', 'TagController@getPostsByTagSlug');

    /*Channel*/
    Route::get('/channel/getmatch/{source?}', 'ChannelController@getmatch');
    Route::get('/channel/getmatchlink/url/{source?}', 'ChannelController@getmatchlink');
    Route::get('/channel/getlivematch/{sport?}', 'ChannelController@getlivematch');

    /*like*/
    Route::post('/post/likecomment', 'PostController@likeComment');
    Route::post('/post/likepost', 'PostController@likePost');
    Route::post('/post/unlikepost/', 'PostController@unlikePost');
    Route::get('/post/getlistlike/{$postId}/{offset?}/{limit?}', 'PostController@getListLike');

    /*comment*/
    Route::get('/post/getcommentsbypostid/{postId}/{offset?}/{limit?}', 'PostController@getCommentsByPostId');
    Route::post('/post/commentpost', array('as' => 'api.post.comment', 'uses' => 'PostController@commentPost'));

    /*share*/
    Route::post('post/sharepost', array('as'=>'api.post.share', 'uses'=>'PostController@sharePost'));

    /*gcm*/
    Route::get('/fcm', 'FcmController@index');
    Route::post('/fcm/registerdevice', 'FcmController@registerDevice');
    Route::post('/fcm/pushnotification', 'FcmController@pushNotification');

    /*upload video*/
    Route::post('/post/upload', 'PostController@upload');

    /* facebook*/
    Route::get('/facebook/sync', 'FacebookController@syncData');

    /*contact*/
    Route::post('/contact', 'ContactController@index');
});

// login
Route::get('/admin/login', array(
    // 'as' => 'admin.login',
    function () {
        // if (!Sentinel::check()) {
        //     return view('backend/auth/login');
        // }
        return Redirect::route('admin.dashboard');
    }, 
));

Route::get('/facebook', array('as'=>'facebook.login', 'uses'=>'FacebookController@facebookLogin'));
Route::get('/callback', array('as'=>'facebook.callback', 'uses'=>'FacebookController@facebookLoginCallback'));

Route::group(array('prefix' => '/admin',
                       'namespace' => 'Admin',
                       'middleware' => ['before', 'sentinel.auth', 'sentinel.permission'] ), function () {

    // admin dashboard
    Route::get('/', array('as' => 'admin.dashboard', 'uses' => 'DashboardController@index'));

    // category
    Route::resource('category', 'CategoryController', array('before' => 'hasAccess:category'));
    Route::get('category/{id}/delete', array('as' => 'admin.category.delete',
                                                 'uses' => 'CategoryController@confirmDestroy', ))->where('id', '[0-9]+');

    // post
    Route::resource('post', 'PostController', array('before' => 'hasAccess:post'));
    Route::get('post/{id}/delete', array('as' => 'admin.post.delete',
                                                 'uses' => 'PostController@confirmDestroy', ))->where('id', '[0-9]+');
    Route::get('post/{id}/publishtoyoutube', array('as' => 'admin.post.publishtoyoutube',
                                                 'uses' => 'PostController@publish', ))->where('id', '[0-9]+');
    Route::post('post/uploadyoutube', array('as' => 'admin.post.uploadyoutube',
                                                 'uses' => 'PostController@uploadYoutube', ));
    Route::get('post/{id}/publishtofacebook', array('as' => 'admin.post.sharefb',
                                                 'uses' => 'PostController@shareFb', ))->where('id', '[0-9]+');
    Route::post('post/{id}/publishtofacebook', array('as' => 'admin.post.publishtofacebook',
                                                 'uses' => 'PostController@postToFacebook', ))->where('id', '[0-9]+');
    Route::post('post/{id}/toggle-publish', array('as' => 'admin.post.toggle-publish',
                                                         'uses' => 'PostController@togglePublish', ))->where('id', '[0-9]+');

    // user
    Route::resource('user', 'UserController');
    Route::get('/user/{id}/delete', array('as' => 'admin.user.delete',
                                         'uses' => 'UserController@confirmDestroy', ))->where('id', '[0-9]+');

    // role
    Route::resource('/role', 'RoleController');
    Route::get('/role/{id}/delete', array('as' => 'admin.role.delete',
                                          'uses' => 'RoleController@confirmDestroy', ))->where('id', '[0-9]+');
    // contact
    Route::resource('contact', 'ContactController');
    Route::get('/contact/{id}/delete', array('as' => 'admin.contact.delete',
                                         'uses' => 'ContactController@confirmDestroy', ))->where('id', '[0-9]+');
    // ajax - contact
    Route::post('/contact/{id}/toggle-view', array('as' => 'admin.contact.toggle-view',
                                                      'uses' => 'ContactController@toggleView', ))->where('id', '[0-9]+');
});


Route::auth();

Route::get('/home', 'HomeController@index');

Route::group(array('namespace' => 'Admin'), function () {

    // admin auth
    Route::get('admin/logout', array('as' => 'admin.logout', 'uses' => 'AuthController@getLogout'));
    Route::get('admin/login', array('as' => 'admin.login', 'uses' => 'AuthController@getLogin'));
    Route::post('admin/login', array('as' => 'admin.login.post', 'uses' => 'AuthController@postLogin'));

    // admin password reminder
    Route::get('admin/forgot-password', array('as' => 'admin.forgot.password',
                                              'uses' => 'AuthController@getForgotPassword', ));
    Route::post('admin/forgot-password', array('as' => 'admin.forgot.password.post',
                                               'uses' => 'AuthController@postForgotPassword', ));

    Route::get('admin/{id}/reset/{code}', array('as' => 'admin.reset.password',
                                                'uses' => 'AuthController@getResetPassword', ))->where('id', '[0-9]+');
    Route::post('admin/reset-password', array('as' => 'admin.reset.password.post',
                                              'uses' => 'AuthController@postResetPassword', ));
});


/*Test*/
Route::get('/test', function () {
    return \Facebook::getApp();
return getSetting('ios');
    $data = array(
        'fanpage'   => 'http://fb.com/'.config('cc.facebook_api.fanpage_id'), 
        'android'   => url('/android/2016/09/13/app-debug.apk'),
        'ios'       => 'javascript:alert("commint soon")',
        );
    if(setSetting($data)) {
        return 'success';
    }
    return 'false';
});
/*end test*/
