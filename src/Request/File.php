<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/28
 * Time: 下午6:24
 */

namespace Alf\Request;

/**
 * Form 上传文件
 * Class File
 * @package Alf\Request
 */
class File
{
    /**
     * @param string $key
     * @param null $default
     * @return FileAttribute|null
     */
    public function get($key, $default = null)
    {
        static $files = null;
        if (is_null($files)) {
            $files = $this->getAll();
        }
        return isset($files[$key]) ? $files[$key] : $default;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $files = $_FILES ? $_FILES : [];
        $data = [];
        foreach ($files as $file) {
            $data[] = new FileAttribute($file);
        }
        return $data;
    }
}