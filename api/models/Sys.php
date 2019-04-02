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
        private        $requestArgs      = array();
        public static  $args             = [];
        public static  $requstArgs       = [];
        public static  $runMode          = 'web';
        private static $debugInfos       = [];
        public static  $versions         = 0;
        private static $tplClassName     = '';
        public static  $actErrorHttpCode = 400;
        public static  $textOutPut       = false;
        public static  $configs          = [];
        public static  $cases            = [];//实例
        const TableName = '{album}';
        private $__db = null;

        public static $dbRead              = null;
        public static $dbWrite             = null;
        public static $dbWriteTransactions = null;
        public static $dbConfigs           = [];

        public static  $redisServer  = null;
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

        public function setParam($param) {
            $this->params     = $param;
            $this->methodName = ParamNew::getStringNotNull($this->params, 'method');
            if (ParamNew::tryGetString($this->params, 'version'))
                $this->version = ParamNew::tryGetString($this->params, 'version');
        }

        public function getCache($key = '') {
            self::addDebugInfos('try cache:' . microtime(), $key);
            if (isset($this->params['nocache']))
                return false;
            return MCDcache::model()->get($this->methodName . '.' . $key);
        }

        public function setCache($key, $data, $expires = 3600) {
            return MCDcache::model()->set($this->methodName . '.' . $key, $data, $expires);
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

        public static function getNewWriteDb() {
            if (is_null(static::$dbWrite)) {
                self::getDbConfig();
                if (isset(self::$dbConfigs['bftv'])) {
                    return MysqlPdo::configDb(self::$dbConfigs['bftv'], true);
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


        public static function getQueueRedis() {
            if (is_null(self::$redisServer)) {
                self::$redisServer = new \Redis();
                self::$redisServer->connect(self::redisServerHost, self::redisServerPort);
            }
            return self::$redisServer;
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

        public function getConnection() {

        }

        public static function getSqlSetStr($keys, $bindKey = 0) {
            $str = [];
            foreach ($keys as $realKey) {
                if (isset(static::$__attributes[$realKey])) {
                    $str[] = "`$realKey`=:bind_$bindKey" . "_$realKey";
                } else {
                    throw new \Exception('key not exist)' . $realKey);
                }
            }
            return join(',', $str);
        }

        public static function getSqlConditionStr($keys, $bindKey = 0) {
            $str = [];
            foreach ($keys as $realKey) {
                if (isset(static::$__attributes[$realKey])) {
                    $str[] = "`$realKey`=:bind_$bindKey" . "_$realKey";
                } else {
                    throw new \Exception('key not exist)' . $realKey);
                }
            }
            return join(' and ', $str);
        }

        public static function formatAttribute($keyName, $value, $fakeKeyName = '') {
            if (isset(static::$__attributes[$keyName])) {
                $attribute = static::$__attributes[$keyName];
            } else {
                throw new \Exception($fakeKeyName . ' not exist,12');
            }

            switch ($attribute['type']) {
                case 'int':
                    $value = intval($value);
                    break;
                default:
                    $value = htmlspecialchars(trim($value));
                    break;
            }
            if ($attribute['length'] && mb_strlen($value, 'UTF-8') > $attribute['length'])
                throw new \Exception($fakeKeyName . '字段长度越界' . mb_strlen($value, 'UTF-8'));
            return $value;
        }

        public static function checkNecessaryKey($param, $necessaryKeys) {
            $keys = array_diff($necessaryKeys, array_keys($param));
            $r    = count($keys) ? true : false;
            if ($r === true)
                throw new \Exception('lost necessary key:' . join(',', $keys), Error::ARGS_LOST);
            return $r;
        }

        public static function checkRequireKey($param, $necessaryKeys) {
            $r        = false;
            $emptyKey = '';
            foreach ($necessaryKeys as $k) {
                if (!isset($param[$k]) || empty($param[$k])) {
                    $r        = true;
                    $emptyKey = $k;
                    break;
                }
            }
            if ($r === true)
                throw new \Exception('cant not be empty key:' . $emptyKey, Error::ARGS_LOST);
            return $r;
        }

        public static function requireKey($param, $necessaryKeys, $noNull = true) {
            $r        = false;
            $emptyKey = '';
            foreach ($necessaryKeys as $k) {
                if (!isset($param[$k]) || ($noNull && empty($param[$k]))) {
                    $r        = true;
                    $emptyKey = $k;
                    break;
                }
            }
            if ($r === true)
                throw new ArgsError('参数必须:' . $emptyKey, ArgsError::ERROR);
            return $r;
        }

        public static function requireArgs($param, $args) {
            $r        = false;
            $emptyKey = '';
            foreach ($args as $k) {
                if (!isset($param[$k])) {
                    $r        = true;
                    $emptyKey = $k;
                    break;
                }
            }
            if ($r === true)
                throw new ArgsError('参数必须:' . $emptyKey, ArgsError::ERROR);
            return $r;
        }

        public static function requireArgsNotNull($param, $args) {
            $r        = false;
            $emptyKey = '';
            foreach ($args as $k) {
                if (!isset($param[$k]) || empty($param[$k])) {
                    $r        = true;
                    $emptyKey = $k;
                    break;
                }
            }
            if ($r === true)
                throw new ArgsError('参数不能为空:' . $emptyKey, ArgsError::ERROR);
            return $r;
        }

        public static function needKey($param, $necessaryKeys) {
            $keys = array_diff($necessaryKeys, array_keys($param));
            $r    = count($keys) ? true : false;
            if ($r === true)
                throw new ArgsError('不能为空:' . join(',', $keys), ArgsError::ERROR);
            return $r;
        }

        public static function getBindDataWithTrueKey($param, $dataKey, $bindKey = 0) {
            $replaced = [];
            foreach ($dataKey as $postKey => $realKey) {
                if (isset($param[$postKey]))
                    $replaced[':bind_' . $bindKey . '_' . $realKey] = static::formatAttribute($realKey, $param[$postKey], $postKey);
            }
            return $replaced;
        }

        public static function getOne($param, $keys) {
            $result = false;
            $k      = '';
            foreach ($keys as $key)
                if (isset($param[$key])) {
                    $result = true;
                    $k      = $key;
                    break;
                }
            if ($result === false)
                throw new \Exception(join(',', $keys) . '必须要填写一个', Error::MSG);
            return $k;
        }

        public static function getIntParam($param, $key) {

        }

        public static function getStringParam($param, $key) {

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

