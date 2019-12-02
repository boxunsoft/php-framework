<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/25
 * Time: 下午5:54
 */

namespace Alf;

use Alf\Exception\NotFoundException;
use Alf\Request\Config;
use Ali\InstanceTrait;

final class Application
{
    use InstanceTrait;

    const ENV_DEVELOP = 'develop';
    const ENV_TEST = 'test';
    const ENV_PRE = 'pre';
    const ENV_BETA = 'beta';
    const ENV_PRODUCT = 'product';

    private $rootPath;
    private $appName;
    private $baseAppNamespace = 'Ala';
    private $environment;
    private $config;
    private $env;
    private $envPath;

    private $suffixs = [];
    private $isInitialized = false;

    protected $shutdownHandler;
    protected $errorHandler;
    protected $exceptionHandler;

    /**
     * @param $rootPath
     * @param $appName
     * @throws NotFoundException
     * @throws \Exception
     */
    public function main($rootPath, $appName)
    {
        if ($this->isInitialized) {
            return;
        }

        $this->isInitialized = true;
        $this->initialize($rootPath, $appName);
        $this->bootstrap();
    }

    /**
     * initialize from $env/app.php
     *
     * @param $rootPath
     * @param $appName
     * @throws \Exception
     */
    private function initialize($rootPath, $appName)
    {
        $this->rootPath = $rootPath;
        $this->appName = $appName;

        $config = $this->env('app');
        // 设置错误显示
        error_reporting(isset($config['error_reporting']) ? $config['error_reporting'] : E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT);
        ini_set('display_errors', isset($config['display_errors']) ? boolval($config['display_errors']) : false);

        // 设置时区
        date_default_timezone_set(isset($config['timezone']) ? $config['timezone'] : 'Asia/Shanghai');
        header('Content-type:text/html;charset=utf-8');

        $this->validateSuffix();
    }

    /**
     * @throws \Exception
     * @throws NotFoundException
     */
    private function bootstrap()
    {
        $uri = Request::getInstance()->uri();
        // before route
        Router::getInstance()->route($uri);
        // after route

        $fullClassName = $this->getFullClassName();
        $controllerFile = $this->getFullFilePath();

        if (!is_file($controllerFile) || !class_exists($fullClassName)) {
            throw new NotFoundException('Not Found', HttpCode::NOT_FOUND);
        }

        $controller = new $fullClassName();
        if (!method_exists($controller, 'main') || !is_callable([$controller, 'main'])) {
            throw new \Exception('Method main() is not exists.', HttpCode::METHOD_NOT_ALLOWED);
        }

        if (method_exists($controller, 'before') && is_callable([$controller, 'before'])) {
            call_user_func([$controller, 'before']);
        }
        call_user_func([$controller, 'main']);
        if (method_exists($controller, 'after') && is_callable([$controller, 'after'])) {
            call_user_func([$controller, 'after']);
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getFullClassName()
    {
        return sprintf('%s\\Controller\\%s', $this->getAppNamespace(),
            strtr(Router::getInstance()->getPath(), '/', '\\'));
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getFullFilePath()
    {
        return sprintf('%s/Controller/%s.php', $this->getAppPath(), Router::getInstance()->getPath());
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

    /**
     * @return string
     * @throws \Exception
     */
    public function getControllerPath()
    {
        return Router::getInstance()->getPath();
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
            $this->config->setPath($this->getRootPath() . DIRECTORY_SEPARATOR . 'config');
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
        if ($envPathConfig && array_key_exists($environment, $envPathConfig)) {
            $this->envPath = $envPathConfig[$environment];
        } else {
            $this->envPath = $this->rootPath . DIRECTORY_SEPARATOR . 'env' . DIRECTORY_SEPARATOR . $environment;
        }
        return $this->envPath;
    }

    /**
     * @throws \Exception
     */
    protected function validateSuffix()
    {
        if ($this->suffixs) {
            $request = Request::getInstance();
            $suffix = $request->suffix();
            if (!($suffix && in_array($suffix, $this->suffixs))) {
                throw new \Exception('Unsupported extension', HttpCode::REQUESTED_RANGE_NOT_SATISFIABLE);
            }
        }
    }

    protected function handler()
    {
        // Capture errors after the program runs
        if ($this->shutdownHandler && is_callable($this->shutdownHandler)) {
            register_shutdown_function($this->shutdownHandler);
        } else {
            set_error_handler('\\Alf\\Error::errorHandler');
        }
        // Capture Exception thrown errors
        if ($this->exceptionHandler && is_callable($this->exceptionHandler)) {
            set_exception_handler($this->exceptionHandler);
        } else {
            set_exception_handler('\\Alf\\Error::exceptionHandler');
        }
        // Catching grammatical errors
        if ($this->errorHandler && is_callable($this->errorHandler)) {
            set_error_handler($this->errorHandler);
        } else {
            set_error_handler('\\Alf\\Error::errorHandler');
        }
    }

    public function setShutdownHandler(callable $handler)
    {
        $this->shutdownHandler = $handler;
    }

    public function setErrorHandler(callable $handler)
    {
        $this->errorHandler = $handler;
    }

    public function setExceptionHandler(callable $handler)
    {
        $this->exceptionHandler = $handler;
    }

    /**
     * 设置命名空间
     * @param $namespace
     */
    public function setBaseAppNamespace($namespace)
    {
        $this->baseAppNamespace = $namespace;
    }
}