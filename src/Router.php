<?php

namespace Alf;

use Ali\InstanceTrait;

class Router
{
    use InstanceTrait;

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function route()
    {
        return $this->request->setRoutePath($this->request->getPath());
    }
}