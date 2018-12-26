<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/25
 * Time: 上午11:43
 */
require dirname(__DIR__).'/autoload.php';

use Alf\Router;
use Alf\Request;

$Router = Router::getInstance();
$Request = Request::getInstance();

echo $Request->getRequestUri().PHP_EOL;
print_r($Request->getParams());
echo PHP_EOL;

$uri = $Request->getUri();
var_dump($uri);
echo $Router->route($uri);
echo PHP_EOL;
