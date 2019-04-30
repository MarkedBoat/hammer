<?php

    namespace models\common\error;

    /**
     * 服务错误
     * Class ServerError
     * @package models\common\error
     */
    class ServerError extends CommonError {

        const CMNC_FAIL        = 7002;//communication 通讯失败
        const MSG_PUSH_FIAL    = 7003;//推送失败
        const NOT_EXIST        = 16400;
        const OPERATION_LOCKED = 7004;
        const REQUEST_TO_FAST  = 7005;

        public function __construct($msg, $code, $debugMsg = '', $infos = []) {
            parent::__construct($msg, $code, $debugMsg, $infos, $this);
        }
    }

