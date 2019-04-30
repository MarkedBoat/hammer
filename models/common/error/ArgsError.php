<?php

    namespace models\common\error;

    /**
     * 参数错误
     * Class ArgsError
     * @package models\common\error
     */
    class ArgsError extends CommonError {

        const LOST      = 4000;
        const ERROR     = 4001;
        const NOT_EXIST = 4002;
        const SIGN      = 4003;
        const TIME_OUT  = 4502;

        public function __construct($msg, $code, $debugMsg = '', $infos = []) {
            parent::__construct($msg, $code, $debugMsg, $infos, $this);
        }
    }

    ?>