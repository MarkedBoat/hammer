<?php

    /**
     * Created by PhpStorm.
     * User: markedboat
     * Date: 2018/9/4
     * Time: 11:01
     */

    namespace models\modes\console;

    use models\common\error\Interruption;
    use models\common\param\Args;

    class CmdBase {
        protected $args       = null;
        private   $__planId   = '';
        private   $__cfgRedis = false;

        public static function getClassName() {
            return __CLASS__;
        }

        public function init($param = []) {
            $this->args     = new Args($param);
            $this->__planId = $this->getArgs()->tryGetString('planId');
        }

        public function setArgs(Args $args) {
            $this->args = $args;
            return $this;
        }

        /**
         * @return \models\common\param\Args
         */
        public function getArgs() {
            return $this->args;
        }

        public function initCmd(Args $args) {
            $this->setArgs($args);
            $this->run();
        }


        public function run() {

        }

        /**
         * 获取当前命令状态
         * @param \Redis $redis
         * @return bool|string
         */
        public function getCurrentStatus(\Redis $redis) {
            return $redis->get('cmd_current_status_' . $this->__planId);
        }

        /**
         *
         * @return bool
         */
        public function isCmdShutdown() {
            $shutdownStatus = $this->getConfigRedisServer()->get('cmd_is_shutdown_' . $this->__planId);
            return $shutdownStatus === 'yes' ? true : false;
        }

        /**
         * 设置配置服务器 redis
         * @param \Redis $redis
         */
        public function setConfigRedisServer(\Redis $redis) {
            $this->__cfgRedis = $redis;
        }

        /**
         * 获取配置服务器 redis
         * @return \Redis
         */
        public function getConfigRedisServer() {
            if ($this->__cfgRedis === false)
                Interruption::model('没有配置config redis server', 400)->run();
            return $this->__cfgRedis;
        }


    }