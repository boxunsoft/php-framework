<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/2
 * Time: 5:00 PM
 */

namespace Alf;

class Error
{
    /**
     * 代码抛出错误拦截
     * @param Exception $e
     */
    public static function exceptionHandler($e)
    {
        /*E_ERROR,
        E_CORE_ERROR,
        E_COMPILE_ERROR,
        E_USER_ERROR,
        E_PARSE,
        E_RECOVERABLE_ERROR*/
        $message = sprintf('message: %s ( %d ), file: %s ( %d )', $e->getMessage(), $e->getCode(), $e->getFile(),
            $e->getLine());

        if (isset($_SERVER['X_REQUESTED_WITH']) && 'XMLHttpRequest' == $_SERVER['X_REQUESTED_WITH']) {
            $response = [
                'code' => $e->getCode() ? $e->getCode() : HttpCode::INTERNAL_SERVER_ERROR,
                'msg' => $message,
                'data' => []
            ];
            ob_clean();
            header('Content-type:application/json;charset=utf-8');
            //指定JSON_PARTIAL_OUTPUT_ON_ERROR,避免$data中有非utf-8字符导致json编码返回false
            echo json_encode($response, JSON_PARTIAL_OUTPUT_ON_ERROR);
        } else {
            echo $message;
        }
    }

    /**
     * 语法错误信息拦截
     *
     * @param $errorCode
     * @param $errorMessage
     * @param $errorFile
     * @param $errorLine
     */
    public static function errorHandler($errorCode, $errorMessage, $errorFile, $errorLine)
    {
        if (!(error_reporting() & $errorCode)) {
            return;
        }

        $e = new \Exception($errorMessage, $errorCode);
        $e->setFile($errorFile);
        $e->setLine($errorLine);
        exceptionHandler($e);
    }

    /**
     * 程序执行结束处理
     */
    public static function shutdownHandler()
    {
        $error = error_get_last();
        if ($error) {
            $e = new \Exception($error['message'], $error['type']);
            $e->setFile($error['file']);
            $e->setLine($error['line']);
            exceptionHandler($e);
        }
    }
}