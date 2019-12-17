<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/17
 * Time: 4:41 PM
 */
return [
    'log_level' => All\Logger\Logger::E_FATAL,
    'log_save_path' => kernel()->getRootPath() . '/logs',
    'log_save_handler' => All\Logger\Logger::HANDLER_FILE,
];