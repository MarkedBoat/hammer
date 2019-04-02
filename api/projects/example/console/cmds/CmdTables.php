<?php
    /**
     * Created by PhpStorm.
     * User: markedboat
     * Date: 2019/4/2
     * Time: 15:59
     */

    namespace projects\example\console\cmds;


    use models\CmdBase;

    class CmdTables extends CmdBase {
        public static function getClassName() {
            return __CLASS__;
        }

        public function __construct($param = []) {
            parent::init($param);

        }


        public function run() {
            $database = $this->getArgs()->getStringNotNull('database');
            echo "\n all tables in {$database}\n";
            json_encode($database, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            echo "\n";
        }
    }