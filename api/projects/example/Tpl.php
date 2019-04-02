<?php

    namespace projects\example;

    use models\Action;
    use models\common\error\CommonError;
    use models\common\param\Param;
    use models\Manger;
    use models\Sys;
    use models\tool\Arr;

    class Tpl {
        private $__aciton = null;

        /**
         * Tpl constructor.
         * @param Action $action
         */
        public function __construct(Action $action) {
            $this->__aciton = $action;
        }

        /**
         * @return Action
         */
        public function getAction() {
            return $this->__aciton;
        }


        public static function lastError($msg, $code, $lastError) {
            $keys = ['Allowed memory size', 'Invalid UTF-8 sequence in argument'];
            $log  = false;
            foreach ($keys as $kw)
                if (strstr($lastError['message'], $kw)) {
                    $log = true;
                    break;
                }
            if ($log) {
                try {
                    Sys::redis('mpr')->lPush('mprErrorLog', date('Y-m-d H:i:s') . '###' . json_encode([
                            'status'   => 400,
                            'msg'      => $msg,
                            'file'     => $lastError['file'] . $lastError['line'],
                            'code'     => $code,
                            'debugMsg' => $lastError['message'],
                            '__arg'    => Manger::$requstArgs,
                            '__debugs' => Manger::getDebugInfos(),
                        ]));
                    $data['__debugs'][] = ['title' => '记录错误', 'data' => 'ok'];
                } catch (\Exception $e) {
                    if (isset($data['__debugs']))
                        $data['__debugs'][] = ['title' => '记录错误失败', 'data' => $e->getMessage()];
                }
            }
            return [
                'status'   => 400,
                'msg'      => $msg,
                'code'     => $code,
                'debugMsg' => '',
            ];
        }


        public function output() {
            try {
                $data           = $this->getAction()->run();
                Manger::$hasOut = true;
                ob_end_clean();
                @header('content-Type:application/json;charset=utf8');
                $data = [
                    'status'     => 200,
                    'data'       => $data,
                    'detailCode' => $this->getAction()->getDetailCode(),
                    'server'     => ['nowTimestamp' => time()],
                ];
                if ($this->getAction()->isDebug()) {
                    $data['__arg']     = Manger::$requstArgs;
                    $data['__debugs']  = Sys::app()->getLogs();
                    $data['lastError'] = error_get_last();
                }
                if (Manger::$outType == 'text') {
                    die(var_export($data, true));
                } else if (Manger::$outType == 'xml') {
                    header('Content-type: text/xml');
                    $ar = new Arr();
                    $ar->toXml($data);
                    die ($ar->getXml());
                } else {
                    $json      = json_encode($data);
                    $lastError = error_get_last();
                    if ($lastError)
                        self::lastError('', '', $lastError);
                    die($json);
                }
            } catch (\Exception $exception) {
                Manger::$hasOut = true;
                ob_end_clean();
                @header('HTTP/1.1 ' . Manger::$actErrorHttpCode . ' Not Found');
                @header("status: " . Manger::$actErrorHttpCode . " Not Found");
                @header('content-Type:application/json;charset=utf8');
                $data = [
                    'status'     => 400,
                    'msg'        => $exception->getMessage(),
                    'code'       => (isset($exception->strErrorCode) && $exception->strErrorCode) ? $exception->strErrorCode : $exception->getCode(),
                    'detailCode' => $this->getAction()->getDetailCode(),
                    'server'     => ['nowTimestamp' => time()],
                    'debugMsg'   => CommonError::$debugMsgText,
                ];
                if ($this->getAction()->isDebug()) {
                    $data['ENV']       = ENV_NAME;
                    $data['file']      = $exception->getFile() . '#' . $exception->getLine();
                    $data['debugMsg']  = CommonError::$debugMsgText;
                    $data['debugData'] = CommonError::$debugData;
                    $data['__arg']     = Manger::$isDebug ? Manger::$requstArgs : [];
                    $data['__trace']   = $exception->getTrace();
                    $data['__debugs']  = Manger::getDebugInfos();
                    $data['lastError'] = error_get_last();
                }
                if (Manger::$outType == 'text') {
                    die(var_export($data, true));
                } else if (Manger::$outType == 'xml') {
                    die((new Arr())->toXml($data));
                } else {
                    if ($this->getAction()->isLogResult()) {//记录错误日志
                        try {
                            Sys::redis('mpr')->lPush('mprErrorLog', date('Y-m-d H:i:s') . '###' . json_encode($data));
                            $data['__debugs'][] = ['title' => '记录错误', 'data' => 'ok'];
                        } catch (\Exception $e) {
                            if (isset($data['__debugs']))
                                $data['__debugs'][] = ['title' => '记录错误失败', 'data' => $e->getMessage()];
                        }
                    }
                    $json      = json_encode($data);
                    $lastError = error_get_last();
                    if ($lastError)
                        self::lastError('', '', $lastError);
                    die($json);
                }
            }
        }

    }
