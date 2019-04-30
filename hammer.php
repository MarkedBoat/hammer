<?php
    error_reporting(11);
    define("__WEBROOT__", __DIR__, true);
    define('DEBUG', true, true);
    define('TRACE', true, true);

    /*
     * regist autoloader
     */
    spl_autoload_register(function ($class) {
        if ($class) {
            $file = str_replace('\\', '/', $class);
            $file .= '.php';

            if (file_exists($file)) {
                include $file;
            } else {
                $file = __DIR__ . '/' . $file;
                if (file_exists($file)) {
                    include $file;
                }
            }
        }
    });
    /*
     * process manger
     */
    $console = new \models\Console($argv);
