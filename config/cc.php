<?php

return [
    'app_name'   => 'IDolo',
	'app_slogan' => 'Dancing Street Video',
	'app_avatar' => '/app/images/idolo-avatar.png',
	'google_api' => [
		'base_url' => '/',
		'application_name' => '',
		'client_id'   	=> '239175692539-2agqd5dcqvlod2roqf6krkbes98dknau.apps.googleusercontent.com',
		'client_secret' => 'W0AoOSUm9P6hOaPPHSwgI1uD',
		'scopes' => [
			'https://www.googleapis.com/auth/youtube.upload',
			'https://www.googleapis.com/auth/youtube.readonly',
			'https://www.googleapis.com/auth/youtube'
		],
		'redirect_uri' => '/admin/post',
		/**
		 * Access type
		 */
		'access_type' => 'offline',

		/**
		 * Approval prompt
		 */
		'approval_prompt' => 'auto',

		/**
		 * Table name for Accestokens 
		 */
		'table_name' => 'youtube_access_tokens',

		/** 
		 * Save and access the authentication tokens based on the Authenticated user. 
		 * Preferable when your system makes use of multiple users with Laravels authentication
		 */
		'auth' => false,
		// 'video_storage' => public_path('uploads'),
	    'firebase_server_key' => 'AIzaSyBlcgDtdjQFxhevYq3oykymym_7BpTKgHU'
	],
	'facebook_api' => [
		'graph_url' => 'https://graph.facebook.com/',
		'app_id' => '234920570236221',
		'app_secret' => '3c36df0a3460eacf8d0ebdb620be4ca9',
		'fanpage_id' => '583524488486094',
		'access_token' => '234920570236221|EY7X1EXN_cqeh2jj2CucLtZTD8Q',
        'admin_user_id' => 6,
	],
	'upload_path' => DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d') . DIRECTORY_SEPARATOR,
	'apiHashKey'  => '',
	'user_score'  => [
		'liked' 	=> 1,
		'commented' => 10,
		'shared' 	=> 5,
		'posted' 	=> [
			'text' 		=> 2,
			'image' 	=> 5,
			'video' 	=> 10
		]
	],
	'post_score'  => [
		'liked' 	=> 2,
		'commented' => 5,
		'shared' 	=> 5
	],
	'over_hot_post' => 24, //after 24h not hot
	'comments' => [
		'number_show' 	=> 2,
		'offset' 	=> 2
	]
];
