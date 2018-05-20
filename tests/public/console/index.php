<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require dirname(ROOT_PATH) . '/vendor/autoload.php';

$app = \Alf\App::getInstance();
$app->setRootPath(ROOT_PATH)->setAppName('Console')->run();