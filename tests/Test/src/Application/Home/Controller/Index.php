<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/2
 * Time: 3:55 PM
 */
namespace Test\Application\Home\Controller;

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