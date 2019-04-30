<?php


    use models\Sys;

    ob_start();
    error_reporting(11);
    define('__HOST__', $_SERVER['HTTP_HOST'], true);
    define("__WEBROOT__", dirname(__FILE__), true);

    function autoload($class) {
        if ($class) {
            $file = __WEBROOT__ . '/' . str_replace('\\', '/', $class);
            $file .= '.php';
            if (file_exists($file)) {
                include $file;
            }
        }
    }


    spl_autoload_register('autoload');
    /*
    function shutDown() {
        $d = error_get_last();
        echo '<pre>';
        var_dump($d);
        debug_print_backtrace();
        echo '</pre>';
    }
    register_shutdown_function('shutDown');
*/

    $filename = __WEBROOT__ . '/config/' . __HOST__ . '.conf.php';
    if (is_file($filename)) {
        Sys::$configs                   = include $filename;
        Sys::$configs['configFilename'] = $filename;
    } else {
        Sys::$configs                   = include __WEBROOT__ . '/config/default.conf.php';
        Sys::$configs['configFilename'] = __WEBROOT__ . '/config/default.conf.php';
    }

    $app = new \models\common\api\Api();
    $app->run();
    die;



