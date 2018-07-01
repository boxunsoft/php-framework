<?php

namespace Alf;

use Ali\InstanceTrait;

class Dispatcher
{
    use InstanceTrait;

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @throws \Exception
     */
    public function dispatch()
    {
        $app = App::getInstance();
        $routePath = $this->request->getRoutePath();

        if ($routePath) {
            $controllerPath = $this->parseRoutePath($routePath);
        }
        if (!$controllerPath) {
            $controllerPath = 'Index';
        }

        $fullClassName = '\\Bx\\App\\' . $app->getAppName() . '\\Controller\\' . strtr($controllerPath, '/', '\\');
        $controllerFile = $app->getRootPath() . '/App/' . $app->getAppName() . '/Controller/' . $controllerPath . '.php';
        if ( ! is_file($controllerFile) || ! class_exists($fullClassName)) {
            throw new \Exception('Controller not exists.');
        }

        $controller = new $fullClassName();
        if ( ! method_exists($controller, 'main') || ! is_callable([$controller, 'main'])) {
            throw new \Exception('Method main() not exists.');
        }

        if (method_exists($controller, 'before')) {
            $controller->before();
        }
        $controller->main();
        if (method_exists($controller, 'after')) {
            $controller->after();
        }
    }

    private function parseRoutePath($routePath)
    {
        $pathArr = explode('/', trim($routePath, '/'));
        $controllerArr = [];
        foreach ($pathArr as $name) {
            $segments = explode('_', $name);
            $controllerArr[] = implode('', array_map('ucfirst', $segments));
        }
        return implode('/', $controllerArr);
    }
}