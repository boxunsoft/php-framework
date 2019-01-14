<?php

namespace Ala\Application\Console;

class Controller extends \Alf\Controller
{
    public function before()
    {
        echo 'Controller::before()'.PHP_EOL;
    }

    public function after()
    {
        echo 'Controller::after()'.PHP_EOL;
    }
}