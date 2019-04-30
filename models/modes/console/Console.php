<?php

    namespace models\modes\console;

    use models\common\error\ConsoleError;
    use models\Sys;

    class Console {
        private $runner  = '';
        private $command = null;
        private $method  = null;
        private $args    = array();

        private static $cmd            = '';
        private static $__logFile      = '';
        private static $__errorLogFile = '';

        function __construct($args) {
            $cmd       = join(' ', $args);
            self::$cmd = $cmd;
            echo "cmd:$cmd\n";
            file_put_contents(self::getLogFile(), date('Y-m-d H:i:s', time()) . ":\t$cmd\n", FILE_APPEND);
            register_shutdown_function([$this, 'shutDown']);
            //set_error_handler([$this, 'onError']);
            try {
                $this->getArgs($args);
                if (isset($this->args['env'])) {
                    if ($this->args['env'] === 'test')
                        Sys::$env = Sys::envTest;
                    if ($this->args['env'] === 'debug')
                        Sys::$env = Sys::envDev;
                    if ($this->args['env'] === 'prod')
                        Sys::$env = Sys::envProd;
                    Sys::$configs = include __WEBROOT__ . '/config/env/' . $this->args['env'] . '.cfg.php';
                } else {
                    die("\n一定要设置环境\n");
                }
                $re       = new \ReflectionMethod($this->command, $this->method);
                $params   = $re->getParameters();
                $args     = [];
                $argNames = [];
                foreach ($params as $param) {
                    $key        = $param->name;
                    $argNames[] = $key;
                    if (isset($this->args[$key])) {
                        $args[] = $this->args[$key];
                    } else {

                    }
                }
                $diff = array_diff($argNames, array_keys($this->args));
                if (count($diff)) {
                    file_put_contents(self::getErrorLogFile(), "$cmd " . join(',', $diff) . "\n", FILE_APPEND);
                    var_dump($diff);
                }
                // var_dump($args);
                call_user_func_array(array(
                    new $this->command($this->args),
                    $this->method,
                ), $args);
            } catch (\Exception $e) {
                $str = "\n>>>>>>>\tConsoleError:" . $e->getMessage() . '>' . $e->getFile() . '>' . $e->getLine() . "\n";
                echo $str;
                if (method_exists($e, 'getDebugMsg')) {
                    echo $e->getDebugMsg() . "\n";
                }

                if (method_exists($e, 'getInfos')) {
                    var_dump($e->getInfos());
                }
                $str2 = $e->getTraceAsString();
                echo $str2;
                file_put_contents(self::getErrorLogFile(), date('Y-m-d H:i:s', time()) . "\t" . self::$cmd . "\n{$str}{$str2}\n", FILE_APPEND);
                //var_dump(debug_backtrace());
            }
        }

        function init() {

        }

        function getArgs($args) {
            $opts = array(
                'script'  => '',
                'command' => '',
                'method'  => '',
                'args'    => array(),
            );
            if (isset($args[0])) {
                $opts['script'] = $args[0];
                if (isset($args[1])) {
                    $cmdName                     = explode('.', $args[1]);
                    $cmdNameLength               = count($cmdName);
                    $cmdName[$cmdNameLength - 1] = 'cmds\Cmd' . ucfirst($cmdName[$cmdNameLength - 1]);
                    $opts['command']             = '\\projects\\' . join('\\', $cmdName);
                    if (isset($args[2])) {
                        $opts['method'] = $args[2];
                        $argsLength     = count($args);
                        if ($argsLength > 3) {
                            for ($i = 3; $i < $argsLength; $i++) {
                                $array = explode('=', $args[$i]);
                                if (count($array) == 2)
                                    $opts['args'][str_replace('--', '', $array[0])] = $array[1];
                            }
                        }
                    } else {
                        $opts['method'] = 'initCmd';
                    }
                } else {
                    self::errorMsg('unknown command name');
                }
            } else {
                self::errorMsg('script not exist?');
            }

            if (class_exists($opts['command'])) {
                $methods = get_class_methods($opts['command']);
                if (in_array($opts['method'], $methods)) {
                    $this->command = $opts['command'];
                    $this->method  = $opts['method'];
                    $this->args    = $opts['args'];
                } else {
                    self::errorMsg('method ' . $opts['method'] . ' not exist in ' . $opts['command'] . "\n\t methods:\n\t" . join("\n\t", $methods));
                }
            } else {
                self::errorMsg('command ' . $opts['command'] . ' not exist~!');
            }
        }

        public function getParam($key) {
            return isset($this->requestArgs['get'][$key]) ? $this->requestArgs['get'][$key] : null;
        }

        //扫描目录下的类列表,不过,暂时用手动添加代替
        public function getControllerList() {
            $dir = __WEBROOT__ . '/controller/';
            $ar  = scandir($dir);
            return is_array($ar) ? array_filter($ar, function ($fileName) {
                return strstr($fileName, 'Controller') ? true : false;
            }) : array();
        }

        public static function debug() {

        }

        public function __destruct() {
            /*
            $str = ob_get_contents();
            ob_clean();
            ob_flush();
            flush();
            ob_end_clean();
            ob_end_flush();
            if (DEBUG)
                echo $str;
            */
        }


        public static function errorMsg($msg) {
            throw new ConsoleError($msg, 400);
            //die;
        }

        function shutDown() {
            $d = error_get_last();
            if ($d) {
                echo "\n";
                var_dump($d);
                //debug_print_backtrace();

                file_put_contents(self::getErrorLogFile(), date('Y-m-d H:i:s', time()) . "\t" . self::$cmd . "\n" . var_export($d, true) . "\n", FILE_APPEND);
                echo "\n";
            }
        }

        public static function getErrorLogFile() {
            if (self::$__errorLogFile === '') {
                $date                 = date('Ymd');
                self::$__errorLogFile = __WEBROOT__ . '/data/log/hammer.error_' . $date . '.log';
                if (!file_exists(self::$__errorLogFile)) {
                    file_put_contents(self::$__errorLogFile, "\n", FILE_APPEND);
                    chmod(self::$__errorLogFile, 0777);
                }

            }
            return self::$__errorLogFile;

        }

        public static function getLogFile() {
            if (self::$__logFile === '') {
                $date            = date('Ymd');
                self::$__logFile = __WEBROOT__ . '/data/log/hammer.history_' . $date . '.log';
                if (!file_exists(self::$__logFile)) {
                    file_put_contents(self::$__logFile, "\n", FILE_APPEND);
                    chmod(self::$__logFile, 0777);
                }
            }
            return self::$__logFile;

        }


    }

