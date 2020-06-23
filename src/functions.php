<?php
/**
 * This file is part of the Boxunsoft package.
 *
 * (c) Jordy <arno.zheng@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

use Alf\Kernel;

if (!function_exists('kernel')) {
    /**
     * @return Kernel
     */
    function kernel()
    {
        return Kernel::getInstance();
    }
}

if (!function_exists('env')) {
    /**
     * @param string $key
     * @return array|null
     */
    function env($key)
    {
        return kernel()->env()->get($key);
    }
}

if (!function_exists('config')) {
    /**
     * @param string $key
     * @return array|null
     */
    function config($key)
    {
        return kernel()->config()->get($key);
    }
}

if (!function_exists('logger')) {
    /**
     * @return \All\Logger\Logger
     */
    function logger()
    {
        return kernel()->logger();
    }
}

