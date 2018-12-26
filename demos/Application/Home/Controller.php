<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/25
 * Time: 下午5:56
 */

namespace Ala\Application\Home;

use Alf\View;

class Controller extends \Alf\Controller
{
    /**
     * @var View
     */
    protected $view;

    public function __construct()
    {
        parent::__construct();
        $this->view = View::getInstance();
    }

    public function before()
    {
        error_reporting(E_ALL);
        register_shutdown_function([$this, 'shutdownHandler']);
    }

    public function after()
    {
    }

    public function shutdownHandler()
    {
//        echo 'Controller::shutdownHandler()' . PHP_EOL;
    }
}