<?php

    namespace models\common\error;

    class CommonError extends \Exception {
        private       $infos        = [];
        private       $data         = [];
        private       $debugMsg     = '';
        public        $strErrorCode = '';
        public static $debugMsgText = '';
        public static $debugData    = [];

        public function __construct($msg, $code, $debugMsg = '', $infos = [], $preException = null) {
            if (empty($debugMsg))
                $debugMsg = $msg;
            // $this->infos    = $infos;
            // $this->debugMsg = $debugMsg;
            self::$debugMsgText = $debugMsg;
            self::$debugData    = $infos;
            parent::__construct($msg, $code, $preException);
        }

        public function getInfos() {
            return $this->infos;
        }

        public function getData() {
            return $this->data;
        }

        public function getDebugMsg() {
            return $this->debugMsg;
        }

    }

