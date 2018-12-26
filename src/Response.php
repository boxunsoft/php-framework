<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/25
 * Time: 上午11:31
 */

namespace Alf;

use Ali\InstanceTrait;

/**
 * 响应
 *
 * Class Response
 * @package Alf
 */
class Response
{
    use InstanceTrait;

    private $controller;

    public function redirect($url)
    {
        header('Location:' . $url);
    }

    public function error($code, $message, array $params = [])
    {
        if ($params) {
            $message = vsprintf($message, $params);
        }
        $this->output($code, $message);
    }

    public function success(array $response)
    {
        $this->output(0, '成功', $response);
    }

    public function output($code, $message, array $response = [])
    {
        $result = [
            'flag' => $code ? 'failure' : 'success',
            'code' => intval($code),
            'message' => trim($message),
            'response' => $response ? $response : new \stdClass()
        ];
        $this->json($result);
    }

    public function json($data)
    {
        ob_clean();
        header('Content-type:application/json;charset=utf-8');
        //指定JSON_PARTIAL_OUTPUT_ON_ERROR,避免$data中有非utf-8字符导致json编码返回false
        echo json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR);
        $this->stop();
    }

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