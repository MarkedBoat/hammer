<?php

    namespace models\common\error;

    use models\Sys;

    class Interruption {
        const ERROR = 400;
        private $__detailCode = '';
        private $__code       = 400;
        private $__msg        = '';
        private $__debugMsg   = [];
        private $__debugData  = [];

        public function __construct($msg, $code = 400, $debugMsg = '', $debugData = []) {
            $this->setMsg($msg);
            $this->setCode($code);
            $this->setDebugMsg($debugMsg);
            $this->setDebugData($debugData);
        }

        /**
         * instantiate  a Interrupution
         * @param $msg
         * @param int $code
         * @param string $debugMsg
         * @param array $debugData
         * @return Interruption
         */
        public static function model($msg, $code = 400, $debugMsg = '', $debugData = []) {
            return new Interruption($msg, $code, $debugMsg, $debugData);
        }

        /**
         * @param $detailCode
         * @return Interruption
         */
        public function setDetailCode($detailCode) {
            $this->__detailCode = $detailCode;
            return $this;
        }

        /**
         * @param $code
         * @return Interruption
         */
        public function setCode($code) {
            $this->__code = $code;
            return $this;
        }

        /**
         * @param $msg
         * @return Interruption
         */
        public function setMsg($msg) {
            $this->__msg = $msg;
            return $this;
        }

        /**
         * @param $debugMsg
         * @return Interruption
         */
        public function setDebugMsg($debugMsg) {
            $this->__debugMsg = $debugMsg;
            return $this;
        }

        /**
         * @param $debugData
         * @return Interruption
         */
        public function setDebugData($debugData) {
            $this->__debugData = $debugData;
            return $this;
        }

        /**
         * @throws CommonError
         */
        public function run() {
            Sys::setDetailCode($this->__detailCode);
            throw  new CommonError($this->__msg, $this->__code, $this->__debugMsg, $this->__debugData);
        }

    }

