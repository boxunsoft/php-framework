<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/25
 * Time: 下午1:43
 */

$rootPath = dirname(dirname(__DIR__));
require dirname($rootPath) . '/vendor/autoload.php';

$suffixs = ['.json'];

$app = \Alf\Application::getInstance();
$app->setSuffixs($suffixs);
$app->startup($rootPath, 'Home');
