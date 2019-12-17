<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/17
 * Time: 4:47 PM
 */

namespace TT\App\Test\Controller\Foo;

use Alf\Controller;
use All\Utils\HttpCode;

class Bar extends Controller
{
    public function main()
    {
        $this->response()->error(HttpCode::NO_CONTENT, 'NoData');
    }
}