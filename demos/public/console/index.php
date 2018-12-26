<?php

$rootPath = dirname(dirname(__DIR__));
require dirname($rootPath) . '/vendor/autoload.php';

$app = \Alf\Application::getInstance();
$app->startup($rootPath, 'Console');