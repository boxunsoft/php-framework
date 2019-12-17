<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/17
 * Time: 4:47 PM
 */

namespace TT\App\Test\Controller;

use Alf\Controller;

class Bar extends Controller
{
    public function main()
    {
        $this->response()->success(['uri' => 'Bar']);
    }
}