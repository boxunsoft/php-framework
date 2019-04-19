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
    private $config;
    private $params = [];

    /**
     * 路由
     *
     * @param string $uri
     * @return string
     * @throws \Exception
     */
    public function route($uri)
    {
        if (preg_match('/[^a-z0-9\/_]+/i', $uri)) {
            throw new \Exception('Bad Request', HttpCode::BAD_REQUEST);
        }

        $uri = trim($uri, '/');
        //默认路由
        if (!$uri) {
            return $this->_setDefault();
        }
        list($uri, $this->params) = $this->_parseUri($uri);
        $uri = trim($uri, '/');
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
            throw new \Exception('Uri must be routed first, please run Router::route($uri)',
                HttpCode::INTERNAL_SERVER_ERROR);
        }
        return $this->path;
    }

    public function getParams()
    {
        return $this->params;
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

    protected function _loadConfig()
    {
        if (is_null($this->config)) {
            $config = Application::getInstance()->config('router');
            $this->setConfig($config);
        }
    }

    public function setConfig($config)
    {
        $this->config = $config ?? [];
        return $this;
    }

    protected function _parseUri($uri)
    {
        $this->_loadConfig();
        if ($this->config) {
            return $this->_parseParam($uri);
        } else {
            return [$uri, []];
        }
    }

    protected function _parseParam($uri)
    {
        $params = [];
        $routedUri = $uri;
        foreach ($this->config as $router) {
            if (!preg_match_all('/:[a-z0-9_]+/i', $router, $matches)) {
                continue;
            }
            $paramNames = [];
            $regexUri = $router;
            foreach ($matches[0] as $m) {
                $regexUri = str_replace($m, '([a-zA-Z0-9_]+)', $regexUri);
                $paramNames[] = substr($m, 1);
            }
            if (!preg_match('#^' . trim($regexUri, '/') . '$#i', $uri, $matches)) {
                unset($paramNames);
                continue;
            }
            foreach ($paramNames as $key => $field) {
                $params[$field] = $matches[$key + 1] ?? '';
            }
            $routedUri = preg_replace('/\/:[a-z0-9_]+/i', '', $router);
            break;
        }
        return [$routedUri, $params];
    }
}