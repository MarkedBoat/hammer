<?php

    namespace models;

    use models\common\db\MCD;
    use models\common\db\MCDcache;
    use models\common\db\MysqlPdo;
    use models\common\error\ArgsError;
    use models\common\error\DbError;
    use models\common\error\ServerError;
    use models\common\param\Param as ParamNew;

    class Sys {

        public static $env = 1;
        const envProd = 1;
        const envTest = 2;
        const envDev  = 3;

        private static $__case           = null;
        public static  $isDebug          = false;
        public static  $hasOut           = false;
        public static  $getTuiObj        = null;
        public static  $args             = [];
        public static  $requstArgs       = [];
        public static  $runMode          = 'web';
        private static $debugInfos       = [];
        public static  $versions         = 0;
        public static  $actErrorHttpCode = 400;
        public static  $textOutPut       = false;
        public static  $configs          = [];
        public static  $cases            = [];//实例

        public static $dbRead              = null;
        public static $dbWrite             = null;
        public static $dbWriteTransactions = null;
        public static $dbConfigs           = [];

        protected      $params       = [];
        public         $methodName   = '';
        public         $version      = '2.0';
        private static $__detailCode = '';
        private        $__logs       = [];

        const redisServerHost = 'redis_list';
        const redisServerPort = 6379;


        public function __construct() {
            $this->init();
        }

        /**
         * @return Sys
         */
        public static function app() {
            if (is_null(self::$__case))
                self::$__case = new Sys();
            return self::$__case;
        }

        public function init() {

        }

        public static function getReadDb() {
            return Sys::db('bftvS');
        }

        public static function getWriteDb() {
            return Sys::db('bftvM');
        }

        /**
         * @return MysqlPdo|null
         * @throws DbError
         */


        public static function dbRead() {
            if (is_null(static::$dbRead)) {
                self::getDbConfig();
                if (DEBUG)
                    return MysqlPdo::configDb(self::$dbConfigs['bftvSlave'], true);
                if (isset(self::$dbConfigs['bftvSlave'])) {
                    static::$dbRead = MysqlPdo::configDb(self::$dbConfigs['bftvSlave'], true);
                } else {
                    throw new DbError('DB配置错误', DbError::ERROR);
                }
            }

            return static::$dbRead;
        }

        /**
         * @return MysqlPdo|null
         * @throws DbError
         */
        public static function dbWrite() {
            if (is_null(static::$dbWrite)) {
                self::getDbConfig();
                //  if (DEBUG)
                //      return MysqlPdo::configDb(self::$dbConfigs['bftv'], true);
                if (isset(self::$dbConfigs['bftv'])) {
                    static::$dbWrite = MysqlPdo::configDb(self::$dbConfigs['bftv'], true);
                } else {
                    throw new DbError('DB配置错误', DbError::ERROR);
                }
            }
            return static::$dbWrite;
        }


        /**
         * @return null
         * @throws DbError
         * @return MysqlPdo
         */
        public static function dbWriteTransactions() {
            if (is_null(static::$dbWriteTransactions)) {
                self::getDbConfig();
                if (isset(self::$dbConfigs['bftv'])) {
                    static::$dbWriteTransactions = MysqlPdo::configDb(self::$dbConfigs['bftv'], true);
                    static::$dbWriteTransactions->setAttribute(\PDO::ATTR_AUTOCOMMIT, false);
                    static::$dbWriteTransactions->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
                    static::$dbWriteTransactions->setAttribute(\PDO::ATTR_PERSISTENT, true);
                } else {
                    throw new DbError('DB配置错误', DbError::ERROR);
                }
            }
            return static::$dbWriteTransactions;
        }


        public static function getDbConfig() {
            if (empty(self::$dbConfigs))
                self::$dbConfigs = self::__getDbConfig();
        }


        private static function __getDbConfig() {
            return include self::getCfgDir() . 'db.php';
        }


        public static function getCfgs() {
            return self::$configs;
        }

        /**
         * @param $redisKey
         * @return \Redis
         * @throws ServerError
         */
        public static function redis($redisKey) {
            if (isset(self::$configs['redis'][$redisKey])) {
                if (!isset(self::$cases['redis']))
                    self::$cases['redis'] = [];
                if (!isset(self::$cases['redis'][$redisKey])) {
                    try {
                        self::$cases['redis'][$redisKey] = new \Redis();
                        self::$cases['redis'][$redisKey]->connect(self::$configs['redis'][$redisKey]['host'], self::$configs['redis'][$redisKey]['port']);
                        if (isset(self::$configs['redis'][$redisKey]['password']))
                            self::$cases['redis'][$redisKey]->auth(self::$configs['redis'][$redisKey]['password']);

                    } catch (\Exception $exception) {
                        throw  new ServerError($exception->getMessage(), $exception->getCode(), '', self::$configs['redis'][$redisKey]);
                    }

                }
            } else {
                throw  new ServerError('没有配置redis信息', ServerError::NOT_EXIST);
            }
            return self::$cases['redis'][$redisKey];
        }

        /**
         * @return MCD
         * @throws ServerError
         */
        public static function memcached() {
            if (isset(self::$cases['memcached']))
                return self::$cases['memcached'];
            if (isset(self::$configs['memcached'])) {
                if (!isset(self::$cases['memcached'])) {
                    try {
                        self::$cases['memcached'] = new MCD(self::$configs['memcached']);
                    } catch (\Exception $exception) {
                        throw  new ServerError($exception->getMessage(), $exception->getCode(), '');
                    }
                }
            } else {
                throw  new ServerError('没有配置memcached信息', ServerError::NOT_EXIST);
            }
            return self::$cases['memcached'];
        }


        /**
         * @param $dbKey
         * @return MysqlPdo
         * @throws ServerError
         */
        public static function db($dbKey) {
            if (isset(self::$configs['db'][$dbKey])) {
                if (!isset(self::$cases['db']))
                    self::$cases['db'] = [];
                if (!isset(self::$cases['db'][$dbKey])) {
                    self::$cases['db'][$dbKey] = MysqlPdo::configDb(self::$configs['db'][$dbKey]);
                }
            } else {
                throw  new ServerError('没有配置信息:db>' . $dbKey . '>' . ENV_NAME, ServerError::NOT_EXIST);
            }
            return self::$cases['db'][$dbKey];
        }

        public static function unsetDb($dbKey) {
            if (isset(self::$configs['db'][$dbKey])) {
                if (!isset(self::$cases['db']))
                    self::$cases['db'] = [];
                if (isset(self::$cases['db'][$dbKey])) {
                    self::$cases['db'][$dbKey] = null;
                    return true;
                } else {
                    return false;
                }
            } else {
                throw  new ServerError('没有配置信息:db>' . $dbKey . '>' . ENV_NAME, ServerError::NOT_EXIST);
            }
        }


        public function isInternal() {
            return false;
        }


        public static function getRemoteIp() {
            return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        }


        public static function addDebugInfos($data, $key = '') {
            if (self::$isDebug)
                if (empty($key))
                    self::$debugInfos[] = $data; else self::$debugInfos[$key] = $data;
        }

        public static function getDebugInfos() {
            return self::$debugInfos;
        }

        public static function getCfgDir() {
            switch (self::$env) {
                case self::envTest:
                    return __WEBROOT__ . '/config/test/';
                    break;
                case self::envDev:
                    return __WEBROOT__ . '/config/dev/';
                    break;
                default:
                    return __WEBROOT__ . '/config/';
                    break;
            }
        }

        public static function setDetailCode($detailCode) {
            return self::$__detailCode = $detailCode;
        }

        public static function getDetailCode() {
            return self::$__detailCode;
        }

        public static function returnData($data, $detailCode) {
            self::$__detailCode = $detailCode;
            return $data;
        }

        /**
         * @param $info
         */
        public function logInfo($info) {
            $this->__logs[] = $info;
        }

        /**
         * @return array
         */
        public function getLogs() {
            return $this->__logs;
        }

    }

