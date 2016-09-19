<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Input;
use Validator;
use Redirect;
use Session;
use Log;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Attachment;
use App\Models\User;
use App\Models\PostLike;
use App\Models\PostComment;
use App\Models\PostShare;
use App\Repositories\Post\PostInterface;
use GuzzleHttp\Client;

class PostController extends Controller
{
    protected $post;
    protected $category;
    protected $perPage;
    protected $yt;

    public function __construct(PostInterface $post) {
        $this->post = $post;
    }

    /**
     * get list posts by $request
     * @param  Request $request [description]
     * @return JSON           [description]
     */
    function getPosts (Request $request) {
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 10);
        $order = $request->get('order_by', 'published_at');
        $by = $request->get('by', 'DESC');
        $user = $this->verifyAuth($request);

        return $this->listPost($offset, $limit, $order, $by, $user);
    }


    /**
     * list post order by score DESC
     * @param  integer $offset [description]
     * @param  integer $limit  [description]
     * @return [json]          [description]
     */
    public function getTopPosts(Request $request) {
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 10);
        $user = $this->verifyAuth($request);
        
        return $this->listPost($offset, $limit, 'score', 'DESC', $user);
    }
    /** 
    * list hot post
    * $offset   Integer
    * $limit    Integer
    * $order    String  (columnname)
    * $by       String (ASC|DESC)
    * return array object
    */
    public function getHotPosts($offset = 0, $limit = 4) {
        $overtime = config('cc.over_hot_post') * 60 * 60;
        $query = Post::where('is_published', 1)->where('created_at','>=', date('Y-m-d h:i:s')-$overtime)->whereNotNull('fb_post_id')->orderBy('score', 'DESC');

        $count = $query->count();
        if ($offset < 0 || $limit < 0) {
            return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'Param invalid'));
        } elseif ($offset > $count) {
            return response()->json(array('error'=>1003, 'data'=>'', 'message'=>'overdata'));
        } else {
            $query = $query->offset($offset)->limit($limit);
        }
        $posts = $query->get();
        if (!$posts) {
            return response()->json(array('error'=>1002, 'data'=>'', 'message'=>'Cannot find any post'));
        }
        foreach ($posts as $p) {
            /*attachments*/
            if ($p->type != 'text') {
                $attachments = Attachment::where('post_id', $p->id)->get();
                $p->attachments = $attachments;
            }
            /*category*/
            $category = Category::find($p->category_id);
            $p->category_name = $category['name'];
            
            /*is_liked*/
            if (auth()->check()) {
                $userId = auth()->user()->id;
                $p->is_liked = PostLike::where('post_id', $p->id)->where('user_id', $userId)->first();
            }
        }
        return response()->json(array('error'=>0,'count'=>$posts->count(), 'data'=>$posts, 'message'=>''));
    }

    /** 
    * get postinfo by id 
    * $postId integer postId
    * return object postinfo
    */
    public function getPostById ($postId) {
    	$post = Post::find($postId);//->tags;

        if (!$post) {
            return response()->json(array('error'=>1002, 'data'=>'', 'message'=>'Cannot find post'));
        }
    	$post->category = Post::find($postId)->category;
    	$post->tags = Post::find($postId)->tags;
    	// $post->attachments = Attachment::where('post_id', $postId)->select(array('src'))->get();
        if ($post->type != 'text') {
            $attachments = Attachment::where('post_id', $postId)->get();
            $post->attachments = $attachments;
        }
        /*category*/
        $category = Category::find($post->category_id);
        $post->category_name = $category['name'];

        /*comment*/
        $commentCount = $post->commented;
        $offset = ($commentCount > config('cc.comments.number_show')) ? $commentCount-config('cc.comments.number_show'): 0;
        $comments = PostComment::where('post_id', $postId)->orderBy('id', 'ASC')
                            ->offset($offset)
                            ->limit(config('cc.comments.number_show'))
                            ->get(array('id', 'content', 'user_id', 'fb_user_id', 'display_name', 'avatar', 'created_at', 'updated_at'));
        $post->comments = array(
            'offset'    => $offset,
            'data'      => $comments, 
            'count'     => $commentCount,
            'moreComment'   => ($offset) ? true : false,
            'showReply'     => false
            );

        /*is_liked*/
        if (auth()->check()) {
            $userId = auth()->user()->id;
            $post->is_liked = PostLike::where('post_id', $postId)->where('user_id', $userId)->first();
        }
    	return response()->json(array('error'=>0, 'data'=>$post, 'message'=>''));
    }

    /** 
    * get postinfo by slug 
    * $postSlug string
    * return object
    */
    public function getPostBySlug ($postSlug) {
    	$post = Post::where('slug', $postSlug)->first();
    	if (!$post) {
    		return response()->json(array('error'=>1002, 'data'=>'', 'message'=>'Cannot find post'));
    	}
        /*attachment*/
        if ($post->type != 'text') {
            $attachments = Attachment::where('post_id', $postId)->get();
            $post->attachments = $attachments;
        }
        /*category*/
        $category = Category::find($post->category_id);
        $post->category_name = $category['name']; 

        /*is_liked*/
        if (auth()->check()) {
            $userId = auth()->user()->id;
            $post->is_liked = PostLike::where('id', $postId)->where('user_id', $userId)->first();
        }

    	return response()->json(array('error'=>0, 'data'=>$post, 'message'=>''));
    }

    /** 
    * get list post by category id
    * $categoryId integer
    * $offset   Integer
    * $limit    Integer
    * $order    String  (columnname)
    * $by       String (ASC|DESC)
    * return array object
    */
    public function getPostsByCategoryId ($categoryId, $offset = 0, $limit = 0, $order='id', $by='DESC') {
        $query = Post::where('category_id', $categoryId)->where('is_published', 1)->orderBy($order, $by);
        $count = $query->count();
        if ($offset < 0 || $limit < 0) {
            return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'Param invalid'));
        } elseif ($offset > $count) {
            return response()->json(array('error'=>1003, 'data'=>'', 'message'=>'overdata'));
        } else {
            $posts = $query->offset($offset)->limit($limit)->get();
        }
        if (!$posts) {
            return response()->json(array('error'=>1003, 'data'=>'', 'message'=>'Cannot find any post'));
        }
        $count = 0;
        foreach ($posts as $p) {
            $count += 1;
            /*attachments*/
            if ($p->type != 'text') {
                $attachments = Attachment::where('post_id', $p->id)->get();
                $p->attachments = $attachments;
            }
            /*category*/
            $category = Category::find($p->category_id);
            $p->category_name = $category['name'];

            /*is_liked*/
            if (auth()->check()) {
                $userId = auth()->user()->id;
                $p->is_liked = PostLike::where('post_id', $p->id)->where('user_id', $userId)->first();
            }
            $comments = PostComment::where('post_id', $p->id)->orderBy('id', 'DESC')->limit(config('cc.comments.number_show'))->get(array('id', 'content', 'user_id', 'display_name', 'avatar'));
            $p->comments = array('offset'=>config('cc.comments.offset'), 'data'=>$comments);
        }
        $count = $query->count();
        return response()->json(array('error'=>0,'count'=>$count, 'data'=>$posts, 'message'=>''));
    }
    /** 
    * get list usport post
    * $offset   Integer
    * $limit    Integer
    * $order    String  (columnname)
    * $by       String (ASC|DESC)
    * return array object
    */
    public function getHotPostsbyCategoryId($categoryId, $offset = 0, $limit = 4) {
        $query = Post::where('category_id', $categoryId)->where('is_published', 1)->orderBy('score', 'DESC');
        $count = $query->count();
        if ($offset < 0 || $limit < 0) {
            return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'Param invalid'));
        } elseif ($offset > $count) {
            return response()->json(array('error'=>1003, 'data'=>'', 'message'=>'overdata'));
        } else {
            $posts = $query->offset($offset)->limit($limit)->get();
        }
        if (!$posts) {
            return response()->json(array('error'=>1003, 'data'=>'', 'message'=>'Cannot find any post'));
        }
        $count = 0;
        foreach ($posts as $p) {
            $count += 1;
            /*attachments*/
            if ($p->type != 'text') {
                $attachments = Attachment::where('post_id', $p->id)->get();
                $p->attachments = $attachments;
            }
            /*category*/
            $category = Category::find($p->category_id);
            $p->category_name = $category['name'];
            
            /*is_liked*/
            if (auth()->check()) {
                $userId = auth()->user()->id;
                $p->is_liked = PostLike::where('post_id', $p->id)->where('user_id', $userId)->first();
            }
            $comments = PostComment::where('post_id', $p->id)->orderBy('id', 'DESC')->limit(config('cc.comments.number_show'))->get(array('id', 'content', 'user_id', 'display_name', 'avatar'));
            $p->comments = array('offset'=>config('cc.comments.offset'), 'data'=>$comments);
        }
        $count = $query->count();
        return response()->json(array('error'=>0,'count'=>$count, 'data'=>$posts, 'message'=>''));
    }

    /** 
    * get list post by category id
    * $categoryId integer
    * $offset   Integer
    * $limit    Integer
    * $order    String (columnname)
    * $by       String (ASC|DESC)
    * return array object
    */
    public function getPostsByCategorySlug ($categorySlug, $offset = 0, $limit = 0, $order='id', $by='DESC') {
        $category = Category::where('slug', $categorySlug)->first();
        return $this->getPostsByCategoryId ($category->id, $offset, $limit, $order, $by);
    }

    /** 
    * get postinfo by id 
    * $postId integer postId
    * return object postinfo
    */
    public function getPostByUserId ($userId = 0, $offset = 0, $limit = 10) {
        // verify user
        if ($userId == 0) {
            return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'Invalid param'));
        }
        $post = Post::where('is_published', 1)->where('user_id', $userId)->orderBy('id', 'DESC');
        if (!$post) {
            return response()->json(array('error'=>1002, 'data'=>'', 'message'=>'Cannot find post'));
        }
        $count = $post->count();
        if ($offset < 0 || $limit < 0) {
            return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'Param invalid'));
        } elseif ($offset > $count) {
            return response()->json(array('error'=>1003, 'data'=>'', 'message'=>'overdata'));
        }
        $post = $post->limit($limit)->offset($offset)->get();

        foreach ($post as $p) {
            $p->category = Post::find($p->id)->category;
            $p->tags = Post::find($p->id)->tags;

            if ($p->type != 'text') {
                $attachments = Attachment::where('post_id', $p->id)->get();
                $p->attachments = $attachments;
            }

            /*category*/
            $category = Category::find($p->category_id);
            $p->category_name = $category['name'];

            /*is_liked*/
            if (auth()->check()) {
                $userId = auth()->user()->id;
                $p->is_liked = PostLike::where('post_id', $p->id)->where('user_id', $userId)->first();
            }
        }
        
        return response()->json(array('error'=>0, 'total_posts'=>$count, 'data'=>$post, 'message'=>''));
    }

    /**
     * API create post
     * @param  string
     * @return json
     */
    public function createPost(Request $request) {
        $type = $request->get('type', 'video');
        $content = $request->get('content', '');
        $categoryId = $request->get('category_id', 6);
        $username = $request->get('username', '');
        $userAvatar = $request->get('user_avatar', '');
        $isPublished = $request->get('is_published', 0);
        $os = $request->get('os', 'android');
        $attachmentsRequest = $request->get('attachments', null);

        if ($content=='' || $categoryId==0) {
            return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'Param invalid'));
        }

        // verify userId
        if (false ===$user = $this->verifyAuth($request)) {
            return response()->json(array('error'=>1002, 'data'=>'', 'message'=>'you not logged in'));
        } else {
            $userId = $user['user_id'];
            $accessToken = $user['access_token'];
            $username = $user['username'];
            $userAvatar = $user['avatar'];
        }

        if (Input::hasFile('attachments')) {
            $file = $request->file('attachments');
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
                //save post
                $newPost = new Post;
                $newPost->content = $content;
                $newPost->user_id =  $userId;
                $newPost->display_name = $username;
                $newPost->avatar = $userAvatar;
                $newPost->category_id = $categoryId;
                $newPost->type = $type;
                $newPost->os = $os;
                // $newPost->source = $source;
                $newPost->is_published = $isPublished;
                $newPost->save();

                //save attachment
                $attachments = new Attachment;
                $attachments->src = $destination . $fileName;
                $attachments->post_id = $newPost->id;
                $attachments->type = $type;
                $attachments->save();

                // caculator score 
                $this->scorePosted($userId, $type);

                //auto approve
                $this->autoApprove($newPost->id);

                return response()->json(array('error'=>0, 'data'=>$newPost, 'message'=>'upload success'));
            } catch (Exception $e) {
                return response()->json(array('error'=>2001, 'data'=>'', 'message'=>'cannot save file.'));    
            }
        } else {
            return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'no file attach.'));
        }
    }

    /**
     * like post
     * @param  [integer] $postId [description]
     * @return [json]         [description]
     */
    public function likePost(Request $request) {
        $postId = $request->get('post_id', 0);
        $fbPostId = $request->get('fb_post_id', '');

        // verify userId
        if (false === $user = $this->verifyAuth($request)) {
            return response()->json(array('error'=>1002, 'data'=>'', 'message'=>'you not logged in'));
        } else {
            $userId = $user['user_id'];
            $accessToken = $user['access_token'];
            $username = $user['username'];
            $userAvatar = $user['avatar'];
        }

        $post = false;
        // verify postId
        if ($fbPostId == '' && $postId != 0) {
            $post = $this->post->find($postId);
            $fbPostId = $post->fb_post_id;
        }
        if ($fbPostId == '' || $postId == 0) {
            return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'invalid param'));
        }

        $url = config('cc.facebook_api.graph_url') . $fbPostId . '/likes';
        $postResult = postData($url, array('access_token'=>$accessToken));

        Log::info('accessToken:'.$accessToken);
        $result = json_decode($postResult);
        if (isset($result->success) && $result->success) {
            try {
                if ($postId == 0) {
                    $post = $this->post->getPostBy('fb_post_id', $fbPostId);
                    $postId = $post->id;
                }
                $postLike = new PostLike;
                $postLike->post_id = $postId;
                $postLike->user_id = $userId;
                $postLike->fb_user_id = User::find($userId)->fb_user_id;
                $postLike->fb_post_id = $fbPostId;
                $postLike->display_name = $username;
                $postLike->avatar = $userAvatar;
                if ($postLike->save()) {
                    $this->scoreLiked($userId, $postId);
                }
                // push notification to owner by device_id
                // if like your own post ==> not push notification
                \Log::info('Api_postcontroller|likePost|' . json_encode($post));
                if ($post) {
                    if ($post->user_id != $userId) {
                        $user = User::find($post->user_id);
                        // only push to mobile user
                        if (isset($user->device_id) && $user->device_id) {
                            $url = url('/api/fcm/pushnotification');
                            $pushData = [
                                'message'   => $username . ' liked your post',
                                'icon'      => $userAvatar,
                                'action'    => 'like',
                                'object'    => 'your post',
                                'device_id' => [$user->device_id],
                                'post_id'   => $postId
                            ];
                            $pushResult = postData($url, $pushData);
                            \Log::info('Api_postcontroller|likePost|Pushnotification|result:' . $pushResult);
                        }
                    }
                }
                
                return response()->json(array('error'=>0, 'data'=>'', 'message'=>''));
            } catch (Exception $e) {
                return response()->json(array('error'=>2001, 'data'=>'', 'message'=>'like post is failure'));
            }
        }

        return response()->json(array('error'=>1005, 'data'=>'', 'message'=>'unknown|'.$postResult));
    }
    /**
     * [unlikePost description]
     * @param  [type] $postId [description]
     * @return JSON
     */
    public function unlikePost(Request $request) {
        $postId = $request->get('post_id', 0);
        $fbPostId = $request->get('fb_post_id', '');

        // verify userId
        if (false === $user = $this->verifyAuth($request)) {
            return response()->json(array('error'=>1002, 'data'=>'', 'message'=>'you not logged in'));
        } else {
            $userId = $user['user_id'];
            $accessToken = $user['access_token'];
            $username = $user['username'];
            $userAvatar = $user['avatar'];
        }

        // verify postId
        if ($fbPostId == '' && $postId != 0) {
            $post = $this->post->find($postId);
            $fbPostId = $post->fb_post_id;
        }
        if ($fbPostId == '' || $postId == 0) {
            return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'invalid param'));
        }

        $url = config('cc.facebook_api.graph_url') . $fbPostId . '/likes';
        $result = postData($url, array('access_token'=>$accessToken));

        $result = json_decode($result);
        if (isset($result->success) && $result->success) {
            if ($postId == 0) {
                $postId = $this->post->getPostBy('fb_post_id', $fbPostId)->id;
            }
            PostLike::where('post_id', $postId)->where('user_id', $userId)->delete();
            $this->scoreLiked($userId, $postId, false);
            return response()->json(array('error'=>0, 'data'=>'', 'message'=>''));
        }

        return response()->json(array('error'=>1005, 'data'=>'', 'message'=>'unknown'));
    }

    /**
     * like comment
     * @param  [integer] $postId [description]
     * @return [json]         [description]
     */
    public function likeComment(Request $request) {
        $comment_id = $request->get('comment_id', '');
        $accessToken = $request->get('access_token', '');

        // verify postId
        if ($comment_id == '' || $accessToken == '') {
            return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'invalid param'));
        }
        // verify userId
        if (false === $user = $this->verifyAuth($request)) {
            return response()->json(array('error'=>1002, 'data'=>'', 'message'=>'you not logged in'));
        } else {
            $userId = $user['user_id'];
            $accessToken = $user['access_token'];
            $username = $user['username'];
            $userAvatar = $user['avatar'];
        }

        $url = config('cc.facebook_api.graph_url') . $comment_id . '/likes';
        $result = postData($url, array('access_token'=>$accessToken));

        $result = json_decode($result);
        if (isset($result->success) && $result->success) {
            return response()->json(array('error'=>0, 'data'=>'', 'message'=>''));
        }

        return response()->json(array('error'=>1005, 'data'=>'', 'message'=>'unknown'));
    }

    public function getListLike ($postId = 0) {
        echo 1; die;
        if ($postId == 0) {
            return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'invalid param'));
        }

        try {
            $postLike = PostLike::where('post_id', $postId)->get(array('user_id', 'username', 'user_avatar'));
            var_dump($postLike); die;

        } catch (Exception $e) {
            
        }
    }

    /**
     * [commentPost description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function commentPost (Request $request) {
        $fbPostId = $request->get('fb_post_id', '');
        $postId = $request->get('post_id', 0);
        $message = $request->get('content', '');

        // verify userId
        if (false === $user = $this->verifyAuth($request)) {
            return response()->json(array('error'=>1002, 'data'=>'', 'message'=>'you not logged in'));
        } else {
            $userId = $user['user_id'];
            $accessToken = $user['access_token'];
            $username = $user['username'];
            $userAvatar = $user['avatar'];
        }
        $post = false;
        // verify postId
        if ($fbPostId == '' && $postId != 0) {
            $post = $this->post->find($postId);
            $fbPostId = $post->fb_post_id;
        }
        if ($fbPostId == '' || $message == '' || $postId == 0) {
            return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'invalid param'));
        }

        $url = config('cc.facebook_api.graph_url') . $fbPostId . '/comments';
        $result = postData($url, array('message'=>$message, 'access_token'=>$accessToken));
        Log::info('comment post|' . json_encode($user) . '|' . $result);

        $result = json_decode($result);
        if (isset($result->id) && strlen($result->id) > 0) {
            try {
                if ($postId == 0) {
                    $post = $this->post->getPostBy('fb_post_id', $fbPostId);//->id;
                    if ($post) {
                        $postId = $post->id;
                    }
                }

                $comment = new PostComment;
                $comment->user_id = $userId;
                $comment->fb_user_id = User::find($userId)->fb_user_id;
                $comment->post_id = $postId;
                $comment->fb_post_id = $fbPostId;
                $comment->content = $message;
                $comment->fb_comment_id = $result->id;
                $comment->display_name = $username;
                $comment->avatar = $userAvatar;

                if ($comment->save()) {
                    // caculator score
                    $this->scoreCommented($userId, $postId);

                    // push notification to owner
                    // not push when user like own post
                    if ($post) {
                        if ($post->user_id != $userId) {
                            $user = User::find($post->user_id);
                            // only push to mobile user
                            if (isset($user->device_id) && $user->device_id) {
                                $url = url('/api/fcm/pushnotification');
                                $pushData = [
                                    'message'   => $username . ' commented your post',
                                    'icon'      => $userAvatar,
                                    'action'    => 'comment',
                                    'object'    => 'your post',
                                    'device_id' => [$user->device_id],
                                    'post_id'   => $postId
                                ];
                                $pushResult = postData($url, $pushData);
                                \Log::info('Api_postcontroller|Pushnotification|result:' . $pushResult);
                            }
                        }
                    }
                    return response()->json(array('error'=>0, 'data'=>$comment, 'message'=>''));
                }
            } catch (Exception $e) {
                return response()->json(array('error'=>2001, 'data'=>'', 'message'=>'cannot comment'));
            }
        }

        return response()->json(array('error'=>1005, 'data'=>'', 'message'=>'Unknown'));

    }

    /**
     * [sharePost description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function sharePost (Request $request) {
        $postId = $request->get('post_id', 0);
        $fbPostId = $request->get('fb_post_id', '');
        $fbSharePostId = $request->get('fb_share_post_id', '');

        $post = false;
        // verify postId
        if ($fbPostId == '' && $postId != 0) {
            $post = $this->post->find($postId);
            $fbPostId = $post->fb_post_id;
        }

        if ($fbPostId == '' || $postId == 0) {
            return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'invalid param'));
        }

        // verify userId
        if (false === $user = $this->verifyAuth($request)) {
            return response()->json(array('error'=>1002, 'data'=>'', 'message'=>'you not logged in'));
        }

        try {
            if ($postId == 0) {
                $post = $this->post->getPostBy('fb_post_id', $fbPostId);//->id;
                $postId = $post->id;
            }
            $share = new PostShare;
            $share->user_id = $user['user_id'];
            $share->post_id = $postId;
            $share->fb_post_id = $fbSharePostId;
            $share->display_name = $user['username'];
            $share->avatar = $user['avatar'];

            if ($share->save()) {
                // caculator score
                $this->scoreShared($user['user_id'], $postId);
                // push notification to all user
                \Log::info('Api_postcontroller|sharePost|' . json_encode($post));
                if ($post) {
                    if ($post->user_id != $user['user_id']) {
                        $user = User::find($post->user_id);
                        if (isset($user->device_id) && $user->device_id) {
                            $url = url('/api/fcm/pushnotification');
                            $pushData = [
                                'message'   => $user['username'] . ' shared your post',
                                'icon'      => $user['avatar'],
                                'action'    => 'share',
                                'object'    => 'your post',
                                'device_id' => [$user->device_id],
                                'post_id'   => $postId
                            ];
                            $pushResult = postData($url, $pushData);
                            \Log::info('Api_postcontroller|Pushnotification|result:' . $pushResult);
                        }
                    }
                }
                
                return response()->json(array('error'=>0, 'data'=>$share, 'message'=>''));
            }
        } catch (Exception $e) {
            return response()->json(array('error'=>1003, 'data'=>'', 'message'=>'cannot shared'));
        }
        return response()->json(array('error'=>1004, 'data'=>'', 'message'=>'cannot share post'));
    }

    /**
     * [getCommentsByPostId description]
     * @param  [type]  $postId [description]
     * @param  integer $offset [description]
     * @param  integer $limit  [description]
     * @param  string  $order  [description]
     * @param  string  $by     [description]
     * @return [type]          [description]
     */
    public function getCommentsByPostId($postId, $offset=0, $limit=5, $order='id', $by='ASC') {
        try {
            $query = PostComment::where('post_id', $postId)->orderBy($order, $by);
            $count = $query->count();
            if ($offset < 0 || $limit < 0) {
                return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'Param invalid'));
            } elseif ($offset > $count) {
                return response()->json(array('error'=>1003, 'data'=>'', 'message'=>'overdata'));
            }
            $comments = $query->offset($offset)->limit($limit)->get();
            if (!$comments) {
                return response()->json(array('error'=>1003, 'data'=>'', 'message'=>'Cannot find any post'));
            }
            return response()->json(array('error'=>0,'count'=>$count, 'data'=>$comments, 'message'=>''));
        } catch (Exception $e) {
            return response()->json(array('error'=>2001, 'data'=>'', 'message'=>'cannot get comment.'));
        }
        return response()->json(array('error'=>1005, 'data'=>'', 'message'=>'Unknown error.'));
    }

    /**
     * create new post 
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function newPost(Request $request) {
        $userId = $request->get('user_id', 0);
        $type = $request->get('type', 'text');
        $content = $request->get('content', '');
        $categoryId = $request->get('category_id', 6);
        $source = $request->get('source', '');
        $os = $request->get('os', 'web');
        $isPublished = $request->get('is_published', 0);
        $attachmentsRequest = $request->get('attachments', null);

        if ($content=='' || $categoryId==0) {
            return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'Param invalid'));
        }

        if ($attachmentsRequest === null) {
            return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'no file attach.'));
        }

        // verify userId
        if (!$user = $this->verifyAuth($request)) {
            return response()->json(array('error'=>1002, 'data'=>'', 'message'=>'you not logged in'));
        }
        $userId = $user['user_id'];
        $username = $user['username'];
        $userAvatar = $user['avatar'];

        try {
            //save post
            $newPost = new Post;
            $newPost->content = $content;
            $newPost->user_id =  $userId;
            $newPost->display_name = $username;
            $newPost->avatar = $userAvatar;
            $newPost->category_id = $categoryId;
            $newPost->type = $type;
            $newPost->source = $source;
            $newPost->os = $os;
            $newPost->is_published = ($isPublished) ? true : false;
            $newPost->save();

            //save attachment
            $src = json_decode( $attachmentsRequest[0]);
            $attachments = new Attachment;
            $attachments->src = $src->src;
            $attachments->post_id = $newPost->id;
            $attachments->type = $type;
            $attachments->save();

            // caculator score 
            $this->scorePosted($userId, $type);

            // push notification
            // $pushData = array('message'=>'USport có bài mới.', 'post_id'=>$newPost->id);
            // $this->pushNotification($pushData);
            
            // auto approve
            $this->autoApprove($newPost->id);

            return response()->json(array('error'=>0, 'data'=>$newPost, 'message'=>'upload success'));

        } catch (Exception $e) {
            return response()->json(array('error'=>2001, 'data'=>'', 'message'=>'cannot save file.'));    
        }
    }

    /**
     * API delete post
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function deletePost(Request $request) {
        $postId = $request->get('post_id', 0);
        // validate post id 
        if ($postId == 0) {
            return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'invalid param'));
        }
        $user = $this->verifyAuth($request);
        if (!$user) {
            return response()->json(array('error'=>1001, 'data'=>'', 'message'=>'invalid user'));
        }

        // find post by post_id and user_id
        $post = Post::whereId($postId)->whereUserId($user['user_id'])->first();
        if (!$post) {
            \Log::info('API_PostController|the post cannot found|' . $postId . '|' . $user['user_id']);
            return response()->json(array('error'=>1002, 'data'=>'', 'message'=>'permission denied.'));
        }
        // delete from fanpage
        $deleteFbPost = $this->deleteFbPost($post['fb_post_id']);
        if (!$deleteFbPost) {
            \Log::info('API_PostController|cannot delete on facebook|' . $post['fb_post_id']);
            return response()->json(array('error'=>1002, 'data'=>'', 'message'=>'permission denied.'));
        }
        // delete from db
        try {
            $deleteRow = $post->delete();
            return response()->json(array('error'=>0, 'data'=>'', 'message'=>'success.'));
        } catch (Exception $e) {
            \Log::info('API_PostController|' . $e->getMessage());
            return response()->json(array('error'=>2001, 'data'=>'', 'message'=>'permission denied.'));
        }
        // return
        return response()->json(array('error'=>1005, 'data'=>'', 'message'=>'Unknown error.'));
    }

    /**
     * get list post with condition
     * 
     * @param  [type] $offset [description]
     * @param  [type] $limit  [description]
     * @param  [type] $order  [description]
     * @param  [type] $by     [description]
     * @param  [type] $user   [description]
     * @return App\Models\Post
     */
    protected function listPost($offset, $limit, $order, $by, $user) {
        $posts = $this->post->getPosts(true, true, $offset, $limit, $order, $by);

        if (!$posts) {
            return response()->json(array('error'=>1005, 'data'=>'', 'message'=>'Unknown'));
        }
        $count = 0;
        foreach ($posts as $p) {
            $count += 1;
            /*attachment*/
            if ($p->type != 'text') {
                $p->attachments = Attachment::where('post_id', $p->id)->get();
            }
            /*category*/
            $category = Category::find($p->category_id);
            $p->category_name = $category['name'];

            /*is_liked*/
            $is_liked = false;
            if ($user !== false) {
                $is_liked = PostLike::where('post_id', $p->id)->where('user_id', $user['user_id'])->first();
            }
            $p->is_liked = ($is_liked) ? true : false;
            
            $commentCount = $p->commented;
            $offset = ($commentCount > config('cc.comments.number_show')) ? $commentCount-config('cc.comments.number_show'): 0;
            $comments = PostComment::where('post_id', $p->id)->orderBy('id', 'ASC')
                                ->offset($offset)
                                ->limit(config('cc.comments.number_show'))
                                ->get(array('id', 'content', 'user_id', 'fb_user_id', 'display_name', 'avatar', 'created_at', 'updated_at'));
            $p->comments = array(
                'offset'    => $offset,
                'data'      => $comments, 
                'count'     => $commentCount,
                'moreComment'   => ($offset) ? true : false,
                'showReply'     => false
                );
        }

        return response()->json(array('error'=>0,'count'=>$count, 'data'=>$posts, 'message'=>''));
    }

    /**
     * verify userinfo (name, avatar, accesstoken)
     * 
     * @param  Request $request [description]
     * @return App\Models\User|false
     */
    protected function verifyAuth(Request $request) {
        $userId = $request->get('user_id', 0);
        $accessToken = $request->get('access_token', '');
        if ($userId == 0) {
            if (!auth()->check()) {
                return false;
            }
            return ['user_id'=>auth()->user()->id, 'access_token'=>auth()->user()->fb_access_token, 'username'=>auth()->user()->name, 'avatar'=>auth()->user()->avatar];
        } else {
            $user = User::find($userId);
            if (!$user) {
                return false;
            }
            $accessToken = ($accessToken != '') ? $accessToken : $user->fb_access_token;
            return ['user_id'=>$userId, 'access_token'=>$accessToken, 'username'=>$user->name, 'avatar'=>$user->avatar];
        }
        return false;
    }

    /**
     * caculator score
     * @param  [type]  $userId    [description]
     * @param  [type]  $postId    [description]
     * @param  boolean $increment [description]
     * @return [type]             [description]
     */
    protected function scoreLiked ($userId, $postId, $increment=true) {
        try {
            $incrementFunction = ($increment) ? 'increment' : 'decrement';
            $post = Post::find($postId);
            $post->$incrementFunction('liked');
            $post->$incrementFunction('score', config('cc.post_score.liked'));

            $user = User::find($userId);
            $user->$incrementFunction('liked');
            $user->$incrementFunction('score', config('cc.user_score.liked'));            
        } catch (Exception $e) {
            \Log::error('PostController|scoreLiked|'.$e->getMessage());
        }
    }

    /**
     * [scoreCommented description]
     * @param  [type]  $userId    [description]
     * @param  [type]  $postId    [description]
     * @param  boolean $increment [description]
     * @return [type]             [description]
     */
    protected function scoreCommented ($userId, $postId, $increment=true) {
        try {
            $incrementFunction = ($increment) ? 'increment' : 'decrement';
            $post = Post::find($postId);
            $post->$incrementFunction('commented');
            $post->$incrementFunction('score', config('cc.post_score.commented'));

            $user = User::find($userId);
            $user->$incrementFunction('commented');
            $user->$incrementFunction('score', config('cc.user_score.commented'));            
        } catch (Exception $e) {
            \Log::error('PostController|scoreCommented|'.$e->getMessage());
        }
    }

    /**
     * [scoreShared description]
     * @param  [type]  $userId    [description]
     * @param  [type]  $postId    [description]
     * @param  boolean $increment [description]
     * @return [type]             [description]
     */
    protected function scoreShared ($userId, $postId, $increment=true) {
        try {
            $incrementFunction = ($increment) ? 'increment' : 'decrement';
            $post = Post::find($postId);
            $post->$incrementFunction('shared');
            $post->$incrementFunction('score', config('cc.post_score.shared'));

            $user = User::find($userId);
            $user->$incrementFunction('shared');
            $user->$incrementFunction('score', config('cc.user_score.shared'));
        } catch (Exception $e) {
            \Log::error('PostController|scoreShared|'.$e->getMessage());
        }
    }

    /**
     * [scorePosted description]
     * @param  [type]  $userId    [description]
     * @param  [type]  $type      [description]
     * @param  boolean $increment [description]
     * @return [type]             [description]
     */
    protected function scorePosted ($userId, $type, $increment=true) {
        try {
            $incrementFunction = ($increment) ? 'increment' : 'decrement';

            $user = User::find($userId);
            $user->$incrementFunction('posted');
            $user->$incrementFunction('score', config('cc.user_score.posted.' . $type));            
        } catch (Exception $e) {
            \Log::error('PostController|scorePosted|'.$e->getMessage());
        }
    }

    /**
     * [upload description]
     * @param  Request $request [description]
     * @return [json]           [description]
     */
    public function upload(Request $request) {
        $config = new \Flow\Config();
        $config->setTempDir(public_path('uploads' . DIRECTORY_SEPARATOR . 'chunksfile'));
        $file = new \Flow\File($config);

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if ($file->checkChunk()) {
                header("HTTP/1.1 200 Ok");
            } else {
                header("HTTP/1.1 204 No Content");
                return ;
            }
        } else {
          if ($file->validateChunk()) {
              $file->saveChunk();
          } else {
              // error, invalid chunk upload request, retry
              header("HTTP/1.1 400 Bad Request");
              return ;
          }
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

        $fileName = $destination . time() . '_' . $request->get('flowFilename', '');
        if ($file->validateFile() && $file->save(public_path($fileName))) {
            // File upload was completed
            return response()->json(array('type'=>'video', 'src'=>$fileName));
        } else {
            // This is not a final chunk, continue to upload
            return response()->json(array('error'=>1005, 'src'=>$fileName));
        }
    }

    /**
     * push notification to device
     * @param  [type] $pushData [description]
     * @return [type]           [description]
     */
    protected function pushNotification($pushData) {
        $url = url('/api/gcm/pushnotification');

        $result = postData($url, $pushData);
        if ($result === FALSE) {
             /* Handle error */ 
             Log::error("push notification has error");
             return false;
         }

        Log::info("PUSH :: " . json_encode($result));
        return true;
    }

    private function autoApprove($postId) {
        // upload to youtube
        // upload to facebook
        // return
    }

    /**
     * delete fb post id
     * @param  [type] $fbPostId [description]
     * @return [type]           [description]
     */
    private function deleteFbPost($fbPostId) {
        $adminAccessToken = User::find(config('cc.facebook_api.admin_user_id'));

        // get page access token
        $pageAccessToken = '';
        \Log::info('API_PostController|deletePost|adminInfo:'.json_encode($adminAccessToken->fb_access_token));
        $url = config('cc.facebook_api.graph_url') . config('cc.facebook_api.fanpage_id') . '?fields=access_token&access_token='.$adminAccessToken->fb_access_token;
        $page = getData($url);
        \Log::info('API_PostController|deletePost|url:'.$url);
        if ($page['error'] != 0) {
            \Log::error('API_PostController|cannot get page access token|' . json_encode($page));
            return false;
        }
        $page = $page['data'];
        \Log::info('API_PostController|deletePost|'.json_encode($page));
        $pageAccessToken = $page->access_token;
        if ($pageAccessToken == '') {
            return false;
        }

        $url = config('cc.facebook_api.graph_url') . $fbPostId;
        \Log::info('API_PostController|DeletePost|PageAccessToken:'.$pageAccessToken);
        $postData = array('access_token'=>$pageAccessToken);

        return postData($url, $postData, 'DELETE');
    }

}
