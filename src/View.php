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
    protected $rootPath;
    protected $extensionName = '.phtml';

    public function escape(&$value, $default = '')
    {
        echo isset($value) && $value ? htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : $default;
    }

    public function assign($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * @param null $tpl
     * @throws \Exception
     */
    public function render($tpl = null)
    {
        if (!$tpl) {
            $tpl = $this->_getDefaultTpl();
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

    public function setRootPath($path)
    {
        $this->rootPath = $path;
        return $this;
    }

    public function getRootPath()
    {
        if ($this->rootPath) {
            return $this->rootPath;
        }
        $App = Application::getInstance();
        $path = $App->getAppPath() . DIRECTORY_SEPARATOR . 'View';
        return $path;
    }

    public function setExtensionName($name)
    {
        $this->extensionName = $name;
    }

    protected function _getTplFile($tpl)
    {
        $path = $this->getRootPath();
        $tpl = trim($tpl, '/\\');
        return $path . DIRECTORY_SEPARATOR . $tpl . $this->extensionName;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function _getDefaultTpl()
    {
        return Application::getInstance()->getControllerPath();
    }
}