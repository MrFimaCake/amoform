<?php
/**
 * Created by PhpStorm.
 * User: mrcake
 * Date: 12/2/17
 * Time: 12:30 AM
 */

spl_autoload_register(function ($className) {
    $expectedPath = './'. str_replace('\\', '/', $className) . '.php';
    if (file_exists($expectedPath)) {
        require_once $expectedPath;
    }
});