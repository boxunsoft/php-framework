<?php

namespace Bx\App\Console\Controller\One\Two\Three;

use Bx\App\Console\Controller\Controller;

class Four extends Controller
{
    public function before()
    {
        parent::before();
        echo 'Four::before()';
    }

    public function main()
    {
        echo 'Four::main()';
    }

    public function after()
    {
        parent::after();
        echo 'Four::after()';
    }
}