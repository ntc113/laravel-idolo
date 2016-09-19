<?php

namespace App\Repositories\Category;

use App\Models\Category;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface;
use App\Exceptions\Validation\ValidationException;

/**
 * Class CategoryRepository.
 *
 * @author 
 */
class CategoryRepository extends RepositoryAbstract implements CategoryInterface, CrudableInterface
{
    /**
     * @var
     */
    protected $perPage;
    /**
     * @var \Category
     */
    protected $category;
    /**
     * Rules.
     *
     * @var array
     */
    protected static $rules = [
        'name' => 'required|min:3|unique:categories',
    ];

    /**
     * @param Category $category
     */
    public function __construct(Category $category)
    {
        $this->category = $category;
        $this->perPage = 10;
    }

    /**
     * @return mixed
     */
    public function all()
    {
        return $this->category->get();
    }

    /**
     * @param int  $page
     * @param int  $limit
     * @param bool $all
     *
     * @return mixed|\StdClass
     */
    public function paginate($page = 1, $limit = 10, $all = false)
    {
        $result = new \StdClass();
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->category->orderBy('name');

        $categories = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalCategories();
        $result->items = $categories->all();

        return $result;
    }

    /**
     * @return mixed
     */
    public function lists()
    {
        return $this->category->lists('name', 'id');
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function find($id)
    {
        return $this->category->findOrFail($id);
    }

    /**
     * @param $slug
     *
     * @return mixed
     */
    public function getArticlesBySlug($slug)
    {
        return $this->category->where('slug', $slug)->first()->posts()->orderBy('created_at', 'desc')->paginate($this->perPage);
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
        if ($this->isValid($attributes)) {
            $this->category->fill($attributes)->save();

            return true;
        }
        throw new ValidationException('Category validation failed', $this->getErrors());
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
        $this->category = $this->find($id);

        if ($this->isValid($attributes)) {
            $this->category->slug = null;
            $this->category->fill($attributes)->save();

            return true;
        }

        throw new ValidationException('Category validation failed', $this->getErrors());
    }

    /**
     * @param $id
     *
     * @return mixed|void
     */
    public function delete($id)
    {
        $this->category = $this->category->find($id);
        $this->category->posts()->delete($id);
        $this->category->delete();
    }

    /**
     * Get total category count.
     *
     * @return mixed
     */
    protected function totalCategories()
    {
        return $this->category->count();
    }
}
