<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Redirect;
use Sentinel;
use App\Http\Requests;
use App\Services\SocialAccountService;

use Laravel\Socialite\Facades\Socialite;

class FacebookController extends Controller
{
    /**
     * [facebookLogin description]
     * @return [type] [description]
     */
    public function facebookLogin()
	{
	    return Socialite::with('facebook')->redirect();
	}

    /**
     * [facebookLoginCallback description]
     * @param  SocialAccountService $service [description]
     * @return [type]                        [description]
     */
	public function facebookLoginCallback(SocialAccountService $service)
    {
        $user = $service->createOrGetUser(Socialite::driver('facebook')->user());

        auth()->login($user);
        /*$u = Auth::user();
        echo $u['email'];
        echo '<pre>';
        var_dump($u);
        die;*/
        
        return Redirect::route('admin.dashboard');
    }

    /**
     * get data from facebook and insert to database
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function syncData (Request $request) {
        $accessToken = $request->get('access_token', '');

        // validate access token
        if ($accessToken == '') {
            return 'invalid access_token';
        }

        $url = config('cc.facebook_api.graph_url') . config('cc.facebook_api.fanpage_id');
        $url .= '/feed?fields=message,created_time,type,shares,updated_time,likes.summary(true){name,picture{url}},comments.summary(true),attachments,source&access_token='. $accessToken;

        // get data from fanpage
        $fbData = getData($url);
        if ($fbData['error'] != 0) {
            return 'error: ' . $fbData['error'];
        }

        $fbData = $fbData['data']->data;
        // save data to db
        foreach ($fbData as $p) {
            if ($p->type == 'video') {
                $post = new \App\Models\Post;

                // check post exist
                // return null if not exist
                $check = \App\Models\Post::whereFbPostId($p->id)->first();
                if (!$check) {
                    /*try {*/
                        $post->fb_post_id = $p->id;
                        if (isset($p->message)) {
                            $post->content = $p->message;
                        }
                        $post->user_id = 1; // adminpage
                        $post->display_name = 'Usport'; // adminpage
                        $post->category_id = 7; // facebook
                        $post->category_name = 'Facebook';
                        $post->liked = $p->likes->summary->total_count;
                        
                        if (isset($p->shares)) {
                            $post->shared = $p->shares->count;                    
                        }
                        $post->type = $p->type;
                        $post->created_at = date('Y-m-d h:i:s', strtotime($p->created_time));
                        $post->updated_at = date('Y-m-d h:i:s', strtotime($p->updated_time));

                        // save post to db
                        $post->save();

                        // execute comments
                        // save comment to db
                        foreach ($p->comments->data as $cm) {
                            $comment = new \App\Models\PostComment;
                            $comment->fb_comment_id = $cm->id;
                            $comment->post_id = $post->id;
                            $comment->fb_post_id = $p->id;
                            $comment->content = $cm->message;
                            $comment->fb_user_id = $cm->from->id;
                            $comment->display_name = $cm->from->name;
                            $comment->created_at = date('Y-m-d h:i:s', strtotime($cm->created_time));

                            //save
                            $comment->save();
                        }

                        // execute likes
                        // save likes to db
                        foreach ($p->likes->data as $l) {
                            $like = new \App\Models\PostLike;
                            $like->post_id = $post->id;
                            $like->fb_post_id = $p->id;
                            $like->display_name = $l->name;
                            $like->fb_user_id = $l->id;
                            if (isset($l->picture)) {
                                $like->avatar = $l->picture->data->url;
                            }

                            //save
                            $like->save();
                        } 
                        // execute attachments
                        // save attachments to db
                        if (isset($p->attachments)) {
                            foreach ($p->attachments->data as $at) {
                                if (isset($at->type) && substr($at->type, 0, 5) == 'video') {
                                    $attachment = new \App\Models\Attachment;
                                    $attachment->type = 'video';
                                    $attachment->post_id = $post->id;
                                    $attachment->fb_post_id = $p->id;
                                    if (isset($at->title)) {
                                        $attachment->title = $at->title;
                                    }
                                    if (isset($at->url)) {
                                        $attachment->src = $p->source;
                                    }
                                    $attachment->thumb = $at->media->image->src;
                                    $attachment->save();
                                }
                            }
                        }
                        
                    /*} catch (Exception $e) {
                        return $e->getMessage();
                    }*/
                }
            }
        }
        
        return 'success';
    }
}
