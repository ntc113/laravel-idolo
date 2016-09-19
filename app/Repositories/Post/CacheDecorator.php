<?php

namespace App\Repositories\Post;

use App\Services\Cache\CacheInterface;

/**
 * Class CacheDecorator.
 *
 * @author Sefa Karagöz <karagozsefa@gmail.com>
 */
class CacheDecorator extends AbstractPostDecorator
{
    /**
     * @var \App\Services\Cache\CacheInterface
     */
    protected $cache;

    /**
     * Cache key.
     *
     * @var string
     */
    protected $cacheKey = 'post';

    /**
     * @param PostInterface $post
     * @param CacheInterface   $cache
     */
    public function __construct(PostInterface $post, CacheInterface $cache)
    {
        parent::__construct($post);
        $this->cache = $cache;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function find($id)
    {
        $key = md5($this->cacheKey.'.id.'.$id);

        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $post = $this->post->find($id);

        $this->cache->put($key, $post);

        return $post;
    }

    /**
     * @return mixed
     */
    public function all()
    {
        $key = md5($this->cacheKey.'.all.posts');

        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $posts = $this->post->all();

        $this->cache->put($key, $posts);

        return $posts;
    }

    /**
     * @param null $page
     * @param bool $all
     *
     * @return mixed
     */
    public function paginate($page = 1, $limit = 10, $all = false)
    {
        $allkey = ($all) ? '.all' : '';
        $key = md5($this->cacheKey.'.page.'.$page.'.'.$limit.$allkey);

        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $paginated = $this->post->paginate($page, $limit, $all);

        $this->cache->put($key, $paginated);

        return $paginated;
    }

    /**
     * @param $slug
     *
     * @return mixed
     */
    public function getBySlug($slug)
    {
        $key = md5($this->cacheKey.'.slug.'.$slug);

        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $post = $this->post->getBySlug($slug);

        $this->cache->put($key, $post);

        return $post;
    }

    /**
     * @param $limit
     *
     * @return mixed
     */
    public function getLastPost($limit)
    {
        $key = md5($limit.$this->cacheKey.'.last');

        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $posts = $this->post->getLastPost($limit);

        $this->cache->put($key, $posts);

        return $posts;
    }

    /**
     * @param $limit
     *
     * @return mixed
     */
    public function getHotPosts($limit)
    {
        $key = md5($limit.$this->cacheKey.'.hot');

        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $posts = $this->post->getHotPosts($limit);

        $this->cache->put($key, $posts);

        return $posts;
    }
}
