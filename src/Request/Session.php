<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/28
 * Time: 下午5:13
 */

namespace Alf\Request;

use Alf\Exception\WarningException;

class Session
{
    public function get($key, $default = null, $prefix = 'HTTP_')
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            throw new WarningException('Session cannot be actived');
        }
        $key = $prefix . strtoupper($key);
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    public function set($key, $value, $prefix = 'HTTP_')
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            throw new WarningException('Session cannot be actived');
        }
        $key = $prefix . strtoupper($key);
        $_SESSION[$key] = $value;
    }
}