<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\Device;
use App\Models\User;

class FcmController extends Controller
{
	public function index() {
		return view('api.gcm.index');
	}
	
	/**
	 * [pushNotification description]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function pushNotification(Request $request) {
		$message 	= $request->get('message', '');
		$title 		= $request->get('title', 'You have a message.');
		$icon 		= $request->get('icon', url(config('cc.app_avatar')));
		$action 	= $request->get('action', '');
		$object 	= $request->get('object', '');
		$postId 	= $request->get('post_id', '');

		$registration_ids = $request->get('device_id', array());
		if (empty($registration_ids)) {
			$devices = User::whereNotNull('device_id')->get(array('device_id'));
			foreach ($devices as $row) {
				$registration_ids[] = $row->device_id;
			}
		}		

		// Set POST variables
        $url = 'https://fcm.googleapis.com/fcm/send';

        $fields = array(
			'registration_ids' => $registration_ids,
			'notification' => array(
				'body' 		=> array(
					'message'	=> $message,
					'title'     => $title,
					'icon'		=> $icon,
					'action'	=> $action,
					'object'	=> $object,
					'post_id'	=> $postId
				),
			),
		);

		$headers = array(
			'Authorization: key=' . config('cc.google_api.firebase_server_key'),
			'Content-Type: application/json'
		);
		
		// Open connection
		$ch = curl_init();

		// Set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

		// Disabling SSL Certificate support temporarly
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

		// Execute post
		$result = curl_exec($ch);
		if ($result === FALSE) {
			die('Curl failed: ' . curl_error($ch));
		}

		// Close connection
		curl_close($ch);
		\Log::info('FCM_pushnotification|Result:'.$result);
		echo $result; die;
	}

    /**
     * [registerDevice description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function registerDevice (Request $request) {
    	$userId = $request->get('user_id', 0);
    	$deviceId = $request->get('device_id', '');
    	
    	// validate param
    	if ($deviceId == '' || $userId == 0) {
    		\Log::error('API_FcmController|RegisterDevice|' . $deviceId . '|' . $userId);
    		return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'invalid params'));
    	}

    	// update device id
    	try {
    		$user = User::find($userId);
    		if (!$user) {
    			return response()->json(array('error'=>1003, 'data'=>'', 'message'=>'cannot find user.'));
    		} else {
    			$user->device_id = $deviceId;
	    		if ($user->save()) {
	    			return response()->json(array('error'=>0, 'data'=>'', 'message'=>'register success.'));
	    		}
    		}
    	} catch (Exception $e) {
    		\Log::error('API_FcmController|RegisterDevice|' . $e->getMessage());
    		return response()->json(array('error'=>2001, 'data'=>'', 'message'=>'cannot register device.'));
    	}

    	return response()->json(array('error'=>1005, 'data'=>'', 'message'=>'unkown error.'));
    }
}
