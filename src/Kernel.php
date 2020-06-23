<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/17
 * Time: 3:35 PM
 */

namespace Alf;

use Alf\Console\Console;
use Alf\Router\Router;
use Alf\Traits\RequestTrait;
use Alf\Traits\ResponseTrait;
use All\Config\Config;
use All\Exception\ErrorException;
use All\Exception\Exception;
use All\Exception\FatalException;
use All\Exception\MemcacheException;
use All\Exception\MysqlException;
use All\Exception\RedisException;
use All\Exception\ServerErrorException;
use All\Instance\InstanceTrait;
use All\Logger\Handler\FileHandler;
use All\Logger\Handler\StreamHandler;
use All\Logger\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use RuntimeException;
use Throwable;

final class Kernel
{
    use InstanceTrait;
    use LoggerAwareTrait;
    use RequestTrait;
    use ResponseTrait;

    const ENV_DEVELOP = 'develop';      // 开发环境
    const ENV_TEST = 'test';            // 测试环境
    const ENV_BETA = 'beta';            // 线上测试环境
    const ENV_RELEASE = 'release';      // 线上生产环境

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
     * WEB模式
     * 
     * @param string $rootPath
     * @param string $appName
     * @throws \Exception
     */
    public function main($rootPath, $appName)
    {
        if ($this->isInitialized) {
            return;
        }

        $this->rootPath = $rootPath;
        $this->appName = $appName;

        $this->initialize();
        $this->bootstrap();
    }

    /**
     * 命令行模式
     * 使用 Symfony/Console
     * 
     * @return Console
     */
    public function console(callable $callback, $name = 'Console', $version = '1.0.0')
    {
        if ($this->isInitialized) {
            return;
        }

        $this->initialize();

        $app = new Console($name, $version);
        if ($callback) {
            $callback($app);
        }
        return $app;
    }

    /**
     * initialize from $env/app.php
     *
     * @throws \Exception
     */
    private function initialize()
    {
        $this->isInitialized = true;

        $config = $this->env()->get('app');
        // 设置错误显示
        error_reporting(isset($config['error_reporting']) ? $config['error_reporting'] : E_ALL);
        ini_set('display_errors', isset($config['display_errors']) ? boolval($config['display_errors']) : false);

        // 设置时区
        date_default_timezone_set(isset($config['timezone']) ? $config['timezone'] : 'Asia/Shanghai');

        // 设置捕捉句柄
        set_error_handler([$this, 'errorHandler'], error_reporting());
        set_exception_handler([$this, 'exceptionHandler']);
        register_shutdown_function([$this, 'shutdownHandler']);
    }

    /**
     * @throws \Exception
     */
    private function bootstrap()
    {
        $router = Router::getInstance();

        $routerConfig = $this->config()->get('router/' . strtolower($this->getAppName()));
        if ($routerConfig) {
            $router->init($routerConfig);
        }

        $res = $router->dispatch($this->request()->method(), $this->request()->getRequestUri());
        if (empty($res[0])) {
            throw new RuntimeException('Not Found', 404);
        }
        if (Router::FOUND !== $res[0]) {
            throw new RuntimeException('Method Not Allowed', 409);
        }
        $className = $res[1];

        if (!class_exists($className)) {
            throw new RuntimeException('Not Found', 404);
        }

        $controller = new $className($res[2] ?: []);
        if (!method_exists($controller, 'main') || !is_callable([$controller, 'main'])) {
            throw new RuntimeException('Method main Not Allowed', 409);
        }

        if (method_exists($controller, 'before') && is_callable([$controller, 'before'])) {
            call_user_func([$controller, 'before']);
        }

        call_user_func([$controller, 'main']);

        if (method_exists($controller, 'after') && is_callable([$controller, 'after'])) {
            call_user_func([$controller, 'after']);
        }
    }

    public function getRootPath()
    {
        return $this->rootPath;
    }

    public function getAppName()
    {
        return $this->appName;
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
     * @return LoggerInterface
     */
    public function logger()
    {
        static $logger;
        if (is_null($logger)) {
            $config = $this->env()->get('app');
            $level = $config['log_level'] ?? LogLevel::DEBUG;
            $saveHandler = $config['log_save_handler'] ?? Logger::HANDLER_FILE;
            if (Logger::HANDLER_STDOUT == $saveHandler) {
                $handler = new StreamHandler();
                $handler->setFilename($config['log_save_path'] ?? 'php://stdout');
            } else {
                $handler = new FileHandler();
                $handler->setSavePath($config['log_save_path'] ?? $this->getRootPath() . DIRECTORY_SEPARATOR . 'logs');
            }
            $logger = new Logger($level, $handler);
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
     * 代码抛出错误拦截
     * @param \Throwable $e
     */
    public function exceptionHandler(\Throwable $e)
    {
        $code = $e->getCode() ? (int) $e->getCode() : E_WARNING;
        $message = sprintf(
            'message: %s ( %d ), file: %s ( %d )',
            $e->getMessage(),
            $e->getCode(),
            $e->getFile(),
            $e->getLine()
        );

        // 日志
        $log = [
            'code' => $code,
            'message' => $message,
        ];

        // 资源参数
        if ($e instanceof MysqlException) {
            $log['sql'] = $e->getPrepareSql();
            $log['params'] = $e->getParams();
            $log['host'] = $e->getHost();
            $log['port'] = $e->getPort();
        } elseif ($e instanceof RedisException) {
            $log['method'] = $e->getMethod();
            $log['params'] = $e->getParams();
            $log['host'] = $e->getHost();
            $log['port'] = $e->getPort();
        } elseif ($e instanceof MemcacheException) {
            $log['method'] = $e->getMethod();
            $log['params'] = $e->getParams();
            $log['config'] = $e->getConfig();
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
            $this->logger()->error($log);
        } elseif (in_array($code, [E_NOTICE, E_USER_NOTICE])) {
            $this->logger()->info($log);
        } elseif ($e instanceof FatalException) {
            $this->logger()->critical($log);
        } else {
            $this->logger()->warning($log);
        }

        if ($this->request()->isXmlHttpRequest()) {
            $this->response()->error($code, $e->getMessage());
        } else {
            $this->response()->htmlError($code, $e->getMessage());
        }
        exit;
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
