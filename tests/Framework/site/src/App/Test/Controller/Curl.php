<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/19
 * Time: 2:04 PM
 */

namespace TT\App\Test\Controller;

use All\Utils\HttpCode;

class Curl extends \Alf\Controller
{
    public function main()
    {
        $response = [];
        if ($this->request()->isPost()) {
            $response = [
                'method' => 'post',
                'params' => $this->request()->get(),
                'data' => $this->request()->post(),
                'input' => $this->request()->input(),
                'header' => [
                    'csrf_token' => $this->request()->header()->get('X_CSRF_TOKEN'),
                ]
            ];
        } elseif ($this->request()->isPut()) {
            $response = [
                'method' => 'put',
                'params' => $this->request()->get(),
                'data' => $this->request()->post(),
                'input' => $this->request()->input(),
                'header' => [
                    'csrf_token' => $this->request()->header()->get('X_CSRF_TOKEN'),
                ]
            ];
        } elseif ($this->request()->isDelete()) {
            $response = [
                'method' => 'delete',
                'params' => $this->request()->get(),
                'data' => $this->request()->post(),
                'input' => $this->request()->input(),
                'header' => [
                    'csrf_token' => $this->request()->header()->get('X_CSRF_TOKEN'),
                ]
            ];
        } elseif ($this->request()->isGet()) {
            $response = [
                'method' => 'get',
                'params' => $this->request()->get(),
                'header' => [
                    'access_token' => $this->request()->header()->get('ACCESS_TOKEN'),
                ]
            ];
        } else {
            $this->response()->error(HttpCode::BAD_REQUEST, 'Bad Request');
        }

        $this->response()->success($response);
    }
}