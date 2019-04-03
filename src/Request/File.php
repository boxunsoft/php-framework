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

class FileAttribute
{
    private $name;
    private $type;
    private $tmpName;
    private $error;
    private $size;

    public function __construct($fileData)
    {
        $this->name = $fileData['name'];
        $this->type = $fileData['type'];
        $this->tmpName = $fileData['tmp_name'];
        $this->error = $fileData['error'];
        $this->size = $fileData['size'];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getTmpName()
    {
        return $this->tmpName;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getSize()
    {
        return $this->size;
    }
}