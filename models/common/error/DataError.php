<?php

    namespace models\common\error;

    /**
     * 数据格式错误
     * Class DataError
     * @package models\common\error
     */
    class DataError extends CommonError {

        const NOT_EXIST        = 5000;
        const DECODE_ERROR     = 5001;
        const ENCODE_ERROR     = 5002;
        const TIMEOUT          = 5003;
        const HAS_EXIST        = 5004;
        const EMPTY_DATA       = 5005;
        const OUT_RANGE        = 5552;
        const RSA_KEY_ERROR    = 5600;
        const RSA_PUB_KEY_INIT = 5601;
        const RSA_PRI_KEY_INIT = 5602;

        public function __construct($msg, $code, $debugMsg = '', $infos = []) {
            parent::__construct($msg, $code, $debugMsg, $infos, $this);
        }
    }

    ?>