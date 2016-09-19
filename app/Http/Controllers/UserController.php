<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Http\Requests;

use App\Models\User;
use App\Models\SocialAccount;
use App\Services\SocialAccountService;

class UserController extends Controller
{
    /** 
    * get list usport user
    * $offset
    * $limit
    * $order
    * $by
    * return array object
    */
    public function getList($offset = 0, $limit = 0, $order='score', $by='DESC') {
    	$query = User::orderBy($order, $by);
    	if ($limit > 0 && $limit > $offset) {
    		$query = $query->offset($offset)->limit($limit);
    	}
		
        $users = $query->get(array('id', 'name', 'avatar', 'phone_number', 'score'));
        $count = $query->count();

    	return response()->json(array('error'=>0,'count'=>$count, 'data'=>$users, 'message'=>''));
    }

    /** 
    * get userinfo by id 
    * $userId integer usportId or facebookId
    * return userinfo
    */
    public function getUserById ($userId) {
        if ($userId == 'me') {
            if (Auth::check()) {
                $userId = Auth::user()->id;
                \Log::info('Api_UserController|getUserById|current_UserId:'.$userId);
                // renew access token
                $user = User::find($userId);
                $updateInfo = $this->socialLogin($user->fb_access_token);
                $updateInfo = json_decode($updateInfo->getContent());
                if ($updateInfo->error != 0) {
                    return $this->socialLogout();
                }
                // return response()->json(array('error'=>0, 'data'=>Auth::user(), 'message'=>''));
            } else {
                return response()->json(array('error'=>1002, 'data'=>'', 'message'=>'not loggedin'));
            }
        }
        $user = User::find($userId);
        if (!$user) {
            return response()->json(array('error'=>1003, 'data'=>'', 'message'=>'cannot find user'));
        }

        $user->total_posts = \App\Models\Post::where('is_published', 1)->where('user_id', $userId)->count();
        return response()->json(array('error'=>0, 'data'=>$user, 'message'=>''));
    }

    /**
     * get user by facebook user id
     * @param  [type]
     * @return [type]
     */
    public function getUserByFbId ($fbId) {
        try {
            $user = User::whereFbUserId($fbId)->first();
            if (!$user) {
                return response()->json(array('error'=>1003, 'data'=>'', 'message'=>'cannot find user.'));
            }
            return response()->json(array('error'=>0, 'data'=>$user, 'message'=>''));
        } catch (Exception $e) {
            return response()->json(array('error'=>2001, 'data'=>'', 'message'=>'cannot find user.'));
        }
        return response()->json(array('error'=>1005, 'data'=>'', 'message'=>'Unknown error.'));
    }

    /**
     * api update userInfo
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function updateUserInfo(Request $request) {
        $fbId = $request->get('fb_id', '');
        $birthday = $request->get('birthday', 0);
        $email = $request->get('email', '');
        $gender = $request->get('gender', 0);
        $name = $request->get('name', '');
        $avatar = $request->get('avatar', '');
        $phoneNumber = $request->get('phone_number', '');

        // validate facebook user id
        if ($fbId == '') {
            return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'invalid params|' . $fbId));
        }

        try {
            $user = User::whereFbUserId($fbId)->first();
            if (!$user) {
                return response()->json(array('error'=>1003, 'data'=>'', 'message'=>'cannot find user.'));
            }
            // update birthday
            if ($birthday != 0) {
                $birthday = @date('Y-m-d', strtotime($birthday));
                $user->birthday = $birthday;
            }
            // update email
            if ($email != '') {
                $user->email = $email;
            }
            // update gender 1:male 2:femail
            if (is_int($gender) && $gender > 0) {
                $user->gender = $gender;
            }
            // update name
            if ($name != '') {
                $user->name = $name;
            }
            // update avatar
            if ($avatar != '') {
                $user->avatar = $avatar;
            }
            // update phone number
            if ($phoneNumber != '') {
                $user->phone_number = $phoneNumber;
            }
            // save to db
            $user->save();

            return response()->json(array('error'=>0, 'data'=>$user, 'message'=>''));
        } catch (Exception $e) {
            return response()->json(array('error'=>2001, 'data'=>'', 'message'=>'cannot find user.'));
        }

        return response()->json(array('error'=>1005, 'data'=>'', 'message'=>'Unknown error.'));
    }

    /**
     * [saveFacebookUser description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function saveFacebookUser (Request $request) {
        $fbId = $request->get('fb_id', '');
        $birthday = $request->get('birthday', 0);
        $email = $request->get('email', '');
        $gender = $request->get('gender', 0);
        $name = $request->get('name', '');
        $avatar = $request->get('avatar', '');
        $os = $request->get('os', 'android');
        $deviceId = $request->get('device_id', '');

        if ($fbId == '' || $name == '') {
            return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'invalid params' . $fbId . "::" . $name));
        }

        $userInfo = array();
        $userInfo['fb_user_id'] = $fbId;
        $userInfo['name'] = $name;
        if ($email != '') {
            $userInfo['email'] = $email;
        }
        $userInfo['gender'] = intval($gender);
        if ($birthday != 0) {
            $userInfo['birthday'] = date('Y-m-d', $birthday);
        }
        if ($avatar != '') {
            $userInfo['avatar'] = $avatar;
        }
        if ($os == 'android') {
            $userInfo['android'] = 1;
        } elseif ($os == 'ios') {
            $userInfo['ios'] = 1;
        } else {
            $userInfo['web'] = 1;
        }
        if ($deviceId != '') {
            $userInfo['device_id'] = $deviceId;
        }
        $service = new SocialAccountService();
        $user = $service->createUser($userInfo);
        if ($user) {
            return response()->json(array('error'=>0, 'data'=>array(
                'user_id' => $user->id,
                'fb_id' => $user->fb_user_id,
                'name' => $user->name,
                'email' => $user->email,
                'gender' => $user->gender,
                'birthday' => $user->birthday,
                'avatar' => $user->avatar,
                'device_id' => $user->device_id
                ), 'message'=>''));
        } else {
            return response()->json(array('error'=>1002, 'data'=>'', 'message'=>'cannot save user'));
        }
    }

    /**
     * [socialLogin description]
     * @param  [type] $token [description]
     * @return [type]        [description]
     */
    public function socialLogin ($token) {
        $newAccessTokenUrl = 'https://graph.facebook.com/oauth/access_token?client_id='.config('cc.facebook_api.app_id').'&client_secret='.config('cc.facebook_api.app_secret').'&grant_type=fb_exchange_token&fb_exchange_token='.$token;
        \Log::info('Api_UserController|socialLogin|'.$newAccessTokenUrl);
        $newAccessToken = @file_get_contents($newAccessTokenUrl);
        if ($newAccessToken === false) {
            //return false
            \Log::error('Api_UserController|Cannot get new access_token|' . json_encode($newAccessTokenUrl));
            return response()->json(array('error'=>1005, 'data'=>'', 'message'=>'Unknown'));
        }
        $userAccessTokenUrl = config('cc.facebook_api.graph_url') . 'me?fields=id,age_range,birthday,email,gender,name,picture,timezone&' . $newAccessToken;

        $userInfo = @file_get_contents($userAccessTokenUrl);

        if ($userInfo === false) {
            return response()->json(array('error'=>1001, 'data'=>'', 'token'=>$token, 'message'=>'token is invalid'));
        } else {
            /*auth*/
            $userInfo = json_decode($userInfo);

            $userData = array();
            if (isset($userInfo->id) && $userInfo->id != '') {
                $userData['fb_user_id'] = $userInfo->id;
            }
            if (isset($userInfo->birthday) && $userInfo->birthday != '') {
                $userData['birthday'] = date('Y-m-d', strtotime($userInfo->birthday));
            }
            if (isset($userInfo->name) && $userInfo->name != '') {
                $userData['name'] = $userInfo->name;
            }
            if (isset($userInfo->email) && $userInfo->email != '') {
                $userData['email'] = $userInfo->email;
            }
            if (isset($userInfo->gender) && $userInfo->gender != '') {
                if ($userInfo->gender == 'male') {
                    $userData['gender'] = 1;
                } elseif ($userInfo->gender == 'female') {
                    $userData['gender'] = 2;
                } else {
                    $userData['gender'] = 0;
                }
            }
            if (isset($userInfo->picture->data) && $userInfo->picture->data != '') {
                $userData['avatar'] = $userInfo->picture->data->url;
            }
            if (isset($userInfo->device_id) && $userInfo->device_id != '') {
                $userData['device_id'] = $userInfo->device_id;
            }
            $accessToken = explode('&', $newAccessToken);
            $userData['fb_access_token'] = explode('=',$accessToken[0])[1];
            $userData['web'] = 1;

            $service = new SocialAccountService();
            $user = $service->createUser($userData);
            
            /*return*/
            if ($user) {
                auth()->login($user, true);
                return response()->json(array('error'=>0, 'data'=>$user, 'token'=>$token, 'message'=>''));
            }
            return response()->json(array('error'=>1002, 'data'=>'', 'token'=>$token, 'message'=>'login errror'));
        }
        
    }

    /**
     * [socialLogout description]
     * @return [type] [description]
     */
    public function socialLogout () {
        auth()->logout();
        return response()->json(array('error'=>0, 'data'=>'', 'message'=>''));
    }
}
