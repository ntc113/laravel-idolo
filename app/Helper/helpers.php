<?php

if (!function_exists('gratavarUrl')) {
    /**
     * Gravatar URL from Email address.
     *
     * @param string $email   Email address
     * @param string $size    Size in pixels
     * @param string $default Default image [ 404 | mm | identicon | monsterid | wavatar ]
     * @param string $rating  Max rating [ g | pg | r | x ]
     *
     * @return string
     */
    function gravatarUrl($email, $size = 60, $default = 'mm', $rating = 'g')
    {
        return 'http://www.gravatar.com/avatar/'.md5(strtolower(trim($email)))."?s={$size}&d={$default}&r={$rating}";
    }
}

/**
 * Backend menu active.
 *
 * @param $path
 * @param string $active
 *
 * @return string
 */
function setActive($path, $active = 'active')
{
    if (is_array($path)) {
        foreach ($path as $k => $v) {
            $path[$k] = getLang().'/'.$v;
        }
    } else {
        $path = getLang().'/'.$path;
    }

    return call_user_func_array('Request::is', (array) $path) ? $active : '';
}

/**
 * @return mixed
 */
function getLang()
{
    return '';
    // return LaravelLocalization::getCurrentLocale();
}

/**
 * @param null $url
 *
 * @return mixed
 */
function langURL($url = null)
{

    //return LaravelLocalization::getLocalizedURL(getLang(), $url);

    return getLang().$url;
}

/**
 * @param $route
 * @param array $parameters
 *
 * @return mixed
 */
function langRoute($route, $parameters = array())
{
    return URL::route($route, $parameters);
}

/**
 * @param $route
 *
 * @return mixed
 */
function langRedirectRoute($route)
{
    return Redirect::route($route);
}

/**
 * Get words from string...
 * @param string $str: String of words
 * @param integer $max: Maximum words
 * @param char $char: is Delimiter
 * @param string $end: Apend string if string will be cutted
 */
function subwords( $str, $max = 24, $char = ' ', $end = '...' ) {
    $str = trim( $str ) ;
    $str = $str . $char ;
    $len = strlen( $str ) ;
    $words = '' ;
    $w = '' ;
    $c = 0 ;
    for ( $i = 0; $i < $len; $i++ ) {
        if ( $str[$i] != $char ) {
            $w = $w . $str[$i] ;
        } else {
            if ( ( $w != $char ) and ( $w != '' ) ) {
                $words .= $w . $char ;
                $c++ ;
                if ( $c >= $max ) {
                    break ;
                }
                $w = '' ;
            }
        }
    }
    if ( $i+1 >= $len) {
        $end = '' ;
    }
    return trim( $words ) . $end ;
}

/**
 * file get content
 * @param  [type] $url [description]
 * @return [type]      [description]
 */
function getData ($url) {
    $result = @file_get_contents($url);
    if ($result === false) {
        \Log::error('Cannot get data from url:' . $url);
        return ['error'=>1005];//response()->json(array('error'=>1005, 'data'=>'', 'message'=>'cannot get content'));
    }
    return ['error'=>0, 'data'=>json_decode($result)];// response()->json(array('error'=>0, 'data'=>json_decode($result), 'message'=>''));
}

/**
 * postData description
 * @param  String   $url      [description]
 * @param  array    $postData
 * @return JSON     $Result;
 */
function postData ($url, $postData, $method = 'POST') {
    // use key 'http' even if you send the request to https://...
    try {
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => $method,
                'content' => http_build_query($postData)
            )
        );
        $context  = @stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        \Log::info('POSTDATA|' . $url . '|' . http_build_query($postData) .'|' . json_encode($result));
        return $result;
    } catch (Exception $e) {
        \Log::error('POSTDATA|' . $e->getMessage());
        return false;
    }
    
}

/**
 * get page access token
 * @param  [type] $accessToken [description]
 * @return [type]              [description]
 */
function getPageAccessToken() {
    try {
        $adminAccessToken = \App\Models\User::find(config('cc.facebook_api.admin_user_id'));
        \Log::info('Helper|getPageAccessToken|adminAccessToken:'.config('cc.facebook_api.admin_user_id'));
        // get page access token
        $pageAccessToken = '';
        \Log::info('Helper|getPageAccessToken|adminInfo:'.json_encode($adminAccessToken->fb_access_token));
        $url = config('cc.facebook_api.graph_url') . config('cc.facebook_api.fanpage_id') . '?fields=access_token&access_token='.$adminAccessToken->fb_access_token;
        $page = getData($url);
        \Log::info('Helper|getPageAccessToken|url:'.$url);
        if ($page['error'] != 0) {
            \Log::error('API_PostController|cannot get page access token|' . json_encode($page));
            return false;
        }
        $page = $page['data'];
        \Log::info('Helper|getPageAccessToken|'.json_encode($page));
        $pageAccessToken = $page->access_token;
        if ($pageAccessToken == '') {
            return false;
        }
        return $pageAccessToken;
    } catch (Exception $e) {
        return false;
    }
    return false;
}

/**
 * get data setting
 * @param  String $key
 * @return mix
 */
function getSetting ($key) {
    try {
        $settingData = \App\Models\Setting::where('key', $key)->first();
        if ($settingData) {
            return $settingData->value;
        }
        return false;
    } catch (Exception $e) {
        \Log::error('Helper|getSetting|cannot get key:'.$key.'|'.$e->getMessage());
        return false;
    }
    \Log::error('Helper|getSetting|Unknown');
    return false;
}

/**
 * [setSetting description]
 * @param array $data [description]
 */
function setSetting($data = array()) {
    if (!is_array($data) || empty($data)) {
        \Log::error('Helper|setSetting|data is not valid:'.json_encode($data));
        return false;
    }

    try {
        foreach ($data as $key => $value) {
            $settingData = \App\Models\Setting::firstOrNew(array('key'=>$key));
            $settingData->value = $value;
            $settingData->save();
        }
        return true;
    } catch (Exception $e) {
        \Log::error('Helper|setSetting|cannot set data:'.json_encode($data).'|'.$e->getMessage());
        return false;
    }
    \Log::error('Helper|setSetting|Unknown');
    return false;
}

/**
 * [deleteFbPost description]
 * @param  [type] $fbPostId [description]
 * @return [type]           [description]
 */
function deleteFbPost($fbPostId) {
    $adminAccessToken = \App\Models\User::find(config('cc.facebook_api.admin_user_id'));

    // get page access token
    $pageAccessToken = '';
    \Log::info('API_PostController|deletePost|adminInfo:'.json_encode($adminAccessToken->fb_access_token));
    $url = config('cc.facebook_api.graph_url') . config('cc.facebook_api.fanpage_id') . '?fields=access_token&access_token='.$adminAccessToken->fb_access_token;
    $page = getData($url);
    \Log::info('API_PostController|deletePost|url:'.$url);
    if ($page['error'] != 0) {
        \Log::error('API_PostController|cannot get page access token|' . json_encode($page));
        return false;
    }
    $page = $page['data'];
    \Log::info('API_PostController|deletePost|'.json_encode($page));
    $pageAccessToken = $page->access_token;
    if ($pageAccessToken == '') {
        return false;
    }

    $url = config('cc.facebook_api.graph_url') . $fbPostId;
    \Log::info('API_PostController|DeletePost|PageAccessToken:'.$pageAccessToken);
    $postData = array('access_token'=>$pageAccessToken);

    return postData($url, $postData, 'DELETE');
}

function deleteYtPost ($ytPostId) {
    return true;
}