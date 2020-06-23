<?php
/**
 * This file is part of the Boxunsoft package.
 *
 * (c) Jordy <arno.zheng@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Alf\Router;

use All\Instance\InstanceTrait;
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased as DispatcherGroupCountBased;
use FastRoute\RouteParser\Std;
use RuntimeException;

/**
 * 路由类
 */
class Router
{
    use InstanceTrait;

    const NOT_FOUND = Dispatcher::NOT_FOUND;
    const FOUND = Dispatcher::FOUND;
    const METHOD_NOT_ALLOWED = Dispatcher::METHOD_NOT_ALLOWED;

    /**
     * @var Route[]
     */
    protected $routes = [];
    protected $collector;
    protected $dispatcher;

    private $isInitialized = false;

    public function __construct()
    {
        $parser = new Std;
        $generator = new GroupCountBased;
        $this->collector = new RouteCollector($parser, $generator);
    }

    public function init($config)
    {
        if ($config) {
            foreach($config as $item) {
                $this->addRoute($item['method'], $item['route'], $item['handler']);
            }
        }

        $this->dispatcher = new DispatcherGroupCountBased($this->getData());
        $this->isInitialized = true;
    }

    public function dispatch($method, $requestUri)
    {
        if (!$this->isInitialized) {
            throw new RuntimeException('Router must run init first');
        }
        return $this->dispatcher->dispatch($method, $requestUri);
    }

    public function addGroup($prefix, callable $callback)
    {
        $this->collector->addGroup($prefix, $callback);
    }

    protected function addRoute($method, $route, $handler)
    {
        $this->collector->addRoute($method, $route, $handler);
    }

    protected function getData()
    {
        return $this->collector->getData();
    }

    public function get($route, $handler)
    {
        $this->addRoute('GET', $route, $handler);
    }

    public function post($route, $handler)
    {
        $this->addRoute('POST', $route, $handler);
    }

    public function put($route, $handler)
    {
        $this->addRoute('PUT', $route, $handler);
    }

    public function delete($route, $handler)
    {
        $this->addRoute('DELETE', $route, $handler);
    }

    public function head($route, $handler)
    {
        $this->addRoute('HEAD', $route, $handler);
    }

    public function patch($route, $handler)
    {
        $this->addRoute('PATCH', $route, $handler);
    }

    public function options($route, $handler)
    {
        $this->addRoute('OPTIONS', $route, $handler);
    }
}