<?php

    namespace models\common\error;

    /**
     * 命令行错误
     * Class ConsoleError
     * @package models\common\error
     */
    class ConsoleError extends CommonError {



        public function __construct($msg, $code, $debugMsg = '', $infos = []) {
            parent::__construct($msg, $code, $debugMsg, $infos, $this);
        }
    }
