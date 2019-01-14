<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/25
 * Time: 下午6:00
 */

namespace Ala\Application\Home\Controller\Login;

use Ala\Application\Home\Controller;

class Index extends Controller
{
    public function main()
    {
        $response = [
            'name' => 'index',
            'message' => 'Index::main()'
        ];
        $this->response()->success($response);
    }
}