<?php

    namespace models;


    use models\common\error\ArgsError;
    use models\common\error\ProcessError;

    class Manger {
        public static  $isDebug          = false;
        public static  $hasOut           = false;
        public static  $getTuiObj        = null;
        private        $requestArgs      = array();
        public static  $args             = [];
        public static  $requstArgs       = [];
        public static  $runMode          = 'web';
        private static $debugInfos       = [];
        public static  $versions         = 0;
        private static $tplClassName     = '';
        public static  $actErrorHttpCode = 400;
        public static  $textOutPut       = false;
        public static  $outType          = 'json';
        public static  $logError         = false;
        public static  $userAgent        = '';
        public static  $isFromWebServer  = '';

        function __construct() {

        }

        public function run() {
            register_shutdown_function(array(
                $this,
                'shutDown'
            ));
            //本层并没有分有区分哪个项目
            if (isset(self::$requstArgs['actErrorHttpCode']) && self::$requstArgs['actErrorHttpCode'])
                self::$actErrorHttpCode = intval(self::$requstArgs['actErrorHttpCode']);
            if (isset(self::$requstArgs['outType']) && self::$requstArgs['outType'])
                self::$outType = self::$requstArgs['outType'];
            try {
                if ($_SERVER['REQUEST_METHOD'] != "POST")
                    throw new ArgsError('请使用post方法', ArgsError::ERROR);
                if (isset(self::$requstArgs['kldebug']) && self::$requstArgs['kldebug'] == 'x')
                    self::$isDebug = true;
                if (isset(self::$requstArgs['versions']) && self::$requstArgs['versions'])
                    self::$versions = true;
                self::outData(self::start(array_merge(self::$requstArgs, $this->getArgs())));

            } catch (\Exception $e) {
                if (TRACE && false) {
                    echo "\n trace:\n";
                    echo $e->getMessage();
                    debug_print_backtrace();
                    echo "\n";
                    die;
                } else {
                    self::outError($e->getMessage());
                }
            }
        }


        public static function start($post) {
            self::$args = $post;
            if (isset($post['method']) && $post['method']) {
                $methodInfos = explode('.', $post['method']);
                $methodParts = $methodInfos;
                $count       = count($methodInfos);
                if ($count < 3) {
                    throw new ArgsError('参数丢失1', ArgsError::LOST);
                } else {


                    try {
                        $methodInfos[2] = ucfirst($methodInfos[2]);
                        if (isset($methodInfos[3])) {
                            $action = $methodInfos[3];
                            unset($methodInfos[3]);
                        } else {
                            throw new ArgsError('参数丢失2', ArgsError::LOST);
                        }
                        $classPath = '\\projects\\' . join('\\', $methodInfos);
                        if (class_exists($classPath)) {
                            $obj = new $classPath();
                            $obj->setParam($post);
                            // if ($obj->isInternal())
                            //    throw new ArgsError('接口不存在', ArgsError::NOT_EXIST);
                            $actionName = 'act' . ucfirst($action);
                            if (method_exists($obj, $actionName)) {
                                //如果一切都正常执行，会用模板定义的模板进行输出
                                self::$tplClassName = '\projects\\' . $methodInfos[0] . '\Tpl';
                                call_user_func_array([
                                    self::$tplClassName,
                                    'out'
                                ], [$obj->$actionName($post)]);
                            } else {
                                self::runAsAction($methodParts, $action, $post);
                            }
                        } else {
                            self::$tplClassName = '\\projects\\' . $methodInfos[0] . '\\Tpl';
                            //throw new ArgsError('找不到method', ArgsError::NOT_EXIST, '找不到类' . $classPath, [$classPath]);
                            self::runAsAction($methodParts, $action, $post);
                        }
                    } catch (\Exception $e) {
                        //如果上面遇到了错误，就用项目自定义的模板把错误输出
                        try {
                            self::$tplClassName = '\\projects\\' . $methodInfos[0] . '\\Tpl';
                            call_user_func_array([
                                self::$tplClassName,
                                'error'
                            ], [$e]);
                        } catch (\Exception $e) {
                            throw new ProcessError('程序错误', ProcessError::RUN_ERROR);
                        }
                    }


                }
            } else {
                throw new ArgsError('method', ArgsError::LOST);
            }
        }

        public static function runAsAction($methodInfos, $action, $post) {
            $methodInfos[2] = lcfirst($methodInfos[2]);
            $last           = count($methodInfos) - 1;
            $action         = $methodInfos[$last];
            unset($methodInfos[$last]);
            $actionClassPath = '\\projects\\' . join('\\', $methodInfos) . '\actions\Action' . ucfirst($action);
            if (class_exists($actionClassPath)) {
                self::$tplClassName = '\projects\\' . $methodInfos[0] . '\Tpl';
                $tplClassName       = self::$tplClassName;
                call_user_func_array([
                    new $tplClassName(new $actionClassPath($post)),
                    'output'
                ], []);
            } else {
                throw new ArgsError('method不存在', ArgsError::ERROR, [$actionClassPath]);
            }
        }


        public function tplOutError() {

        }

        public function outData($data) {
            @header('content-Type:text/json;charset=utf8');
            echo json_encode([
                'status' => 200,
                'data'   => $data
            ]);
        }

        public function outError($msg) {
            @header('HTTP/1.1 ' . self::$actErrorHttpCode . ' Not Found');
            @header("status: " . self::$actErrorHttpCode . " Not Found");
            @header('content-Type:text/json;charset=utf8');
            echo json_encode([
                'status' => 400,
                'msg'    => $msg,
            ]);
        }

        function init() {

        }


        function getArgs() {
            if (self::$runMode !== 'web')
                return [];
            $uri = trim(strstr($_SERVER['REQUEST_URI'], '?') ? explode('?', $_SERVER['REQUEST_URI'])[0] : $_SERVER['REQUEST_URI'], '/');
            if ($uri) {
                $elements = explode('/', $uri);
                if (count($elements) === 2) {
                    $data = [];
                    if (!empty($elements[0]) && substr_count($elements[0], '.') >= 3) {
                        $data['method']     = $elements[0];
                        $_REQUEST['method'] = $elements[0];

                    } else {
                        throw new ArgsError('参数错误:method', ArgsError::ERROR);
                    }
                    if (!empty($elements[1]) && $elements[1][0] === 'v') {
                        $data['v']     = substr($elements[1][0], 1);
                        $_REQUEST['v'] = $data['v'];
                    } else {
                        throw new ArgsError('参数错误:v', ArgsError::ERROR);
                    }
                    return $data;
                } else {
                    throw new ArgsError('参数丢失:method或者v', ArgsError::LOST);
                }
            } else {
                return [];
            }
        }


        public function getParam($key) {
            return isset($this->requestArgs['get'][$key]) ? $this->requestArgs['get'][$key] : null;
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




        function shutDown() {
            if (self::$hasOut)
                return false;
            $d = error_get_last();
            if ($d) {
                ob_end_clean();
                @header('HTTP/1.1 ' . self::$actErrorHttpCode . ' Not Found');
                @header("status: " . self::$actErrorHttpCode . " Not Found");
                @header('content-Type:text/json;charset=utf8');
                die(call_user_func_array([
                    self::$tplClassName,
                    'lastError'
                ], [
                    '服务器错误',
                    ProcessError::RUN_ERROR,
                    $d
                ]));
            } else {

            }
        }


        function shutDownInBack() {
            $d = error_get_last();
            if ($d) {
                if (self::$isDebug) {
                    echo '<pre>';
                    var_dump($d);
                    debug_print_backtrace();
                    echo '</pre>';
                } else {

                }
            }
        }

        //make sure the pay sign is wright
        public static function isSureSign($orderId, $sureSign) {
            $str16  = dechex(str_replace(substr($orderId, -1), '', $orderId));
            $idHash = md5($str16);
            for ($i = 1; $i <= 16; $i++) {
                $sign = substr($idHash, $i, 16);
                if (strstr($sureSign, $sign))
                    return true;
            }
            return false;
        }

        public static function getSureSign($orderId) {
            $str16         = dechex(str_replace(substr($orderId, -1), '', $orderId));
            $idHash        = md5($str16);
            $start         = rand(1, 16);
            $strSign       = substr($idHash, $start, 16);
            $startReplaced = rand(1, 16);
            $strReplaced   = md5($startReplaced);
            $strBeReplaced = substr($strReplaced, $startReplaced, 16);
            return str_replace($strBeReplaced, $strSign, $strReplaced);
        }

        public static function redirect($url) {
            ob_start();
            $contents = ob_get_contents();
            ob_end_clean();
            header('Location:' . $url);
        }

        public static function addDebugInfos($data, $key = '') {
            if (Manger::$isDebug)
                self::$debugInfos[] = ['title' => $key, 'data' => is_string($data) ? "\n{$data}\n" : $data];
        }

        public static function getDebugInfos() {
            return self::$debugInfos;
        }

        public static function isFromWebServer() {
            if (self::$isFromWebServer !== '')
                return self::$isFromWebServer;
            self::$isFromWebServer = false;
            Manger::addDebugInfos($_SERVER);
            if (isset($_SERVER['HTTP_WEB_SERVER_TIME']) && isset($_SERVER['HTTP_WEB_SERVER_SIGN']) && intval($_SERVER['HTTP_WEB_SERVER_TIME']) > (time() - 300))
                self::$isFromWebServer = substr(md5($_SERVER['HTTP_WEB_SERVER_TIME'] . 'kinglone'), 10) === $_SERVER['HTTP_WEB_SERVER_SIGN'] ? true : false;
            return self::$isFromWebServer;
        }

        public static function setFromServerSide() {
            self::$isFromWebServer = true;
        }

        public static function isFromCMSWebServer() {
            return self::$userAgent === 'BftvcmsWeb' ? true : false;
        }
    }

