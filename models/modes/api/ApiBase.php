<?php

    namespace models\modes\api;

    use models\common\error\Interruption;
    use models\common\param\Args;


    /**
     * Class Action
     * @package models
     * 接口具体方法的抽你类
     */
    abstract class ApiBase {
        protected $args           = null;
        public    $uniqueId       = '';
        private   $__detailCode   = false;
        private   $__errorMessage = '';
        private   $__errorCode    = 400;
        private   $__debugMessage = '';
        private   $__debugData    = null;
        private   $__logResult    = false;
        private   $__debug        = false;
        private   $__apiName      = '';

        public function model(){
            $className=static::class;
            return new $className();
        }
        public function init($param = []) {
            $this->args      = new Args($param);
            $this->__apiName = $this->args->tryGetString('method');
        }

        public function setArgs(Args $args) {
            $this->args = $args;
            return $this;
        }

        public function initCmd(Args $args) {
            $this->setArgs($args);
            $this->run();
        }

        public static function getClassName() {
            return __CLASS__;
        }

        public static function getActionName() {
            return static::getClassName();
        }

        /**
         * @return \models\common\param\Args
         */
        public function getArgs() {
            return $this->args;
        }

        public abstract function run();



        /**
         * @param string $detailCode
         * @return Action
         */
        public function setDetailCode($detailCode) {
            $this->__detailCode = $detailCode;
            return $this;
        }

        public function getDetailCode() {
            return $this->__detailCode;
        }

        /**
         * @param $msg
         * @return Action
         */
        public function setErrorMsg($msg) {
            $this->__errorMessage = $msg;
            return $this;
        }

        public function getErrorMsg() {
            return $this->__errorMessage;
        }

        /**
         * @param int $code
         * @return Action
         */
        public function setErrorCode($code) {
            $this->__errorCode = $code;
            return $this;
        }

        /**
         * @param $debugMessage
         * @return Action
         */
        public function setDebugMsg($debugMessage) {
            $this->__debugMessage = $debugMessage;
            return $this;
        }

        /**
         * @param $data
         * @return Action
         */
        public function setDebugData($data) {
            $this->__debugData = $data;
            return $this;
        }

        public function setError($msg, $code) {
        }

        public function outError() {
            Interruption::model($this->__errorMessage, $this->__errorCode, $this->__debugMessage, $this->__debugData)->run();
        }

        /**
         * 记录输出结果
         * @param bool $debug 结果中要不要debug？
         */
        public function logResult($debug = false) {
            $this->__logResult = true;
            $this->__debug     = $debug;
        }

        public function isLogResult() {
            return $this->__logResult;
        }

        public function debug() {
            $this->__debug = true;
        }

        public function isDebug() {
            return $this->__debug;
        }

        public function logInfo($info) {
            Sys::app()->logInfo($info);
        }

        public function setApiName($apiName) {
            $this->__apiName = $apiName;
        }

        public function getApiName() {
            return $this->__apiName;
        }
    }

