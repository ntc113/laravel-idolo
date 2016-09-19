<?php

namespace App\Http\Controllers;

use View;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    
    public function __construct() {

    }

    public function index (Request $request) {
    	$name = $request->get('name', '');
    	$email = $request->get('email', '');
    	$subject = $request->get('subject', '');
    	$message = $request->get('message', '');

    	if ($name == '' || $email == '' || $subject == '' || $message == '') {
    		return response()->json(array('error'=>1001, 'message'=> 'invalid param'));
    	}
    	try {
    		$contact = new \App\Models\Contact;
	    	$contact->name = $name;
	    	$contact->email = $email;
	    	$contact->subject = $subject;
	    	$contact->message = $message;

	    	// 1 email chi duoc gui toi da 3 contact trong ngay	
	    	$contactedInDay = $contact->where('email', $email)->where('created_at','>=', date('Y-m-d 00:00:00'))->count();
	    	if ($contactedInDay < 3) {
	    		$contact->save();
	    		return response()->json(array('error'=>0, 'message'=> 'Your contact is sent successfully'));
	    	}
	   		return response()->json(array('error'=>1003, 'message'=> 'you cannot send over 3 contacts per day'));
    	} catch (Exception $e) {
    		return response()->json(array('error'=>2001, 'message'=> 'cannot send the contact'));
    	}
    	return response()->json(array('error'=>1005, 'message'=> 'Unknown error'));
    }
}
