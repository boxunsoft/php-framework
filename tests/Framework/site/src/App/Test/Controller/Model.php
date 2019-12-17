<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/17
 * Time: 7:01 PM
 */

namespace TT\App\Test\Controller;

use TT\Model\Test\UserModel;

class Model extends Controller
{
    public function main()
    {
        $UserModel = UserModel::getInstance();
        $list = $UserModel->getList(1, 10);

        $this->response()->success(['list' => $list]);
    }
}