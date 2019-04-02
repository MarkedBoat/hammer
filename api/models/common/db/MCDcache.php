<?php
    namespace models\common\db;


    use models\common\error\ArgsError;
    use models\common\error\DataError;
    use models\common\error\DbError;
    use models\common\error\ServerError;
    use models\Sys;

    class MCDcache {
        private static $servers   = [];
        private static $serverCnt = 0;
        private static $server    = null;
        private        $mem       = null;

        public static function model() {
            if (self::$serverCnt == 0)
                self::$server = new MCDcache();
            return self::$server;
        }

        public function __construct() {
            $this->mem = new \Memcached();
            if (DEBUG) {
                $this->mem->addservers([
                    ['192.168.1.20', 11211],
                ]);
                self::$serverCnt = 1;
            } else {
                $this->mem->addservers([
                    ['memcache.server1', 11211],
                    ['memcache.server2', 11211],
                    ['memcache.server3', 11211],
                    ['memcache.server4', 11211],
                ]);
                self::$serverCnt = 4;
            }
            if (self::$serverCnt == 0)
                throw new ServerError('没找到服务器', ServerError::NOT_EXIST, 'memcache');
        }


        public function getKeyIndex($key) {
            $num = ord(substr($key, - 1));
            return $num % self::$serverCnt;
        }

        public function set($key, $val, $expires = null) {
            $serverIndex = $this->getKeyIndex($key);
            if (is_array($val) || is_object($val))
                $val = '###' . json_encode($val);
            return $this->mem->setByKey($serverIndex, $key, $val, $expires);

        }

        public function get($key) {
            $serverIndex = $this->getKeyIndex($key);
            $val         = $this->mem->getByKey($serverIndex, $key);
            if (substr($val, 0, 3) == '###')
                $val = json_decode(substr($val, 3), true);
            Sys::addDebugInfos(['cache read:' . microtime(), $serverIndex], $key);
            return $val;
        }

    }

    ?>