<?php

namespace App\Interfaces;

/**
 * Class ModelInterface.
 *
 * @author 
 */
interface ModelInterface
{
    /**
     * @param $value
     *
     * @return mixed
     */
    public function setUrlAttribute($value);

    /**
     * @return mixed
     */
    public function getUrlAttribute();
}
