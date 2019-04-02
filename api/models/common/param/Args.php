<?php

    namespace models\common\param;


    use models\common\error\ArgsError;
    use models\common\error\DataError;

    class Args {
        private $dataRaw = [];

        public function __construct(array $array) {
            $this->dataRaw = $array;
        }

        public function add($key, $val) {
            $this->dataRaw[$key] = $val;
        }

        public function getInt($key, $msg = '', $code = 0) {
            return Param::getInt($this->dataRaw, $key, $msg, $code);
        }

        public function getIntNotNull($key, $msg = '', $code = 0) {
            return Param::getIntNotNull($this->dataRaw, $key, $msg, $code);
        }

        public function tryGetInt($key, $msg = '', $code = 0) {
            return Param::tryGetInt($this->dataRaw, $key, $msg, $code);
        }

        public function getString($key, $msg = '', $code = 0) {
            return Param::getString($this->dataRaw, $key, $msg, $code);
        }

        public function getStringNotNull($key, $msg = '', $code = 0) {
            return Param::getStringNotNull($this->dataRaw, $key, $msg, $code);
        }

        public function tryGetString($key, $msg = '', $code = 0) {
            return Param::tryGetString($this->dataRaw, $key, $msg);
        }

        public function getArray($key, $msg = '', $code = 0) {
            return Param::getArray($this->dataRaw, $key, $msg, $code);
        }

        public function getArrayNotNull($key, $msg = '', $code = 0) {
            return Param::getArrayNotNull($this->dataRaw, $key, $msg, $code);
        }

        public function tryGetArray($key, $matchType = true) {
            return Param::tryGetArray($this->dataRaw, $key, $matchType);
        }

        public function getDataRaw() {
            return $this->dataRaw;
        }

        /**
         * 获取版本
         * @param int $v1
         * @param int $v2
         * @param int $v3
         * @param string $key
         * @param bool $necessary
         * @return bool
         * @throws ArgsError
         */
        public function getVersion(&$v1 = 0, &$v2 = 0, &$v3 = 0, $key = 'version', $necessary = false) {
            $version = $necessary ? Param::getStringNotNull($this->dataRaw, $key) : Param::tryGetString($this->dataRaw, $key);
            if (empty($version))
                return false;
            $vers = explode('.', $version);
            if (count($vers) < 3) {
                if ($necessary === false) {
                    return false;
                } else {
                    throw  new ArgsError('信息错误', ArgsError::ERROR);
                }
            }
            $v1 = $vers[0];
            $v2 = $vers[1];
            $v3 = $vers[2];
            return true;
        }

        public static function isTimeout($timestamp, $range, $now = 0) {
            if ($now === 0)
                $now = time();
            if ($timestamp < ($now - $range))
                throw  new ArgsError('参数超时', ArgsError::TIME_OUT, '', $now);

        }

        public static function getQueryParam($query) {
            $array = json_decode($query, true);
            if (!is_array($array))
                throw  new DataError('query解析失败', DataError::DECODE_ERROR);
            return new Args($array);
        }

    }

