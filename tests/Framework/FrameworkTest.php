<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/17
 * Time: 4:51 PM
 */

namespace Tests\Framework;

use PHPUnit\Framework\TestCase;

class FrameworkTest extends TestCase
{
    protected $rootPath;

    protected function setUp()
    {
        $this->rootPath = __DIR__ . '/site';
    }

    public function testA()
    {
        // 没找到如何模拟CLI和HTTP的单元测试方法
        $this->assertTrue(true);
    }
}