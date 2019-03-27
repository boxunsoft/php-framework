<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/28
 * Time: 下午6:24
 */

namespace Alf\Request;

class File
{
    public function get($key, $default = null)
    {
        $files = $this->getAll();
        return isset($files[$key]) ? $files[$key] : $default;
    }

    public function getAll()
    {
        return $_FILES ? $_FILES : [];
    }
}