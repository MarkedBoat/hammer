<?php

    namespace models\modes\api;


    use models\common\error\ProcessError;
    use models\common\param\Args;

    class Api {
        public static $isDebug          = false;
        public static $hasOut           = false;
        public static $actErrorHttpCode = 400;
        public static $textOutPut       = false;
        public static $logError         = false;
        private       $__version        = 0;
        private       $__method         = [];
        private       $__methodName     = '';
        /** @var Args */
        private $__requestArgs = null;
        /** @var OutputerBase */
        private $__outputer = null;

        function __construct() {

        }

        public function run() {
            register_shutdown_function([$this, 'shutDown']);
            //本层并没有分有区分哪个项目
            try {
                echo '<pre>';
                $r     = preg_match('/^\/v\d+\/[\w+,.]+($|\/|\?)/i', $_SERVER['REQUEST_URI'], $ar);
                $level = 0;
                if ($r === 1) {
                    $matchCnt = preg_match_all('/\w+/', $ar[0], $match1);
                    $v        = array_shift($match1[0]);
                    $level    = $matchCnt - 1;
                    if ($level > 2 && $level < 5) {
                        $this->__version    = intval(str_replace('v', '', $v));
                        $this->__method     = $match1[0];
                        $this->__methodName = join('.', $match1[0]);
                    }
                }
                $this->__requestArgs = new Args($_REQUEST);//接受参数
                if ($this->__methodName === '') {
                    $this->__methodName = $this->__requestArgs->getStringNotNull('method', '没有api方法');
                    $this->__method     = explode('.', $this->__methodName);
                    $level              = count($this->__method);
                }
                $this->__initOutputer();
                //if ($_SERVER['REQUEST_METHOD'] != "POST")
                //   throw new ArgsError('请使用post方法', ArgsError::ERROR);
                $this->__method[]           = 'Api' . ucfirst($this->__method[$level - 1]);
                $this->__method[$level - 1] = 'apis';
                $actionClassName            = '\\projects\\' . join('\\', $this->__method);
                $this->__outputer->setAction($this->__getAction($actionClassName));
                $this->__outputer->run();
            } catch (\Exception $e) {
                ob_end_clean();
                @header('HTTP/1.1 ' . self::$actErrorHttpCode . ' Not Found');
                @header("status: " . self::$actErrorHttpCode . " Not Found");
                @header('content-Type:text/json;charset=utf8');
                die(json_encode(['status' => 400, 'msg' => $e->getMessage()]));
            }
        }


        /**
         * @throws \Exception
         */
        private function __initOutputer() {
            $outputerClassName = '\\projects\\' . $this->__method[0] . '\\Outputer';
            if (!class_exists($outputerClassName))
                throw  new \Exception('项目不存在或者未配置好'.$outputerClassName);
            $this->__outputer = call_user_func_array([$outputerClassName, 'model'], []);
        }

        private function __getAction($actionClassName) {
            if (!class_exists($actionClassName))
                throw  new \Exception('api不存在'.$actionClassName);
            return call_user_func_array([$actionClassName, 'model'], [$this->__requestArgs]);
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

            $d = error_get_last();
            if ($d) {
                ob_end_clean();
                @header('HTTP/1.1 200 Not Found');
                @header("status: 200 Not Found");
                @header('content-Type:text/json;charset=utf8');
               var_dump($d);
            } else {

            }
        }
    }

