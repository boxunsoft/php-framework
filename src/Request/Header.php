<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/28
 * Time: 下午5:22
 */

namespace Alf\Request;

class Header
{
    public function get($key, $default = null, $prefix = 'HTTP_')
    {
        $key = $prefix . strtoupper($key);
        return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
    }
}