<?php

    namespace models\common\db;


    use models\common\error\Interruption;
    use models\common\error\ServerError;

    /***
     * Memcached 缓存
     * Class MCD
     * @package models\common\db
     */
    class MCD {
        private static $servers   = [];
        private static $serverCnt = 0;
        private static $server    = null;
        private        $mem       = null;


        public function __construct(array $servers) {
            $this->mem = new \Memcached();
            $this->mem->addservers($servers);
            self::$serverCnt = count($servers);
            if (self::$serverCnt == 0)
                Interruption::model('memcached 服务器配置配置有误', ServerError::NOT_EXIST)->setDetailCode('memcached_servers_count_0')->run();
        }


        public function getKeyIndex($key) {
            $num = ord(substr($key, -1));
            return $num % self::$serverCnt;
        }

        /**
         * @param $key
         * @param $val
         * @param $ttl
         * @return bool
         */
        public function set($key, $val, $ttl = 0) {
            $serverIndex = $this->getKeyIndex($key);
            return $this->mem->setByKey($serverIndex, $key, $val, $ttl > 0 ? (time() + $ttl) : null);

        }

        /**
         * @param $key
         * @return mixed
         */
        public function get($key) {
            $serverIndex = $this->getKeyIndex($key);
            $val         = $this->mem->getByKey($serverIndex, $key);
            return $val;
        }

    }

