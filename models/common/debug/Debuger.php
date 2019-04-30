<?php

    namespace models\common\error;

    /**
     * 参数错误
     * Class ArgsError
     * @package models\common\error
     */
    class Debuger {
        private static $__debugData = [];

        public static function log($data, $title = '') {
            self::$__debugData[] = is_string($title) && strlen($title) ? [$title, $data] : $data;
        }

        public static function getDebugData() {
            return self::$__debugData;
        }
    }

