<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/25
 * Time: 下午5:54
 */

namespace Alf;

use Alf\Exception\ErrorException;
use Alf\Exception\ExitException;
use Alf\Exception\NotFoundException;
use Alf\Exception\ShutdownException;
use Alf\Exception\WarningException;
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

    private $errorCodes = [
        E_ERROR,
        E_CORE_ERROR,
        E_COMPILE_ERROR,
        E_USER_ERROR,
        E_PARSE,
        E_RECOVERABLE_ERROR
    ];

    private $isInitialized = false;

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

        // Capture errors after the program runs
        register_shutdown_function([$this, '_shutdownHandler']);
        // Capture Exception thrown errors
        set_exception_handler([$this, '_exceptionHandler']);
        // Catching grammatical errors
        set_error_handler([$this, '_errorHandler']);

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
                call_user_func([$obj, 'main'], $e);
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