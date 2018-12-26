<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/25
 * Time: 上午11:42
 */

namespace Alf\Exception;

use Alf\Exception;

class RouterException extends Exception
{
    const CODE_INVALID = 601;
    const CODE_NOT_ROUTED = 602;
}