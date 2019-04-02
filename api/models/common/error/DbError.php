<?php
    namespace models\common\error;

    /**
     * 数据库错误
     * Class DbError
     * @package models\common\error
     */
    class DbError extends CommonError {

        const ERROR        = 6000;
        const READ_ONLY    = 6001;
        const EXCUTE_ERROR = 6002;
        const WRITE_FAIL   = 6003;
        const BIND_ERROR   = 6004;

        public function __construct($msg, $code, $debugMsg = '', $infos = []) {
            parent::__construct($msg, $code, $debugMsg, $infos, $this);
        }
    }

    ?>