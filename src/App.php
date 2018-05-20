<?php

namespace Alf;

use Ali\InstanceTrait;

class App
{
    use InstanceTrait;

    private $rootPath;
    private $appName;

    public function run()
    {
        try {
            $request = Request::getInstance();
            $router = Router::getInstance($request);
            $router->route();
            $dispatcher = Dispatcher::getInstance($request);
            $dispatcher->dispatch();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;
        return $this;
    }

    public function setAppName($appName)
    {
        $this->appName = $appName;
        return $this;
    }

    public function getRootPath()
    {
        return $this->rootPath;
    }

    public function getAppName()
    {
        return $this->appName;
    }
}