<?php

namespace App\Repositories\Post;

/**
 * Class AbstractPostDecorator.
 *
 * @author Sefa Karagöz <karagozsefa@gmail.com>
 */
abstract class AbstractPostDecorator implements PostInterface
{
    /**
     * @var PostInterface
     */
    protected $post;

    /**
     * @param PostInterface $post
     */
    public function __construct(PostInterface $post)
    {
        $this->post = $post;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function find($id)
    {
        return $this->post->find($id);
    }

    /**
     * @param $slug
     *
     * @return mixed
     */
    public function getBySlug($slug)
    {
        return $this->post->getBySlug($slug);
    }

    /**
     * @return mixed
     */
    public function all()
    {
        return $this->post->all();
    }

    /**
     * @param null $perPage
     * @param bool $all
     *
     * @return mixed
     */
    public function paginate($page = 1, $limit = 10, $all = false)
    {
        return $this->post->paginate($page, $limit, $all);
    }
}
