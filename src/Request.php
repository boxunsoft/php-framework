<?php

namespace Alf;

use Ali\InstanceTrait;

class Request
{
    use InstanceTrait;

    private $scheme;
    private $host;
    private $port;
    private $path;
    private $realPath;
    private $routePath;
    private $suffix;

    private $clientIp;
    private $serverIp;
    private $userAgent;
    private $referer;

    public function __construct()
    {
        $this->parsePath();
    }

    private function parsePath()
    {
        if ( ! $this->path) {
            $path = $this->parseRealPath();
            $lastPos = strrpos($path, '.');
            if ($lastPos !== false) {
                $this->suffix = strtolower(substr($path, $lastPos + 1));
                $path = substr($path, 0, $lastPos);
            }
            $this->path = $path;
        }

        return $this->path;
    }

    private function parseRealPath()
    {
        if ( ! $this->realPath) {
            if (PHP_SAPI == 'cli') {
                $args = getopt('', array('uri:', 'param::'));
                $requestUri = empty($args['uri']) ? '' : $args['uri'];

                if ( ! empty($args['param'])) {
                    parse_str($args['param'], $_REQUEST);
                }
            } else {
                $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            }

            $strpos = strpos($requestUri, '?');
            if ($strpos === false) {
                $this->realPath = strtolower($requestUri);
            } else {
                $this->realPath = strtolower(substr($requestUri, 0, $strpos));
            }
        }

        return $this->realPath;
    }

    public function setRoutePath($routePath)
    {
        $this->routePath = $routePath;
        return $this;
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function getHost()
    {
        $this->host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getRealPath()
    {
        return $this->realPath;
    }

    public function getRoutePath()
    {
        return $this->routePath;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }

    public function getClientIp()
    {
        //IP V4
        if ( ! $this->clientIp) {
            $ip = '';
            $unknown = 'unknown';
            if ( ! $ip && ! empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'],
                    $unknown)) {
                $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $clientIp = trim(current($ipList));
                if (ip2long($clientIp) !== false) {
                    $ip = $clientIp;
                }
            }
            if ( ! $ip && ! empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
                $ip = trim($_SERVER['REMOTE_ADDR']);
            }
            $this->clientIp = $ip;
        }
        return $this->clientIp;
    }

    public function getServerIp()
    {
        //IP V4
        if ( ! $this->serverIp) {
            if ( ! empty($_SERVER['SERVER_ADDR'])) {
                $this->serverIp = $_SERVER['SERVER_ADDR'];
            } else {
                $this->serverIp = gethostbyname(gethostname());
            }
        }
        return $this->serverIp;
    }

    public function getUserAgent()
    {
        $this->userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        return $this->userAgent;
    }

    public function getReferer()
    {
        $this->referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        return $this->referer;
    }
}