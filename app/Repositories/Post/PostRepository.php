<?php

namespace App\Repositories\Post;

use App\Models\Post;
use Config;
use Response;
use Sentinel;
use App\Models\Tag;
use App\Models\PostTag;
use App\Models\Category;
use App\Models\Attachment;
use App\Models\User;
use Illuminate\Support\Str;
use Event;
use Image;
use File;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Exceptions\Validation\ValidationException;

/**
 * Class PostRepository.
 *
 * @author
 */
class PostRepository extends RepositoryAbstract implements PostInterface, CrudableInterface
{
    protected $width;
    protected $height;
    protected $thumbWidth;
    protected $thumbHeight;
    protected $imgDir;
    protected $perPage;
    protected $post;
    /**
     * Rules.
     *
     * @var array
     */
    protected static $rules = [
        // 'title' => 'required',
        'content' => 'required',
    ];

    /**
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        // $config = Config::get('fully');
        $this->perPage = 10;//$config['per_page'];
        $this->width = 460;//$config['modules']['post']['image_size']['width'];
        $this->height = 270;//$config['modules']['post']['image_size']['height'];
        $this->thumbWidth = 32;//$config['modules']['post']['thumb_size']['width'];
        $this->thumbHeight = 32;//$config['modules']['post']['thumb_size']['height'];
        $this->imgDir = '/uploads/posts/';//$config['modules']['post']['image_dir'];
        $this->post = $post;
    }

    /**
     * @return mixed
     */
    public function all($is_published = false)
    {
        $query = $this->post->with('tags')->orderBy('created_at', 'DESC');
        if ($is_published) {
            $query->where('is_published', 1);
        }
        return $query->get();
    }

    /**
     * [getPosts description]
     * @param  boolean $is_published [description]
     * @param  integer $offset       [description]
     * @param  integer $limit        [description]
     * @param  string  $order        [description]
     * @param  string  $by           [description]
     * 
     * @return [type]                [description]
     */
    public function getPosts($onFacebook=true, $isPublished=false, $offset=0, $limit=10, $order='id', $by='DESC') {
        $query = $this->post->orderBy($order, $by);
        if ($onFacebook) {
            $query->whereNotNull('fb_post_id');
        }
        if ($isPublished) {
            $query->where('is_published', 1);
        }
        $count = $query->count();
        if ($offset < 0 || $limit < 0) {
            // log
            error_log('ERROR::invalid param|offset:'.$offset.'|limit:'.$limit);
            return false;
        } elseif ($offset > $count) {
            // log
            error_log('ERROR::overdata|offset:'.$offset.'|count:'.$count);
            return false;
        }
        $query = $query->take($limit)->offset($offset);
        return $query->get();
    }

    /**
     * @param $limit
     *
     * @return mixed
     */
    public function getLastPost($limit)
    {
        return $this->post->orderBy('created_at', 'desc')->where('is_published', 1)->take($limit)->offset(0)->get();
    }

    /**
     * @param $limit
     *
     * @return mixed
     */
    public function getHotPosts($limit)
    {
        return $this->post->orderBy('created_at', 'desc')->where('is_published', 1)->where('is_hot', 1)->take($limit)->offset(0)->get();
    }

    /**
     * @return mixed
     */
    public function lists()
    {
        return $this->post->get()->lists('title', 'id');
    }

    /**
     * Get paginated posts.
     *
     * @param int  $page  Number of posts per page
     * @param int  $limit Results per page
     * @param bool $all   Show published or all
     *
     * @return StdClass Object with $items and $totalItems for pagination
     */
    public function paginate($page = 1, $limit = 10, $all = false)
    {
        $result = new \StdClass();
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->post->with('tags')->orderBy('created_at', 'DESC');

        if (!$all) {
            $query->where('is_published', 1);
        }

        $posts = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalPosts($all);
        $result->items = $posts->all();

        return $result;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function find($id)
    {
        return $this->post->with(['tags', 'category'])->findOrFail($id);
    }

    /**
     * @param $slug
     *
     * @return mixed
     */
    public function getBySlug($slug)
    {
        return $this->post->with(['tags', 'category'])->where('slug', $slug)->first();
    }
    /**
     * @param $slug
     *
     * @return mixed
     */
    public function getPostBy($key, $value)
    {
        return $this->post->with(['tags', 'category'])->where($key, $value)->first();
    }

    /**
     * @param $attributes
     *
     * @return bool|mixed
     *
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes)
    {
        $attributes['is_published'] = isset($attributes['is_published']) ? true : false;

        if ($this->isValid($attributes)) {

            $userId = Sentinel::getUser()->id;
            $user = User::find($userId);
            $this->post->user_id = $userId;
            $this->post->category_id = $attributes['category_id'];
            $this->post->type = $attributes['type'];
            $this->post->source = $attributes['source'];
            $this->post->avatar = $user['avatar'];
            $this->post->display_name = $user['name'];
            $this->post->created_at = date('Y-m-d h:i:s');
            if (!$this->post->fill($attributes)->save()) {
                return false;
            }

            try {
                $attachment = new Attachment();
                $attachment->type = $attributes['type'];
                $attachment->src = $attributes['src'];
                $this->post->attachments()->save($attachment);
            } catch (Exception $e) {
                return false;
            }

            $postTags = explode(',', $attributes['tag']);

            foreach ($postTags as $postTag) {
                if (!$postTag) {
                    continue;
                }
                $postTag = trim($postTag);

                $tag = Tag::where('name', '=', $postTag)->first();

                if (!$tag) {
                    $tag = new Tag();
                }

                // $tag->lang = $this->getLang();
                $tag->name = $postTag;
                $tag->slug = Str::slug($postTag);

                try {
                    $this->post->tags()->save($tag);                    
                } catch (Exception $e) {
                    return false;
                }
            }

            //Event::fire('post.created', $this->post);
            Event::fire('post.creating', $this->post);

            return true;
        }
        throw new ValidationException('Post validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @param $attributes
     *
     * @return bool|mixed
     *
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function update($id, $attributes)
    {
        $this->post = $this->find($id);
        // $attributes['is_published'] = isset($attributes['is_published']) ? true : false;
        if (array_key_exists('is_published', $attributes)) {
            $this->post->is_published = $attributes['is_published'];
        }
        if (array_key_exists('category_id', $attributes)) {
            $this->post->category_id = $attributes['category_id'];
        }

        if ($this->isValid($attributes)) {
            if (!$this->post->fill($attributes)->save()) {
                return false;
            }

            /*attachments*/
            if (array_key_exists('src', $attributes)) {
                $attachments = Attachment::where('post_id', $id)->first();
                $attachments->src = $attributes['src'];
                $this->post->attachments()->save($attachments);
            }

            /*Tags*/
            if (array_key_exists('tag', $attributes)) {
                $postTags = explode(',', $attributes['tag']);

                foreach ($postTags as $postTag) {
                    if (!$postTag) {
                        continue;
                    }

                    $tag = Tag::where('name', '=', $postTag)->first();

                    if (!$tag) {
                        $tag = new Tag();
                    }
                    $pTag = PostTag::where('tag_id', $tag->id)->where('post_id', $id)->first();

                    if (!$pTag) {
                        $tag->name = $postTag;
                        $tag->slug = Str::slug($postTag);
                        $this->post->tags()->save($tag);                    
                    }
                }
            }

            return true;
        }

        throw new ValidationException('Post validation failed', $this->getErrors());
    }

    /**
     * @param $id
     *
     * @return mixed|void
     */
    public function delete($id)
    {
        $post = $this->post->findOrFail($id);
        $post->tags()->detach();
        $post->delete();
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function togglePublish($id)
    {
        $post = $this->post->find($id);

        $post->is_published = ($post->is_published) ? false : true;
        $post->save();

        return Response::json(array('result' => 'success', 'changed' => ($post->is_published) ? 1 : 0));
    }

    /**
     * @param $id
     *
     * @return string
     */
    public function getUrl($id)
    {
        $post = $this->post->findOrFail($id);

        return url('post/'.$id.'/'.$post->slug, $parameters = array(), $secure = null);
    }

    /**
     * Get total post count.
     *
     * @param bool $all
     *
     * @return mixed
     */
    protected function totalPosts($all = false)
    {
        if (!$all) {
            return $this->post->where('is_published', 1)->count();
        }

        return $this->post->count();
    }
}
