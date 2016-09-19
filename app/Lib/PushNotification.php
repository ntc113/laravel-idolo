<?php
namespace App\Lib;

class PushNotification 
{
	protected $url = url('/api/fcm/pushnotification');
	protected $pushData;

	public function __construct($pushData) {
		$this->pushData = $pushData;
	}

	public function setUrl ($url) {
		$this->url = $url;
	}

	public function setPushData ($pushData) {
		$this->pushData = $pushData;
	}

	public function pushNotification () {
		$pushResult = postData($this->url, $this->pushData);
		return $pushResult;
	}
}