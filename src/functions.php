<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/17
 * Time: 4:12 PM
 */
if (!function_exists('env')) {
    /**
     * @param string $key
     * @return array|null
     * @throws Exception
     */
    function env($key)
    {
        return \Alf\Kernel::getInstance()->env()->get($key);
    }
}

if (!function_exists('logger')) {
    /**
     * @return \All\Logger\Logger
     */
    function logger()
    {
        return \Alf\Kernel::getInstance()->logger();
    }
}

if (!function_exists('kernel')) {
    /**
     * @return \Alf\Kernel
     */
    function kernel()
    {
        return \Alf\Kernel::getInstance();
    }
}