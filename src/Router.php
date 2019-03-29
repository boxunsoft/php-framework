<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/25
 * Time: 下午5:54
 */

namespace Alf;

use Ali\InstanceTrait;

/**
 * URI路由
 *
 * Class Router
 * @package Alf
 */
final class Router
{
    use InstanceTrait;

    private $path;

    /**
     * 路由
     *
     * @param string $uri
     * @return string
     * @throws \Exception
     */
    public function route($uri)
    {
        if (preg_match('/[^a-z\/_]+/i', $uri)) {
            throw new \Exception('Bad Request', HttpCode::BAD_REQUEST);
        }

        $uri = trim($uri, '/');
        //默认路由
        if (!$uri) {
            return $this->_setDefault();
        }
        $uriArr = array_filter(explode('/', $uri));
        $pathArr = [];
        foreach ($uriArr as $word) {
            $wordArr = array_filter(explode('_', $word));
            $pathArr[] = implode('', array_map('ucfirst', $wordArr));
        }
        $this->path = implode('/', $pathArr);
        return $this->path;
    }

    /**
     * 获取控制器路径
     *
     * @return string
     * @throws \Exception
     */
    public function getPath()
    {
        if (!$this->path) {
            throw new \Exception('Uri must be routed first, please run Router::route($uri)', HttpCode::INTERNAL_SERVER_ERROR);
        }
        return $this->path;
    }

    /**
     * 设置控制器路径
     *
     * @param $name
     * @return string
     */
    protected function _setPath($name)
    {
        $this->path = ucfirst($name);
        return $this->path;
    }

    /**
     * 默认
     *
     * @return string
     */
    protected function _setDefault()
    {
        $this->path = 'Index';
        return $this->path;
    }
}