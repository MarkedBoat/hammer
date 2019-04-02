<?php
    namespace models\common\db;

    //use \console\CConsole;
    //use \models\ConsoleError;

    use models\common\error\DbError;

    class MysqlPdo extends \PDO {
        private $commandText = '';
        private $cmd         = null;
        private $prefix      = '';
        private $bindData    = [];
        private $readOnly    = false;
        public  $cts         = '';
        private $cfg         = [];

        /**
         * @param $config
         * @param bool $isAlive
         * @return MysqlPdo/PDO
         * @throws DbError
         */
        public static function configDb($config, $isAlive = false) {
            $opt = array(
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                \PDO::ATTR_PERSISTENT         => false
            );
            try {
                $model = new MysqlPdo($config['connectionString'], $config['username'], $config['password'], $opt);
            } catch (\Exception $e) {
                throw new DbError('操作失败', DbError::ERROR, '数据库链接失败' . $e->getMessage() . $e->getCode());
            }

            $model->prefix   = isset($config['prefix']) ? trim($config['prefix']) : '';
            $model->readOnly = isset($config['readOnly']) ? $config['readOnly'] : false;
            $model->cts      = date('Y-m-d H:i:s', time());
            $model->cfg      = $config;
            return $model;
        }

        public function __get($attr) {
            if (isset($this->$attr))
                return $this->$attr;
            throw new \Exception('could find attr');
        }

        public function setAttr($attr, $data) {
            $this->$attr = $data;
        }

        public function setCmd($cmd, $cmdText) {
            $this->cmd         = $cmd;
            $this->commandText = $cmdText;
        }

        public function setText($commandText) {
            return new MysqlPdoCmd($this, $commandText);
        }


        public function bind($bindKey, $param) {
            $this->bindData[$bindKey] = $param;
            $this->cmd->bindValue($bindKey, $param);
            return $this;
        }

        public function bindArray($array) {
            $this->bindData = [];
            foreach ($array as $bindKey => $bindValue) {
                $this->bindData[$bindKey] = $bindValue;
                $this->cmd->bindValue($bindKey, $bindValue);
            }
            return $this;
        }

        private function __execute() {
            try {
                $this->cmd->execute();
                return $this->cmd->rowCount();
            } catch (\PDOException $e) {
                throw new DbError('操作失败', DbError::WRITE_FAIL, $e->getMessage(), [$this->commandText, $this->bindData]);
            }
        }

        /**
         * @return int
         */
        public function execute() {
            return $this->__execute();
        }

        /**
         * @return array
         */
        public function queryAll() {
            $this->__execute();
            return $this->cmd->fetchAll();
        }

        /**
         * @return array|bool
         */
        public function queryRow() {
            $this->__execute();
            if (!$this->cmd->rowCount())
                return false;
            $result = array();
            while ($row = $this->cmd->fetch()) {
                $result = $row;
                break;
            }
            return $result;
        }

        /**
         * @return bool|string
         */
        public function queryScalar() {
            $this->__execute();
            if (!$this->cmd->rowCount())
                return false;
            $this->cmd->setFetchMode(\PDO::FETCH_BOTH);
            $result = array();
            while ($row = $this->cmd->fetch()) {
                $result = $row;
                break;
            }
            return $result[0];
        }

    }



