<?php
    namespace models\common\error;

    class ProcessError extends CommonError {


        const RUN_ERROR = 7000;
        const SYNC_FAIL = 7001;
        const CMNC_FAIL = 7002;//communication 通讯失败


        public function __construct($msg, $code, $debugMsg = '', $infos = []) {
            parent::__construct($msg, $code, $debugMsg, $infos, $this);
        }
    }

