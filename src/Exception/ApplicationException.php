<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/25
 * Time: 下午5:20
 */

namespace Alf\Exception;

use Alf\Exception;

class ApplicationException extends Exception
{
    const CODE_NOT_ALLOW_EXTENSION = 621;
    const CODE_EMPTY_ROOT_PATH = 622;
    const CODE_EMPTY_APP_NAME = 623;
    const CODE_EMPTY_APP_NAMESPACE = 624;
    const CODE_CONTROLLER_NOT_EXISTS = 625;
    const CODE_METHOD_NOT_EXISTS = 626;
}