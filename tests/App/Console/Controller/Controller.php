<?php

namespace Bx\App\Console\Controller;

class Controller
{
    public function before()
    {
        echo 'Controller::before()';
    }

    public function after()
    {
        echo 'Controller::after()';
    }
}