<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/25
 * Time: 上午11:31
 */

namespace Alf;

/**
 * 响应
 *
 * Class Response
 * @package Alf
 */
class Response extends \All\Response
{
    private $controller;

    public function stop()
    {
        if ($this->controller && trim(get_class($this->controller),
                '\\') != trim(Application::getInstance()->getErrorClassName(), '\\')) {
            if (method_exists($this->controller, 'after')) {
                $this->controller->after();
            }
        }
        exit;
    }

    public function setController($controller)
    {
        $this->controller = $controller;
    }
}