<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\Device;
use App\Models\User;

class GcmController extends Controller
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
		$message = $request->get('message', '');
		$title = $request->get('title', '');
		$nameapp = $request->get('nameapp', '');
		$badge = $request->get('badge', '');
		$sound = $request->get('sound', '');

		$registration_ids = $request->get('registration_ids', array());
		if (empty($registration_ids)) {
			$devices = User::whereNotNull('device_id')->get(array('device_id'));
			foreach ($devices as $row) {
				$registration_ids[] = $row->registration_id;
			}
		}		

		// Set POST variables
        $url = 'https://android.googleapis.com/gcm/send';

        $fields = array(
			'registration_ids' => $registration_ids,
			'data' => array(
				'body' 		=> $message,
				'title'     => $title,
	            'nameapp'   => $nameapp,
	            'badge'     => $badge,
	            "sound"     => $sound
			),
		);

		$headers = array(
			'Authorization: key=AIzaSyC8yHL6hprhuFnQt7Fmehf6Xdwf19vbWLU',
			'Content-Type: application/json'
		);
		// Open connection
		$ch = curl_init();

		// Set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

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
    	if ($deviceId == '' || $userId) {
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
    		return response()->json(array('error'=>2001, 'data'=>'', 'message'=>'cannot register device.'));
    	}

    	return response()->json(array('error'=>1005, 'data'=>'', 'message'=>'unkown error.'));
    }
}
