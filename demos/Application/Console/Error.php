<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/26
 * Time: 下午2:37
 */

namespace Ala\Application\Console;

class Error extends Controller
{
    public function main($e)
    {
        print_r($e);
        echo PHP_EOL;
    }
}