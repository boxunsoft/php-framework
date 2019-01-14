<?php

namespace Ala\Application\Console\Controller\One\Two\Three;

use Ala\Application\Console\Controller;

class Four extends Controller
{
    public function before()
    {
        parent::before();
        echo 'Four::before()'.PHP_EOL;
    }

    public function main()
    {
        echo 'Four::main()'.PHP_EOL;
    }

    public function after()
    {
        parent::after();
        echo 'Four::after()'.PHP_EOL;
    }
}