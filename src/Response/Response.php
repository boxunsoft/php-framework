<?php
/**
 * This file is part of the Boxunsoft package.
 *
 * (c) Jordy <arno.zheng@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Alf\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

/**
 * 响应类
 * 使用symfony/http-foundation
 */
class Response
{
    public function redirect($url, $status = HttpFoundationResponse::HTTP_FOUND)
    {
        $response = new RedirectResponse($url, $status);
        $response->send();
    }

    public function error($code, $message, $params = [])
    {
        if ($params) {
            $message = vsprintf($message, $params);
        }
        $this->output($code, $message);
    }

    public function success(array $data = [])
    {
        $this->output(0, '成功', $data);
    }

    protected function output($code, $message, array $data = [])
    {
        $code = intval($code);
        $resp = [
            'code' => (int)$code,
            'message' => (string)$message,
            'data' => $data,
        ];
        $response = new JsonResponse($resp);
        $response->send();
        exit;
    }

    public function htmlError($code, $message)
    {
        echo sprintf('%s[%d]', $message, $code);
        exit;
    }
}