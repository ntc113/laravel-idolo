<?php

namespace App\Repositories\Post;

use App\Repositories\RepositoryInterface;

/**
 * Interface PostInterface.
 *
 * @author Sefa Karagöz <karagozsefa@gmail.com>
 */
interface PostInterface extends RepositoryInterface
{
    /**
     * @param $slug
     *
     * @return mixed
     */
    public function getBySlug($slug);
}
