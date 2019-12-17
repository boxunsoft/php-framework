<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/17
 * Time: 6:56 PM
 */

namespace TT\App\Test\Controller;

use Alf\View;

abstract class Controller extends \Alf\Controller
{
    /**
     * @var View
     */
    private $view;

    public function __construct()
    {
        parent::__construct();
        $this->view = View::getInstance();
        $this->view->setRootPath(kernel()->getAppPath() . '/View');
    }

    /**
     * @return View
     */
    public function view()
    {
        return $this->view;
    }
}