<?php
namespace App\Lib;

class NtcResult {
	protected $error;
	protected $data;
	protected $message;

	public function __construct($error, $data, $message) {
		$this->error = $error;
		$this->data = $data;
		$this->message = $message;
	}

	public function getError() {
		return $this->error;
	}

	public function setError($error) {
		$this->error = $error;
	}

	public function getData() {
		return $this->data;
	}

	public function setData($data) {
		$this->data = $data;
	}

	public function getMessage() {
		return $this->message;
	}

	public function setMessage($message) {
		$this->message = $message;
	}

	public function showResult () {
		return response()->json(array('error'=>$this->error, 'data'=>$this->data, 'message'=>$this->message));
	}
}