<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/25
 * Time: 下午1:43
 */

$rootPath = dirname(dirname(__DIR__));

/**
 * @var \Composer\Autoload\ClassLoader $loader
 */
$loader = require dirname($rootPath) . '/vendor/autoload.php';

/*----- test start ------*/
//App suggests adding composer.json
$loader->addPsr4('Ala\\', $rootPath);
/*----- test end ------*/

$suffixs = ['.json'];

$app = \Alf\Application::getInstance();
$app->setSuffixs($suffixs);
$app->startup($rootPath, 'Home');
