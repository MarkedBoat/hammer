<?php
    /**
     * Created by PhpStorm.
     * User: markedboat
     * Date: 2019/4/2
     * Time: 15:57
     */

    namespace projects\example\api\apis;


    use models\common\api\ApiBase;
    use models\Sys;

    class ApiTables extends ApiBase {
        public static function getClassName() {
            return __CLASS__;
        }

        public function __construct($param) {
            parent::init($param);

        }

        public function run() {
            return 'ok';
            $database = $this->getArgs()->getStringNotNull('database');
            $ip       = Sys::getRemoteIp();
            return [
                'your ip' => $ip,
                'tables'  => Sys::db($database)->setText('show tables;')->queryAll()
            ];
        }


    }