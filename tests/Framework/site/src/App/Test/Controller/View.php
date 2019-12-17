<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/17
 * Time: 6:58 PM
 */

namespace TT\App\Test\Controller;

class View extends Controller
{
    public function main()
    {
        $this->view()->render();
    }
}