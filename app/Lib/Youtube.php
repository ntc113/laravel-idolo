<?php
namespace App\Lib;

class Youtube {

	/**
	 * The injected instance of \Google_Client
	 * @var \Google_Client
	 */
	protected $client;

	/**
	 * The instance of \Google_Service_YouTube instantiated in the constructor
	 * @var \Google_Service_YouTube
	 */
	protected $youtube;

	/**
	 * Constructor stores the passed Google Client object, sets a bunch of config options from the config file, and also
	 * creates and instance of the \Google_Service_YouTube class and stores this for later use.
	 *
	 * @param \Google_Client $client
	 */
	public function __construct(\Google_Client $client) {
		$this->client = $client;
		// $this->client->setApplicationName(config('cc.google_api.application_name'));
		$this->client->setClientId(config('cc.google_api.client_id'));
		$this->client->setClientSecret(config('cc.google_api.client_secret'));
		$this->client->setScopes(config('cc.google_api.scopes'));
		// $this->client->setAccessType(config('cc.google_api.access_type'));
		// $this->client->setApprovalPrompt(config('cc.google_api.approval_prompt'));
		$this->client->setRedirectUri(\URL::to(config('cc.google_api.redirect_uri')));
		// $this->client->setClassConfig('Google_Http_Request', 'disable_gzip', true);		
		$this->youtube = new \Google_Service_YouTube($this->client);
		$accessToken = $this->getLatestAccessTokenFromDB();
		if ($accessToken) {
			$this->client->setAccessToken($accessToken);
		}
	}

	/**
	 * Saves the access token to the database.
	 * @param $accessToken
	 */
	public function saveAccessTokenToDB($accessToken)
	{
		$data = array(
			'access_token' => $accessToken['access_token'],
			'token_type'	=> $accessToken['token_type'],
			'expires_in'	=> $accessToken['expires_in'],
			'created_at' => date('Y-m-d H:i:s', $accessToken['created']),
		);

		if(config('cc.google_api.auth') == true && \Auth::check()) {
			$data['user_id'] = \Auth::user()->id;
		}
		$ins = \DB::table(config('cc.google_api.table_name'))->insert($data);
		return $ins;
	}

	/**
	 * Returns the last saved access token, if there is one, or null
	 * @return mixed
	 */
	public function getLatestAccessTokenFromDB()
	{
		if(config('cc.google_api.auth')) {
			$latest = \DB::table(config('cc.google_api.table_name'))
				->where('user_id', \Auth::user()->id)
				->orderBy('created_at', 'desc')->first();
		} else {
			$latest = \DB::table(config('cc.google_api.table_name'))
				->orderBy('created_at', 'desc')
				->first();
		}
		
		if ($latest) {
			//current time with timezone
			$currentTime = strtotime(date('Y-m-d H:i:s', time()));
			$expired = (strtotime($latest->created_at) + ($latest->expires_in - 30)) < $currentTime;

			if ($expired) {
				return null;
			}
			return array(
				'access_token' => $latest->access_token,
				'expires_in' => $latest->expires_in
				);
		}
		return null;
	}

	/*
	 * Return JSON response of uploaded videos 
	 * @return json
	 */
	public function getUploads($maxResults=50)
	{
		$channelsResponse = $this->youtube->channels->listChannels('contentDetails', array(
			'mine' => 'true',
		));

		foreach ($channelsResponse['items'] as $channel)
		{
			$uploadsListId = $channel['contentDetails']['relatedPlaylists']['uploads'];

			$playlistItemsResponse = $this->youtube->playlistItems->listPlaylistItems('snippet', array(
																									'playlistId' => $uploadsListId,
																									'maxResults' => $maxResults
																								));

			$items = [];
			foreach ($playlistItemsResponse['items'] as $playlistItem) 
			{
				$video = [];
				$video['videoId'] 		= $playlistItem['snippet']['resourceId']['videoId'];
				$video['title'] 		= $playlistItem['snippet']['title'];
				$video['publishedAt'] 	= $playlistItem['snippet']['publishedAt'];

				array_push($items, $video);
			}
		}

		return $items;
	}

	/**
	 * Uploads the passed video to the YouTube account identified by the access token in the DB and returns the
	 * uploaded video's YouTube Video ID. Attempts to automatically refresh the token if it's expired.
	 *
	 * @param array $data As is returned from \Input::all() given a form as per the one in views/example.blade.php
	 * @return string The ID of the uploaded video
	 * @throws \Exception
	 */
	public function upload(array $data)
	{
		if(!$accessToken = $this->client->getAccessToken()) {
			\Log::error('UPLOAD VIDEO ERROR::You need an access token to upload');
			return false;
		}

		try{
			$videoPath = '';
			if (array_key_exists('videoPath', $data)) {
				$videoPath = public_path($data['videoPath']);
				if (!file_exists($videoPath)) {
					\Log::error('UPLOAD VIDEO ERROR:: file not found | ' . $videoPath);
					return false;
				}
			} elseif (array_key_exists('video', $data)) {
				$videoPath = $data['video']->getRealPath();
			}		    

		    // Create a snippet with title, description, tags and category ID
		    // Create an asset resource and set its snippet metadata and type.
		    // This example sets the video's title, description, keyword tags, and
		    // video category.
		    $snippet = new \Google_Service_YouTube_VideoSnippet();
		    if (array_key_exists('title', $data)) {
				$snippet->setTitle($data['title']);
			}
			if (array_key_exists('description', $data)) {
				$snippet->setDescription($data['description']);
			}
			if (array_key_exists('tags', $data)) {
				$snippet->setTags($data['tags']);
			}

		    // Numeric video category. See
		    // https://developers.google.com/youtube/v3/docs/videoCategories/list 
		    // Entertainment: 24. 17: sports
			if (array_key_exists('category_id', $data)) {
				$snippet->setCategoryId($data['category_id']);
			}

		    // Set the video's status to "public". Valid statuses are "public",
		    // "private" and "unlisted".
		    $status = new \Google_Service_YouTube_VideoStatus();
		    if (array_key_exists('status', $data)) {
				$status->privacyStatus = $data['status'];
			}

		    // Associate the snippet and status objects with a new video resource.
		    $video = new \Google_Service_YouTube_Video();
		    $video->setSnippet($snippet);
		    $video->setStatus($status);

		    // Specify the size of each chunk of data, in bytes. Set a higher value for
		    // reliable connection as fewer chunks lead to faster uploads. Set a lower
		    // value for better recovery on less reliable connections.
		    $chunkSizeBytes = 1 * 1024 * 1024;

		    // Setting the defer flag to true tells the client to return a request which can be called
		    // with ->execute(); instead of making the API call immediately.
		    $this->client->setDefer(true);

		    // Create a request for the API's videos.insert method to create and upload the video.
		    $insertRequest = $this->youtube->videos->insert("status,snippet", $video);

		    // Create a MediaFileUpload object for resumable uploads.
		    $media = new \Google_Http_MediaFileUpload(
		        $this->client,
		        $insertRequest,
		        'video/*',
		        null,
		        true,
		        $chunkSizeBytes
		    );
		    $media->setFileSize(@filesize($videoPath));


		    // Read the media file and upload it chunk by chunk.
		    $result = false;
		    $handle = @fopen($videoPath, "rb");
		    while (!$result && !feof($handle)) {
		      $chunk = @fread($handle, $chunkSizeBytes);
		      $result = $media->nextChunk($chunk);
		    }

		    @fclose($handle);
		    // If you want to make other calls after the file upload, set setDefer back to false
		    $this->client->setDefer(false);

		    return $result;
		} catch (\Google_Service_Exception $e) {
			\Log::error('UPLOAD VIDEO ERROR::' . $e->getMessage());
			return false;
		} catch (\Google_Exception $e) {
			\Log::error('UPLOAD VIDEO ERROR::' . $e->getMessage());
			return false;
		}
		return false;
	}

	/**
	 * Deletes a video from the account specified by the Access Token
	 * Attempts to automatically refresh the token if it's expired.
	 *
	 * @param $id of the video to delete
	 * @return true if the video was deleted and false if it was not
	 * @throws \Exception
	 */
	public function delete($video_id) {
		$accessToken = $this->client->getAccessToken();

		if (is_null($accessToken)) {
			\Log::error('LIB|Youtube|Delete|You need an access token to delete.');
			return false;
			// throw new \Exception('You need an access token to delete.');
		}

		// Attempt to refresh the access token if it's expired and save the new one in the database
		if ($this->client->getAccessToken()) {
			$accessToken = json_decode($accessToken);
			$refreshToken = $accessToken->refresh_token;
			$this->client->refreshToken($refreshToken);
			$newAccessToken = $this->client->getAccessToken();
			$this->saveAccessTokenToDB($newAccessToken);
		}

		$result = $this->youtube->videos->delete($video_id);

		if (!$result) {
			\Log::error('LIB|Youtube|Delete|Could not delete the video from the youtube account.');
			return false;
			// throw new \Exception("Couldn't delete the video from the youtube account.");
		}

		return $result->getId();

	}



	/**
	 * Method calls are passed on to the injected instance of \Google_Client. Used for calls like:
	 *
	 *      $authUrl = Youtube::createAuthUrl();
	 *      $accessToken = Youtube::authenticate($_GET['code']);
	 *
	 * @param $method
	 * @param $args
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		return call_user_func_array(array($this->client, $method), $args);
	}

}