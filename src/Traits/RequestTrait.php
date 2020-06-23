<?php
/**
 * This file is part of the Boxunsoft package.
 *
 * (c) Jordy <arno.zheng@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

 namespace Alf\Traits;

use All\Request\Request;

trait RequestTrait
 {
     /**
     * @var 
     */
    private $request;

    public function request()
    {
        if (null === $this->request) {
            $this->request = Request::getInstance();
        }

        return $this->request;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }
 }