<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/26
 * Time: 上午10:32
 */

namespace Alf;

use Ali\InstanceTrait;

/**
 * 视图
 *
 * Class View
 * @package Alf
 */
class View
{
    use InstanceTrait;

    private $data;
    protected $ext = '.php';

    public function escape(&$value, $default = '')
    {
        echo isset($value) && $value ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $default;
    }

    public function assign($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function render($tpl = null)
    {
        if (!$tpl) {
            $Request = Request::getInstance();
            $tpl = $Request->getUri();
        }
        $tplFile = $this->_getTplFile($tpl);
        $this->data && extract($this->data);
        $this->data = null;
        include $tplFile;
    }

    public function template($tpl)
    {
        return $this->_getTplFile($tpl);
    }

    protected function _getTplFile($tpl)
    {
        $app = Application::getInstance();
        $path = $app->getAppPath() . DIRECTORY_SEPARATOR . 'view';
        $tpl = trim($tpl, '/\\');
        return $path . DIRECTORY_SEPARATOR . $tpl . $this->ext;
    }
}