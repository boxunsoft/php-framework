<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/2
 * Time: 3:48 PM
 */

$rootPath = dirname(dirname(dirname(__DIR__)));
/**
 * @var
 */
$vendor = require dirname(dirname($rootPath)) . '/vendor/autoload.php';
$vendor->addPsr4('Test\\', $rootPath.'/src');


$app = Alf\Application::getInstance();
$app->setBaseAppNamespace('Test');

//$app->setExceptionHandler(function($e){
//    $message = sprintf('message111: %s ( %d ), file: %s ( %d )', $e->getMessage(), $e->getCode(), $e->getFile(),
//        $e->getLine());
//    echo $message;
//});

$app->main($rootPath, 'Home');