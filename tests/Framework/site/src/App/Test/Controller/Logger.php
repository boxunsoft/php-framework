<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/17
 * Time: 6:52 PM
 */

namespace TT\App\Test\Controller;

use Alf\Controller;

class Logger extends Controller
{
    public function main()
    {
        $this->response()->success(env('app'));
    }
}