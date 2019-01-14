<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/25
 * Time: 下午5:54
 */

namespace Alf;

use Alf\Exception\ApplicationException;
use Alf\Exception\ErrorException;
use Alf\Exception\ExitException;
use Alf\Exception\NotFoundException;
use Alf\Exception\ShutdownException;
use Alf\Exception\WarningException;
use Ali\InstanceTrait;
use All\Config;

final class Application
{
    use InstanceTrait;

    const ENV_DEVELOP = 'develop';
    const ENV_TEST = 'test';
    const ENV_PRODUCT = 'product';

    private $rootPath;
    private $appName;
    private $baseAppNamespace = 'Ala';
    private $environment;
    private $config;
    private $env;
    private $envPath;

    private $suffixs = [];

    private $errorCodes = [
        E_ERROR,
        E_CORE_ERROR,
        E_COMPILE_ERROR,
        E_USER_ERROR,
        E_PARSE,
        E_RECOVERABLE_ERROR
    ];

    private $isInitialized = false;
    private $controllerPath;

    /**
     * @param $rootPath
     * @param $appName
     * @throws ApplicationException
     * @throws Exception\RouterException
     * @throws NotFoundException
     */
    public function startup($rootPath, $appName)
    {
        if ($this->isInitialized) {
            return;
        }

        $this->isInitialized = true;
        $this->_init($rootPath, $appName);
        $this->dispatch();
    }

    /**
     * @param $rootPath
     * @param $appName
     * @throws ApplicationException
     */
    private function _init($rootPath, $appName)
    {
        // 设置错误显示
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT);
        ini_set('display_errors', false);
        //设置时区
        date_default_timezone_set('Asia/Shanghai');
        header('Content-type:text/html;charset=utf-8');

        register_shutdown_function([$this, '_shutdownHandler']);
        set_exception_handler([$this, '_exceptionHandler']);
        set_error_handler([$this, '_errorHandler']);

        $this->rootPath = $rootPath;
        $this->appName = $appName;

        if (!$this->_allowSuffix()) {
            throw new ApplicationException('Unpermitted extensions', ApplicationException::CODE_NOT_ALLOW_EXTENSION);
        }
    }

    /**
     * @throws ApplicationException
     * @throws Exception\RouterException
     * @throws NotFoundException
     */
    private function dispatch()
    {
        $uri = Request::getInstance()->uri();
        $this->controllerPath = Router::getInstance()->route($uri);

        $fullClassName = sprintf('%s\\Controller\\%s',
            $this->getAppNamespace(),
            strtr($this->controllerPath, '/', '\\'));
        $controllerFile = sprintf('%s/Controller/%s.php',
            $this->getAppPath(),
            $this->controllerPath);

        if (!is_file($controllerFile) || !class_exists($fullClassName)) {
            throw new NotFoundException();
        }

        $controller = new $fullClassName();
        if (!method_exists($controller, 'main') || !is_callable([$controller, 'main'])) {
            throw new ApplicationException('Method main() is not exists.',
                ApplicationException::CODE_METHOD_NOT_EXISTS);
        }

        if (method_exists($controller, 'before')) {
            $controller->before();
        }
        $controller->main();
        if (method_exists($controller, 'after')) {
            $controller->after();
        }
    }

    public function setSuffixs(array $suffixs)
    {
        $this->suffixs = $suffixs;
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

    public function getAppNamespace()
    {
        return '\\' . $this->baseAppNamespace . '\\Application\\' . $this->appName;
    }

    public function getAppPath()
    {
        return $this->rootPath . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . $this->appName;
    }

    public function getEnvironment()
    {
        if ($this->environment) {
            return $this->environment;
        }
        return $this->environment = getenv('ALF_ENV') ? getenv('ALF_ENV') : self::ENV_PRODUCT;
    }

    public function getControllerPath()
    {
        return $this->controllerPath;
    }

    /**
     * @param $key
     * @return array|mixed|null
     * @throws \Exception
     */
    public function config($key)
    {
        if (!$this->config) {
            $this->config = new Config();
            $this->config->setPath($this->rootPath . DIRECTORY_SEPARATOR . 'config');
        }
        return $this->config->get($key);
    }

    /**
     * @param $key
     * @return mixed
     * @throws \Exception
     */
    public function env($key)
    {
        if (!$this->env) {
            $this->env = new Config();
            $this->env->setPath($this->getEnvPath());
        }
        return $this->env->get($key);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private function getEnvPath()
    {
        if ($this->envPath) {
            return $this->envPath;
        }

        $environment = $this->getEnvironment();
        $envPathConfig = $this->config('app.env_path');
        if (array_key_exists($environment, $envPathConfig)) {
            $this->envPath = $envPathConfig[$environment];
        } else {
            $this->envPath = $this->rootPath . DIRECTORY_SEPARATOR . 'env' . DIRECTORY_SEPARATOR . $environment;
        }
        return $this->rootPath;
    }

    /**
     * 是否被允许的URL扩展名后缀
     *
     * @return bool
     */
    protected function _allowSuffix()
    {
        if (!$this->suffixs) {
            return true;
        }
        $request = Request::getInstance();
        $suffix = $request->suffix();
        return in_array($suffix, $this->suffixs);
    }

    public function getErrorClassName()
    {
        return $this->getAppNamespace() . '\\Error';
    }

    /**
     * @param Exception $e
     */
    public function _exceptionHandler($e)
    {
        if ($e instanceof ExitException) {
            return;
        }

        $message = sprintf('message: %s ( %d ), file: %s ( %d )', $e->getMessage(), $e->getCode(), $e->getFile(),
            $e->getLine());
        $fullErrorClassName = $this->getErrorClassName();
        if (class_exists($fullErrorClassName)) {
            $obj = new $fullErrorClassName();
            if (method_exists($obj, 'main')) {
                $obj->main($e);
            } else {
                echo $message;
            }
        } else {
            echo $message;
        }
    }

    public function _errorHandler($errorCode, $errorMessage, $errorFile, $errorLine)
    {
        if (!(error_reporting() & $errorCode)) {
            return;
        }

        if (in_array($errorCode, $this->errorCodes)) {
            $e = new ErrorException($errorMessage, $errorCode);
        } else {
            $e = new WarningException($errorMessage, $errorCode);
        }
        $e->setFile($errorFile);
        $e->setLine($errorLine);
        $this->_exceptionHandler($e);
    }

    public function _shutdownHandler()
    {
        $error = error_get_last();
        if ($error) {
            $e = new ShutdownException($error['message'], $error['type']);
            $e->setFile($error['file']);
            $e->setLine($error['line']);
            $this->_exceptionHandler($e);
        }
    }
}