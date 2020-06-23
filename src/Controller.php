<?php
/**
 * This file is part of the Boxunsoft package.
 *
 * (c) Jordy <arno.zheng@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Alf;

use Alf\Traits\RequestTrait;
use Alf\Traits\ResponseTrait;

/**
 * 控制器
 *
 * Class Controller
 * @package Alf
 */
abstract class Controller
{
    use RequestTrait;
    use ResponseTrait;

    public function __construct(?array $params) 
    {
        $this->request()->attribute()->replace($params ?: []);
    }

    abstract public function main();
}