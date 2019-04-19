<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/25
 * Time: 上午11:43
 */
require dirname(__DIR__) . '/autoload.php';

use Alf\Router;
use Alf\Request;

$Router = Router::getInstance();
$Request = Request::getInstance();

echo $Request->requestUri().PHP_EOL;
print_r($Request->params());
echo PHP_EOL;

$uri = $Request->uri();
$routerConfig = [
    '/foo/bar/:ac_id/:list_id/:article_id',
    '/foo/bar/:article_id',
    '/foo/bar/:list_id/:article_id',
    '/:list_id/:article_id',
];
$Router->setConfig($routerConfig);
echo $Router->route($uri);
echo PHP_EOL;
print_r($Router->getParams());
echo PHP_EOL;


exit;
$uri = '/foo/bar/232/234242';

$router = '/foo/bar/:list_id/:article_id';

$r = preg_match('#^/foo/bar/([a-zA-Z0-9_]+)/([a-zA-Z0-9_]+)$#i', $uri, $m);
var_dump($r, $m);

//$r = preg_match_all('/:[a-z0-9_]+/i', $router, $m);
//var_dump($r, $m);

$r = preg_replace('/\/:[a-z0-9_]+/i', '', $router);
var_dump($r);