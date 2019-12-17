<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/17
 * Time: 6:39 PM
 */

namespace TT\App\Test\Controller\Router;

use Alf\Controller;
use All\Router\Router;

class Bar extends Controller
{
    public function main()
    {
        $this->response()->success(Router::getInstance()->getParams());
    }
}