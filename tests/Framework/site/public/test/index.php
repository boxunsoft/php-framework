<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/17
 * Time: 6:12 PM
 */
$rootPath = dirname(dirname(__DIR__));

$vendor = require dirname(dirname(dirname($rootPath))) . '/vendor/autoload.php';
$vendor->addPsr4('TT\\', $rootPath . '/src');

$suffixs = ['.json'];

$kernel = \Alf\Kernel::getInstance();
$kernel->setAppNamespace('TT');
$kernel->setSuffixs($suffixs);
$kernel->main($rootPath, 'Test');
