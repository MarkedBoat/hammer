<?php

    namespace models\modes\api;

    use models\Action;
    use models\common\error\Debuger;
    use models\Sys;
    use models\tool\Arr;

    class OutputerBase {
        private $__aciton = null;


        public static function model() {
            //static::class;
            $className = __CLASS__;
            return new $className();
        }

        /**
         * Tpl constructor.
         * @param ApiBase $action
         */
        public function setAction(ApiBase $action) {
            $this->__aciton = $action;
        }

        /**
         * @return ApiBase
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

        public function run() {
            $outType = '';
            try {
                $data    = $this->getAction()->run();
                $outType = $this->getAction()->getArgs()->tryGetString('outType');
                ob_end_clean();
                $data = [
                    'status'     => 200,
                    'data'       => $data,
                    'detailCode' => $this->getAction()->getDetailCode(),
                    'server'     => ['nowTimestamp' => time()],
                ];
                if ($this->getAction()->isDebug()) {
                    $data['__arg']     = $this->getAction()->getArgs()->getDataRaw();
                    $data['__debugs']  = Sys::app()->getLogs();
                    $data['lastError'] = error_get_last();
                }
                $this->output($data, $outType);

            } catch (\Exception $exception) {
                ob_end_clean();
                @header('HTTP/1.1 400 Not Found');
                @header("status: 400 Not Found");
                @header('content-Type:application/json;charset=utf8');
                $data = [
                    'status'     => 400,
                    'msg'        => $exception->getMessage(),
                    'code'       => (isset($exception->strErrorCode) && $exception->strErrorCode) ? $exception->strErrorCode : $exception->getCode(),
                    'detailCode' => $this->getAction()->getDetailCode(),
                    'server'     => ['nowTimestamp' => time()],
                ];
                if ($this->getAction()->isDebug()) {
                    $data['ENV']       = ENV_NAME;
                    $data['file']      = $exception->getFile() . '#' . $exception->getLine();
                    $data['debugMsg']  = $this->getAction()->getErrorMsg();
                    $data['debugData'] = Debuger::getDebugData();
                    $data['__arg']     = $this->getAction()->getArgs()->getDataRaw();
                    $data['__trace']   = $exception->getTrace();
                    $data['lastError'] = error_get_last();
                }
                $this->output($data, $outType);
            }
        }

        public function output($data, $outType) {
            switch ($outType) {
                case 'xml':
                    echo var_export($data, true);
                    break;
                case 'text':
                    header('Content-type: text/xml');
                    $ar = new Arr();
                    $ar->toXml($data);
                    echo $ar->getXml();
                    break;
                default:
                    @header('content-Type:application/json;charset=utf8');
                    echo json_encode($data);
                    break;
            }
            die;
        }

    }
