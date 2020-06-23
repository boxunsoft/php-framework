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

use Alf\Response\Response;

trait ResponseTrait
 {
    /**
     * @var Response
     */
    private $response;

    public function response()
    {
        if (null === $this->response) {
            $this->response = new Response;
        }

        return $this->response;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }
 }