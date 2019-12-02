<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/2
 * Time: 2:49 PM
 */

namespace Alf\Request;

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