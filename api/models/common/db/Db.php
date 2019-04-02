<?php

    namespace models\common\db;

    //use \console\CConsole;

    use models\Sys;

    class Db {
        public        $isAlive   = true;
        public static $dbs       = [];
        public        $db        = null;
        public static $dbKeyName = '';

        public static function db() {
            return new Db();
        }

        /**
         * @param $dbKey
         * @return MysqlPdo
         * @throws \Exception
         */
        function __get($dbKey) {
            return Sys::db($dbKey);
        }


        function __construct() {

        }

        function __destruct() {
            /*
            // TODO: Implement __destruct() method.
            foreach ($this->connections as $dbKey => $db) {
                $this->connections[$dbKey] = null;
                unset($this->connections[$dbKey]);
            }
            */
            foreach (static::$dbs as $dbKey => $db)
                unset(static::$dbs[$dbKey]);

        }

        function init() {

        }
    }
