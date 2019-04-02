<?php
    /**
     * Created by PhpStorm.
     * User: markedboat
     * Date: 2019/4/2
     * Time: 15:57
     */

    namespace projects\example\api\actions;


    use models\Action;
    use models\Sys;

    class ActionTables extends Action {
        public static function getClassName() {
            return __CLASS__;
        }

        public function __construct($param) {
            parent::init($param);

        }

        public function run() {
            $database = $this->getArgs()->getStringNotNull('database');
            $ip       = Sys::getRemoteIp();
            return [
                'your ip' => $ip,
                'tables'  => Sys::db($database)->setText('show tables;')->queryAll()
            ];
        }

        public function getCacheId() {

        }

        public function setCache() {

        }

        public function delCache() {

        }
    }