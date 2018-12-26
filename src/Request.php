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
 * 请求类
 *
 * Class Request
 * @package Alf
 */
class Request
{
    use InstanceTrait;

    private $serverName;
    private $serverPort;
    private $serverIp;

    private $uri;
    private $requestUri;
    private $suffix = null;

    private $method;
    private $params = null;
    private $cliParams = null;

    private $clientIp;
    private $userAgent;
    private $referer;

    /**
     * URL的原始路径
     *
     * @return string
     */
    public function getRequestUri()
    {
        if ($this->requestUri) {
            return $this->requestUri;
        }
        if ($this->isCli()) {
            $cliParams = $this->_getCliParams();
            $this->requestUri = $cliParams['uri'];
        } else {
            $this->requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
            $strpos = strpos($this->requestUri, '?');
            if ($strpos !== false) {
                $this->requestUri = substr($this->requestUri, 0, $strpos);
            }
        }
        return $this->requestUri;
    }

    /**
     * 过滤后缀后的路径, 与相应应用的控制一致
     *
     * @return string
     */
    public function getUri()
    {
        if ($this->uri) {
            return $this->uri;
        }
        $requestUri = $this->getRequestUri();
        $strpos = strpos($requestUri, '.');
        if ($strpos === false) {
            $this->uri = $requestUri;
        } else {
            $this->uri = substr($requestUri, 0, $strpos);
        }
        return $this->uri;
    }

    /**
     * URL后缀, 扩展名(如index.php.json, 由认为.php.json,而不是.json)
     * @return string
     */
    public function getSuffix()
    {
        if ($this->suffix !== null) {
            return $this->suffix;
        }
        $requestUri = $this->getRequestUri();
        $this->suffix = strstr($requestUri, '.');
        $this->suffix = $this->suffix === false ? '' : $this->suffix;
        return $this->suffix;
    }

    /**
     * CLI模式下参数
     * @return array|mixed
     */
    public function getParams()
    {
        if (!$this->isCli()) {
            return [];
        }
        if ($this->params !== null) {
            return $this->params;
        }
        $cliParams = $this->_getCliParams();
        $this->params = $cliParams['params'];
        return $this->params;
    }

    public function getParam($key, $default = null)
    {
        $params = $this->getParams();
        return isset($params[$key]) ? $params[$key] : $default;
    }

    public function getInput()
    {
        return file_get_contents('php://input');
    }

    public function getPost($key = '', $default = null)
    {
        if (!$key) {
            return $_POST;
        }
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    public function getQuery($key = '', $default = null)
    {
        if (!$key) {
            return $_GET;
        }
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    public function get($key = '', $default = null)
    {
        if (!$key) {
            return $_REQUEST;
        }
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
    }

    public function getCookie($key = '', $default = null)
    {
        if (!$key) {
            return $_COOKIE;
        }
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }

    public function getHeader($key, $default = null, $prefix = 'HTTP_')
    {
        $key = $prefix . strtoupper($key);
        return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
    }

    /**
     * 获取上传的文件列表
     *
     * @return mixed
     */
    public function getFiles()
    {
        return $_FILES ? $_FILES : [];
    }

    /**
     * 获取上传的文件信息
     *
     * @param $key
     * @param null $default
     * @return null
     */
    public function getFile($key, $default = null)
    {
        $files = $this->getFiles();
        return isset($files[$key]) ? $files[$key] : $default;
    }

    public function getMethod()
    {
        if ($this->method) {
            return $this->method;
        }
        $this->method = $this->isCli() ? 'GET' : strtoupper($_SERVER['REQUEST_METHOD']);
        return $this->method;
    }

    public function getServerName()
    {
        if ($this->serverName) {
            return $this->serverName;
        }
        $this->serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
        return $this->serverName;
    }

    public function getServerPort()
    {
        if ($this->serverPort) {
            return $this->serverPort;
        }
        $this->serverPort = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '';
        return $this->serverPort;
    }

    public function getServerIp()
    {
        if ($this->serverIp) {
            return $this->serverIp;
        }
        //IP V4
        if (!empty($_SERVER['SERVER_ADDR'])) {
            $this->serverIp = $_SERVER['SERVER_ADDR'];
        } else {
            $this->serverIp = gethostbyname(gethostname());
        }
        return $this->serverIp;
    }

    public function getClientIp()
    {
        if ($this->clientIp) {
            return $this->clientIp;
        }
        //IP V4
        $ip = '';
        $unknown = 'unknown';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'],
                $unknown)) {
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $clientIp = trim(current($ipList));
            if (ip2long($clientIp) !== false) {
                $ip = $clientIp;
            }
        }
        if (!$ip && !empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
            $ip = trim($_SERVER['REMOTE_ADDR']);
        }
        $this->clientIp = $ip;
        return $this->clientIp;
    }

    public function getUserAgent()
    {
        if ($this->userAgent) {
            return $this->userAgent;
        }
        $this->userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        return $this->userAgent;
    }

    public function getReferer()
    {
        if ($this->referer) {
            return $this->referer;
        }
        $this->referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        return $this->referer;
    }

    public function isCli()
    {
        return PHP_SAPI == 'cli';
    }

    public function isPost()
    {
        return 'POST' == $this->getMethod();
    }

    public function isGet()
    {
        return 'GET' == $this->getMethod();
    }

    public function isPut()
    {
        return 'PUT' == $this->getMethod();
    }

    public function isDelete()
    {
        return 'DELETE' == $this->getMethod();
    }

    public function isXmlHttpRequest()
    {
        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
            return true;
        } else {
            return false;
        }
    }

    protected function _getCliParams()
    {
        if ($this->cliParams !== null) {
            return $this->cliParams;
        }

        $args = getopt('', array('uri:', 'params::', 'post::', 'get::'));
        $args['uri'] = empty($args['uri']) ? '/' : $args['uri'];
        //PARAMS
        if (empty($args['params'])) {
            $args['params'] = [];
        } else {
            parse_str($args['params'], $args['params']);
        }
        //POST
        if (isset($args['post'])) {
            if (empty($args['post'])) {
                $_POST = [];
            } else {
                parse_str($args['post'], $_POST);
            }
        }
        //GET
        if (isset($args['get'])) {
            if (empty($args['get'])) {
                $_GET = [];
            } else {
                parse_str($args['get'], $_GET);
            }
        }
        $this->cliParams = $args;
        return $this->cliParams;
    }
}