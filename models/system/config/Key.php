<?php

    namespace models\system\config;

    use models\Api;
    use models\common\db\DbModel;
    use models\common\error\ArgsError;
    use models\common\error\DataError;
    use models\common\error\DetailError;
    use models\Manger;
    use models\common\db\MCDcache;
    use models\Sys;
    use models\tool\RSA;

    class Key extends DbModel {
        const TableName = 'bftv_cfg_rsa_keys';

        public static function model() {
            return new Key(__NAMESPACE__ . '\\' . __CLASS__);
        }

        public function getConnection() {
            return Sys::db('bftv');
        }

        public function getTableName() {
            return self::TableName;
        }

        /**
         * @param $key
         * @return Key
         * @throws DetailError
         */
        public static function findByName($key) {
            $json = MCDcache::model()->get('rsa_key_cfg_' . $key);
            if ($json) {
                return Key::model()->loadValues($json);
            } else {
                $model = Key::model()->findByAttributes(['src' => $key]);
                if (empty($model))
                    throw  new DetailError('通行凭证非法', DataError::NOT_EXIST, '', [], DetailError::ACCESS_TOKEN_IS_INVALID);
                MCDcache::model()->set('rsa_key_cfg_' . $key, $model->getAttributes(), 3600);
                return $model;
            }
        }

        public function verfiyArray($signData, $sign) {
            ksort($signData);
            $signStr = join('', $signData);
            return $this->verify($signStr, $sign);
        }

        public function verify($signStr, $sign) {
            $pubKey    = $this->pub;
            $debugData = [];
            if (Manger::$isDebug)
                $debugData = [RSA::sign($signStr, $this->pri), $signStr];
            $result = RSA::verify($signStr, $sign, $pubKey);
            if (empty($result))
                throw  new DetailError('签名错误', ArgsError::SIGN, '', $debugData, DetailError::SIGN_ERROR);
        }

        public function sign($signStr) {
            $priKey = $this->pri;
            return RSA::sign($signStr, $priKey);
        }

    }

