<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/17
 * Time: 3:35 PM
 */

namespace Alf;

use Ali\InstanceTrait;
use All\Config\Config;
use All\Exception\ErrorException;
use All\Exception\Exception;
use All\Exception\FatalException;
use All\Exception\HttpException;
use All\Exception\MemcacheException;
use All\Exception\MysqlException;
use All\Exception\RedisException;
use All\Exception\ServerErrorException;
use All\Logger\Logger;
use All\Request\Request;
use All\Response\Response;
use All\Router\Router;
use All\Utils\HttpCode;

final class Kernel
{
    use InstanceTrait;

    const ENV_DEVELOP = 'develop';
    const ENV_TEST = 'test';
    const ENV_PRE = 'pre';
    const ENV_BETA = 'beta';
    const ENV_RELEASE = 'release';

    /**
     * 站点根目录
     * @var string
     */
    private $rootPath;
    /**
     * 应用名称(英文,开头字母大写)
     * @var string
     */
    private $appName;
    /**
     * 应用命名窨
     * @var string
     */
    private $appNamespace = 'Bx';
    /**
     * 环境变量
     * @var string
     */
    private $environment;
    /**
     * 环境配置目录
     * @var string
     */
    private $envPath;

    /**
     * 允许的URL后缀配置
     * @var array
     */
    private $suffixs = [];
    /**
     * 是否已经初始化过了,不能重复初始化
     * @var bool
     */
    private $isInitialized = false;

    /**
     * PHP 执行结束回调函数
     * @var callable
     */
    protected $shutdownHandler;
    /**
     * PHP错误回调函数
     * @var callable
     */
    protected $errorHandler;
    /**
     * PHP Exception错误回调函数
     * @var callable
     */
    protected $exceptionHandler;

    /**
     * @param string $rootPath
     * @param string $appName
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

        $config = $this->env()->get('app');
        // 设置错误显示
        error_reporting(isset($config['error_reporting']) ? $config['error_reporting'] : E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT);
        ini_set('display_errors', isset($config['display_errors']) ? boolval($config['display_errors']) : false);

        // 设置时区
        date_default_timezone_set(isset($config['timezone']) ? $config['timezone'] : 'Asia/Shanghai');

        // 响应头
        $this->setHeader();

        // 日志
        if (!empty($config['log_level'])) {
            Logger::setLevel($config['log_level']);
        }
        if (!empty($config['log_save_path'])) {
            Logger::setSavePath($config['log_save_path']);
        }
        if (!empty($config['log_save_handler'])) {
            Logger::setSaveHandler($config['log_save_handler']);
        }

        $this->handler();

        $this->validateSuffix();
    }

    /**
     * @throws \Exception
     */
    private function bootstrap()
    {
        $request = Request::getInstance();
        $uri = $request->uri();

        // before route
        $router = Router::getInstance();
        $routerConfig = $this->config()->get('router/' . strtolower($this->getAppName()));
        if ($routerConfig) {
            $router->init($routerConfig);
        }
        $router->route($uri);
        // after route

        $fullClassName = $this->getFullClassName();
        $controllerFile = $this->getFullFilePath();

        if (!is_file($controllerFile) || !class_exists($fullClassName)) {
            throw new HttpException(HttpCode::NOT_FOUND);
        }

        $controller = new $fullClassName();
        if (!method_exists($controller, 'main') || !is_callable([$controller, 'main'])) {
            throw new HttpException(HttpCode::METHOD_NOT_ALLOWED);
        }

        if (method_exists($controller, 'before') && is_callable([$controller, 'before'])) {
            call_user_func([$controller, 'before']);
        }

        call_user_func([$controller, 'main']);

        if (method_exists($controller, 'after') && is_callable([$controller, 'after'])) {
            call_user_func([$controller, 'after']);
        }
    }

    private function getFullClassName()
    {
        return sprintf('%s\\Controller\\%s', $this->getAppNamespace(), Router::getInstance()->getController());
    }

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
        return '\\' . $this->appNamespace . '\\App\\' . $this->appName;
    }

    public function getAppPath()
    {
        return $this->getRootPath() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . $this->getAppName();
    }

    public function getEnvironment()
    {
        if ($this->environment) {
            return $this->environment;
        }
        return $this->environment = getenv('ALF_ENV') ? getenv('ALF_ENV') : self::ENV_RELEASE;
    }

    /**
     * @return Config
     */
    public function config()
    {
        static $config;
        if (is_null($config)) {
            $config = new Config();
            $config->setPath($this->getRootPath() . DIRECTORY_SEPARATOR . 'config');
        }
        return $config;
    }

    /**
     * @return Config
     * @throws \Exception
     */
    public function env()
    {
        static $env;
        if (is_null($env)) {
            $env = new Config();
            $env->setPath($this->getEnvPath());
        }
        return $env;
    }

    /**
     * @return Logger
     */
    public function logger()
    {
        static $logger;
        if (is_null($logger)) {
            $logger = Logger::getInstance();
        }
        return $logger;
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
        $envPathConfig = $this->config()->get('app.env_path');
        if ($envPathConfig && array_key_exists($environment, $envPathConfig)) {
            $this->envPath = $envPathConfig[$environment];
        } else {
            $this->envPath = $this->getRootPath() . DIRECTORY_SEPARATOR . 'env' . DIRECTORY_SEPARATOR . $environment;
        }
        return $this->envPath;
    }

    /**
     * @throws HttpException
     */
    protected function validateSuffix()
    {
        if ($this->suffixs) {
            $request = Request::getInstance();
            $suffix = $request->suffix();
            if (!($suffix && in_array($suffix, $this->suffixs))) {
                throw new HttpException(HttpCode::REQUESTED_RANGE_NOT_SATISFIABLE);
            }
        }
    }

    protected function setHeader()
    {
        // 只允许同域名iframe嵌套
        header('X-Frame-Options: SAMEORIGIN');
        // 禁止浏览器用MIME-sniffing解析资源类型
        header('X-Content-Type-Options: nosniff');
        // 启用XSS保护
        header('X-XSS-Protection: 1; mode=block');
        header('Content-type:text/html;charset=utf-8');
    }

    protected function handler()
    {
        // Capture errors after the program runs
        if ($this->shutdownHandler && is_callable($this->shutdownHandler)) {
            register_shutdown_function($this->shutdownHandler);
        } else {
            register_shutdown_function([$this, 'shutdownHandler']);
        }
        // Capture Exception thrown errors
        if ($this->exceptionHandler && is_callable($this->exceptionHandler)) {
            set_exception_handler($this->exceptionHandler);
        } else {
            set_exception_handler([$this, 'exceptionHandler']);
        }
        // Catching grammatical errors
        if ($this->errorHandler && is_callable($this->errorHandler)) {
            set_error_handler($this->errorHandler);
        } else {
            set_error_handler([$this, 'errorHandler']);
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
    public function setAppNamespace($namespace)
    {
        $this->appNamespace = $namespace;
    }

    /**
     * 代码抛出错误拦截
     * @param \Exception $e
     */
    public function exceptionHandler($e)
    {
        $code = $e->getCode() ? $e->getCode() : HttpCode::INTERNAL_SERVER_ERROR;
        $message = sprintf('message: %s ( %d ), file: %s ( %d )', $e->getMessage(), $e->getCode(), $e->getFile(),
            $e->getLine());

        // 日志
        $logData = [
            'code' => $code,
            'message' => $message,
        ];

        // 资源参数
        if ($e instanceof MysqlException) {
            $logData['sql'] = $e->getPrepareSql();
            $logData['params'] = $e->getParams();
            $logData['host'] = $e->getHost();
            $logData['port'] = $e->getPort();
        } elseif ($e instanceof RedisException) {
            $logData['method'] = $e->getMethod();
            $logData['params'] = $e->getParams();
            $logData['host'] = $e->getHost();
            $logData['port'] = $e->getPort();
        } elseif ($e instanceof MemcacheException) {
            $logData['method'] = $e->getMethod();
            $logData['params'] = $e->getParams();
            $logData['config'] = $e->getConfig();
        }

        // 记录日志
        if (in_array($code, [
                E_ERROR,
                E_CORE_ERROR,
                E_COMPILE_ERROR,
                E_USER_ERROR,
                E_PARSE,
                E_RECOVERABLE_ERROR
            ])
            || $e instanceof ErrorException
            || $e instanceof ServerErrorException) {
            $this->logger()->error($logData);
        } elseif (in_array($code, [E_NOTICE, E_USER_NOTICE])) {
            $this->logger()->info($logData);
        } elseif ($e instanceof FatalException) {
            $this->logger()->fatal($logData);
        } else {
            $this->logger()->warn($logData);
        }

        $request = Request::getInstance();
        $response = Response::getInstance();
        if ($request->isXmlHttpRequest()) {
            $response->error($code, $e->getMessage());
        } else {
            echo $e->getMessage();
            $response->stop();
        }
    }

    /**
     * 语法错误信息拦截
     *
     * @param $errorCode
     * @param $errorMessage
     * @param $errorFile
     * @param $errorLine
     */
    public function errorHandler($errorCode, $errorMessage, $errorFile, $errorLine)
    {
        if (!(error_reporting() & $errorCode)) {
            return;
        }

        $e = new Exception($errorMessage, $errorCode);
        $e->setFile($errorFile);
        $e->setLine($errorLine);
        $this->exceptionHandler($e);
    }

    /**
     * 程序执行结束处理
     */
    public function shutdownHandler()
    {
        $error = error_get_last();
        if ($error) {
            $e = new Exception($error['message'], $error['type']);
            $e->setFile($error['file']);
            $e->setLine($error['line']);
            $this->exceptionHandler($e);
        }
    }
}