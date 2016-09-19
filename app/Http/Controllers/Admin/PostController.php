<?php

namespace App\Http\Controllers\Admin;

use View;
use Flash;
use Input;
use Response;
use Redirect;
use Illuminate\Http\Request;
use App\Services\Pagination;
use App\Http\Controllers\Controller;
use App\Repositories\Post\PostInterface;
use App\Repositories\Category\CategoryInterface;
use App\Models\Attachment;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\Post\PostRepository as Post;
use App\Repositories\Category\CategoryRepository as Category;

class PostController extends Controller
{
    
    protected $post;
    protected $category;
    protected $perPage;
    protected $yt;

    public function __construct(PostInterface $post, CategoryInterface $category)
    {
        View::share('active', 'blog');
        $this->post = $post;
        $this->category = $category;
        $this->perPage = 10;//config('fully.modules.post.per_page');
        $this->yt = new \App\Lib\Youtube(new \Google_Client);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $code = Input::get('code', '');
        if ($code != '') {
            $yt = new \App\Lib\Youtube(new \Google_Client);
            $accessToken = $yt->authenticate($code);
            $yt->saveAccessTokenToDB($accessToken);
            flash()->message('New session was created successfully');
        }
        $posts = $this->post->all();

        return view('backend.post.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $categories = $this->category->lists();

        return view('backend.post.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        if (Input::hasFile('attachments')) {
            $file = Input::file('attachments');
            $ext  = $file->getClientOriginalExtension();
            $fileName = $file->getClientOriginalName() . '_' . time() . '.' . $ext;
            $fileSize = $file->getClientSize();
            $max_size = $file->getMaxFilesize();
            if ($fileSize > $max_size) {
                return response()->json(array('error'=>1004, 'data'=>'', 'message'=>'overdata.'));
            }
            $destination = config('cc.upload_path');
            if (!file_exists(public_path($destination))) {
                if (!mkdir(public_path($destination), 0777, true)) {
                    return response()->json(array('error'=>3001, 'data'=>'', 'message'=>'can not upload file.'));
                }
            }
            if (!is_writable(public_path($destination))) {
                return response()->json(array('error'=>3002, 'data'=>'', 'message'=>'can not upload file.'));
            }
            $file->move(public_path($destination), $fileName);

            try {
                $userId = config('cc.facebook_api.admin_user_id');
                $user = \App\Models\User::find($userId);
                $type = 'video';
                //save post
                $post = new \App\Models\Post;
                $post->title = Input::get('title');
                $post->content = Input::get('content');
                $post->user_id = $userId;
                $post->display_name = $user->name;
                $post->avatar = $user->avatar;
                $post->category_id = 6;
                $post->type = $type;
                $post->os = 'web';
                $post->is_published = 1;
                $post->save();

                //save attachment
                $attachment = new \App\Models\Attachment;
                $attachment->src = $destination . $fileName;
                $attachment->post_id = $post->id;
                $attachment->type = $type;
                $attachment->save();

                flash()->message('Post was created successfully', 'success');
                return view('backend.post.show', compact('post', 'attachment', 'user'));
            } catch (Exception $e) {
                flash()->message('Created post is failure', 'error');
                return view('backend.post.create');
            }
            flash()->message('Created post is failure', 'error');
            return view('backend.post.create');
        } else {
            flash()->message('Created post is failure', 'error');
            return view('backend.post.create');
        }
        flash()->message('Created post is failure', 'error');
        return view('backend.post.create');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $post = $this->post->find($id);
        $attachment = $post->attachments()->first();
        $user = $post->user()->first();

        return view('backend.post.show', compact('post', 'attachment', 'user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $post = $this->post->find($id);
        $tags = null;

        foreach ($post->tags as $tag) {
            $tags .= ','.$tag->name;
        }

        $tags = substr($tags, 1);
        $categories = $this->category->lists();
        $attachments = $post->attachments()->first();

        return view('backend.post.edit', compact('post', 'tags', 'categories', 'attachments'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function update($id)
    {
        try {
            $this->post->update($id, Input::all());
            flash()->message('Post was successful updated', 'success');

            return langRedirectRoute('admin.post.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.post.edit')->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        // delete post from facebook
        $post = $this->post->find($id);
        if (!$post) {
            flash()->message('The post is not exist.', 'error');
            return langRedirectRoute('admin.post.index');
        }
        if (!deleteFbPost($post->fb_post_id)) {
            flash()->message('Cannot delete the post on the facebook.', 'error');
            return langRedirectRoute('admin.post.index');
        }
        // delete post from youtube
        if (!deleteYtPost($post->youtube_id)) {
            flash()->message('Cannot delete the post on the youtube.', 'error');
            return langRedirectRoute('admin.post.index');
        }
        // delete post from db
        try {
            $this->post->delete($id);
            flash()->message('Post was successful deleted', 'success');
        } catch (Exception $e) {
            flash()->message('Cannot delete the post.', 'error');
            return langRedirectRoute('admin.post.index');
        }

        return langRedirectRoute('admin.post.index');
    }

    public function confirmDestroy($id)
    {
        $post = $this->post->find($id);

        return view('backend.post.confirm-destroy', compact('post'));
    }

    public function togglePublish($id)
    {
        return $this->post->togglePublish($id);
    }

    /**
     * [publish video to youtube]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function publish($id) {
        $accessToken = $this->yt->getLatestAccessTokenFromDB();
        if ($accessToken === null) {
            $client = new \Google_Client();
            // $client->setRedirectUri(\URL::current());
            $yt = new \App\Lib\Youtube($client);
            $authUrl = $yt->createAuthUrl();
            return view('backend.post.publish', compact('authUrl'));
        }

        try {
            $post = $this->post->find($id);
            if (!$post) {
                flash()->message('The post is not exist', 'error');
                return view('backend.post.index');
            }

        } catch (Exception $e) {
            flash()->message('The post is not exist', 'error');
        }
        
        $attachment = $post->attachments()->first();
        return view('backend.post.publish', compact('post', 'attachment'));
    }

    /**
     * [uploadYoutube description]
     * @return [type] [description]
     */
    public function uploadYoutube() {
        try {
            $id = Input::get('id');
            $title = Input::get('title');

            $yt = new \App\Lib\Youtube(new \Google_Client);
            $result = $yt->upload(Input::all());
            if ($result !== false) {
                \Log::info('Youtube upload result:' . json_encode($result));
                try {
                    //update attachment
                    $attachment = Attachment::where('post_id', $id)->first();
                    $attachment->title = $title;
                    $attachment->src = 'http://youtube.com/watch?v=' . $result['id'];
                    $attachment->thumb = 'http://img.youtube.com/vi/' . $result['id'] . '/0.jpg';
                    $attachment->save();                    
                } catch (Exception $e) {
                    \Log::error('Admin_postcontroller|message:' . $e->getMessage());
                }
                
                //update to posts table
                $this->post->update($id, array('youtube_id'=>$result['id'], 'title'=>Input::get('title'), 'content'=>Input::get('description')));
                flash()->message('Publish to youtube is successful', 'success');
            }  else {
                flash()->message('Publish to Youtube is failure', 'error');    
            }
            
            return langRedirectRoute('admin.post.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.post.publish')->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * load form share post to fanpage
     * before: video was uploaded to youtube
     * 
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function shareFb ($id) {
        $post = $this->post->find($id);
        $attachment = $post->attachments()->first();
        return view('backend.post.postfacebook', compact('post', 'attachment'));
    }

    /**
     * method POST
     * 
     */
    public function postToFacebook () {
        $id = Input::get('id');
        $url = config('cc.facebook_api.graph_url') . config('cc.facebook_api.fanpage_id') . '/feed';
        $result = postData($url, Input::except('id'));
        \Log::info('facebook upload result:' . json_encode($result));
        if ($result !== false) {
            //update fb_post_id
            try {
                $result = json_decode($result);
                $post = $this->post->find($id);
                $post->fb_post_id = $result->id;
                $post->is_published = 1;
                $post->published_at = date('Y-m-d H:i:s', time());
                $post->save();
                flash()->message('Publish to facebook is successful', 'success');

                // push notification to all user
                $url = url('/api/fcm/pushnotification');
                $postData = [
                    'message'=> config('cc.app_name') . ' have a new post',
                    'action' => 'upload',
                    'object' => 'new video'
                ];
                $pushResult = postData($url, $postData);
                \Log::info('Admin_postcontroller|Pushnotification|result:' . $pushResult);

                // push notification to owner
                $postData = [
                    'message'=> 'Your post have been published.',
                    'action' => 'upload',
                    'object' => 'new video',
                    'device_id' => \App\Models\User::find($post->user_id)->device_id,
                    'post_id'   => $post->id
                ];
                $pushResult = postData($url, $postData);
                \Log::info('Admin_postcontroller|Pushnotification|result:' . $pushResult);
                return langRedirectRoute('admin.post.index');
            } catch (Exception $e) {
                \Log::error('Admin_postcontroller|message:' . $e->getMessage());
            }

        }  else {
            flash()->message('Publish to facebook is failure', 'error');    
        }
        flash()->message('Publish to facebook is failure', 'error');
        return langRedirectRoute('admin.post.index');
    }
}
