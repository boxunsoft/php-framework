<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/25
 * Time: 下午8:34
 */

namespace Ala\Application\Home;

class Error extends Controller
{
    public function main($e)
    {
        print_r($e);
        $this->response()->stop();
    }
}