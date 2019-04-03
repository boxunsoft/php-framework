<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/28
 * Time: 下午5:16
 */

namespace Alf\Request;

class Cookie
{
    public function get($key, $default = null, $prefix = '')
    {
        $key = $prefix . $key;
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }

    public function set($name, $value, $lifetime = 0)
    {
        $params = session_get_cookie_params();
        $currTime = time();
        $lifetime = $lifetime > 0 ? $currTime + $lifetime : ($params['lifetime'] ? $currTime + $params['lifetime'] : 0);
        return setcookie($name, $value, $lifetime, $params['path'], $params['domain'], $params['secure'],
            $params['httponly']);
    }
}