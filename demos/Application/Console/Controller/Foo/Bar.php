<?php

namespace Ala\Application\Console\Controller\Foo;

use Ala\Application\Console\Controller;

class Bar extends Controller
{
    public function main()
    {
        echo 'Bar::main()'.PHP_EOL;
        print_r($this->request()->getParams());
        echo PHP_EOL;
    }
}