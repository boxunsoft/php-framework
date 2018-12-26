<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/25
 * Time: 下午5:54
 */

namespace Alf;

/**
 * 控制器
 *
 * Class Controller
 * @package Alf
 */
class Controller
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Response
     */
    private $response;
    /**
     * @var Application
     */
    private $app;

    public function __construct()
    {
        $this->app = Application::getInstance();
        $this->request = Request::getInstance();
        $this->response = Response::getInstance();
        $this->response->setController($this);
    }

    public function app()
    {
        return $this->app;
    }

    public function request()
    {
        return $this->request;
    }

    public function response()
    {
        return $this->response;
    }
}