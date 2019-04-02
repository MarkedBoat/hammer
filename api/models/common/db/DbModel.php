<?php

    namespace models\common\db;


    use models\Api;
    use models\common\error\Interruption;
    use models\Manger;
    use models\Sys;

    class DbModel {
        public         $isAlive           = false;
        private        $pk                = null;
        private static $__columnInfo      = [];//表的字段结构
        private        $__attributes      = array();
        private        $className         = '';
        private        $__hasInitAttrs    = false;
        private        $__forceInsert     = false;
        private        $__onDuplicateSets = [];
        public         $attributes        = array();
        public         $bindArray         = array();
        public         $conditions        = array();
        public         $order             = '';
        public         $page              = array(
            'pageNo' => 0,
            'size'   => 100,
        );

        private $__dbCnn = null;

        private $__conditions = [];

        /**
         * @return $this
         */
        public static function model() {
            $calledClass = get_called_class();
            return new $calledClass($calledClass);
        }

        public function __construct($className = __CLASS__) {
            $this->className = $className;
            $this->afterConstruct();
        }

        public function __set($key, $val) {
            $this->initAttributes();
            if (isset($this->getFileds()[$key]))
                $key = $this->getFileds()[$key];
            if (isset($this->__attributes[$key])) {
                if ((is_null($this->__attributes[$key]['value']) && !is_null($val)) || $this->__attributes[$key]['value'] != $val)
                    $this->__attributes[$key]['isChange'] = true;
                $this->__attributes[$key]['value'] = $val;
                $this->attributes[$key]            = $val;
            } else {
                Manger::addDebugInfos([$this, $this->getFileds()], 'getFileds');
                throw new \Exception($this->className . '.' . $key . ' not exist(set');
            }
        }

        public function __get($key) {
            $this->initAttributes();
            if (isset($this->getFileds()[$key]))
                $key = $this->getFileds()[$key];
            if (isset($this->__attributes[$key])) {
                return $this->__attributes[$key]['value'];
            } else {
                Manger::addDebugInfos([$this, $this->getFileds()], 'getFileds');
                throw new \Exception($this->className . '.' . $key . ' not exist(get');
            }
        }

        /**
         * 字段映射，有些原数据库字段起的不尽人意，或者有歧义，又不方便修改
         * @return array
         */
        public function getFileds() {
            return [];
        }

        public function getConnection() {
            return Sys::db('bftv');
        }

        public function getReadDb() {
            return Sys::db('bftv');
        }

        public function getWriteDb() {
            return Sys::db('bftv');
        }

        /**
         * @return string
         */
        public function getTableName() {
            return '';
        }

        public function getAttributes() {
            return $this->attributes;
        }

        public function initAttributes() {
            if ($this->__hasInitAttrs)
                return false;
            $tableName = $this->getTableName();
            if (!isset(self::$__columnInfo[$tableName])) {
                self::$__columnInfo[$tableName] = ['columns' => [], 'pk' => ''];
                $result                         = $this->getConnection()->setText('show full columns from ' . $this->getTableName() . ';')->queryAll();
                foreach ($result as $row) {
                    $row['value']                                             = null;
                    $row['isChange']                                          = false;
                    self::$__columnInfo[$tableName]['columns'][$row['Field']] = $row;
                    if (is_null($this->pk) && strstr($row['Key'], 'PRI'))
                        self::$__columnInfo[$tableName]['pk'] = $row['Field'];
                }
            }
            $this->__attributes   = self::$__columnInfo[$tableName]['columns'];
            $this->pk             = self::$__columnInfo[$tableName]['pk'];
            $this->__hasInitAttrs = true;
        }

        /**
         * 如果有 $this->pk，一般被视为update,但是有整体插入的设置，所以加个强制插入标识
         */
        public function setForceInsert() {
            $this->__forceInsert = true;
        }

        public function setConditions($param = []) {
            $this->__conditions = $param;
        }

        public function compare($attr, $isEq = false) {
            $this->initAttributes();
            $value = $this->$attr;
            if (is_null($value))
                return;
            $this->conditions[$attr]                = $isEq ? "`$attr`=:$attr" : "`$attr` like :$attr";
            $this->__attributes[$attr]['bindValue'] = $isEq ? $value : "%$value%";
            $this->bindArray[':' . $attr]           = $isEq ? $value : "%$value%";
        }

        public function in($key, $range) {
            if (count($range))
                $this->conditions[] = '`' . $key . '` in ("' . join('","', $range) . '")';
        }

        public function notIn($key, $range) {
            if (count($range))
                $this->conditions[] = '`' . $key . '` not in ("' . join('","', $range) . '")';
        }

        public function addCondition($str) {
            $this->conditions[] = $str;
        }

        public function order($order) {
            if ($order)
                $this->order = ' ' . $order . ' ';
        }

        public function page($pageNo = 1, $size = 20) {
            $this->page = array(
                'pageNo' => $pageNo,
                'start'  => intval($pageNo - 1) * $size,
                'length' => $size,
                'size'   => $size,
            );
        }

        public function count() {
            return $this->getConnection()->setText('SELECT count(*) FROM ' . $this->getTableName() . (count($this->conditions) ? ' where ' . join(' and ', $this->conditions) : '') . ' LIMIT 1')->bindArray($this->bindArray)->queryScalar();
        }

        public function find() {
            $this->page['total']     = $this->count();
            $this->page['pageTotal'] = ceil($this->page['total'] / intval($this->page['size']));
            return $this->getConnection()->setText('SELECT * FROM ' . $this->getTableName() . (count($this->conditions) ? ' where ' . join(' and ', $this->conditions) : '') . $this->order . ' LIMIT ' . intval($this->page['start']) . ',' . intval($this->page['length']))->bindArray($this->bindArray)->queryAll();
        }

        /**
         * @param $data
         * @return $this/DbModel
         */
        private function __loadValues($data) {
            $this->initAttributes();
            foreach ($this->__attributes as $key => $attribute) {
                if (isset($data[$key])) {
                    $this->__attributes[$key]['value'] = $data[$key];
                    $this->attributes[$key]            = $data[$key];
                }
            }
            $this->afterLoadValues();
            return $this;
        }

        public function afterLoadValues() {

        }

        public function afterConstruct() {

        }

        /**
         * @param $data
         * @return $this
         */
        public function loadValues($data) {
            $this->initAttributes();
            foreach ($this->__attributes as $key => $attribute) {
                if (isset($data[$key]))
                    $this->$key = $data[$key];
            }
            $this->afterLoadValues();
            return $this;
        }

        /**
         * @param $pk
         * @return $this|bool
         */
        public function findByPk($pk) {
            $this->initAttributes();
            $row = $this->getConnection()->setText('SELECT * FROM ' . $this->getTableName() . ' WHERE `' . $this->pk . '`=:' . $this->pk . ';')->bind(':' . $this->pk, $pk)->queryRow();
            if ($row) {
                $this->__loadValues($row);
                return $this;
            } else {
                return false;
            }
        }

        /**
         * @param $pk
         * @param $msg
         * @param $code
         * @return $this|DbModel
         */
        public function findByPkNotNull($pk, $msg, $code) {
            $result = $this->findByPk($pk);
            if ($result === false) {
                Interruption::model($msg, $code)->setDebugData([$this->getTableName(), $pk])->run();
            }
            return $result;
        }

        /**
         * @param $pk
         * @return $this|DbModel
         */
        public function tryFindByPk($pk) {
            $result = $this->findByPk($pk);
            if ($result === false) {
                return static::model();
            }
            return $result;
        }

        /**
         * @param $attributes
         * @return $this[]
         */
        public function findAllByAttributes($attributes) {
            $this->initAttributes();
            $sqls = array();
            $bind = array();
            foreach ($attributes as $key => $val) {
                if (!isset($this->__attributes[$key]))
                    continue;
                $sqls[':' . $key] = "`$key`=:$key";
                $bind[':' . $key] = $val;
            }
            $table = $this->getConnection()->setText('SELECT * FROM ' . $this->getTableName() . ' WHERE ' . join(' and ', $sqls) . ';')->bindArray($bind)->queryAll();
            $list  = array();
            if ($table) {
                foreach ($table as $row) {
                    $tmp    = call_user_func_array(array(
                        get_class($this),
                        'model'
                    ), array());
                    $list[] = $tmp->__loadValues($row);
                }
                return $list;
            } else {
                return array();
            }
        }

        /**
         * @param $attributes
         * @return $this
         */
        public function findByAttributes($attributes) {
            $this->initAttributes();
            $sqls = array();
            $bind = array();
            foreach ($attributes as $key => $val) {
                if (!isset($this->__attributes[$key]))
                    continue;
                $sqls[':' . $key] = "`$key`=:$key";
                $bind[':' . $key] = $val;
            }
            $row = $this->getConnection()->setText('SELECT * FROM ' . $this->getTableName() . ' WHERE ' . join(' and ', $sqls) . ' LIMIT 1;')->bindArray($bind)->queryRow();
            if ($row) {
                return $this->__loadValues($row);
            } else {
                return false;
            }
        }

        public function findAll() {
            $table = $this->getConnection()->setText('SELECT * FROM ' . $this->getTableName())->queryAll();
            $list  = array();
            if ($table) {
                foreach ($table as $row) {
                    $tmp    = call_user_func_array(array(
                        get_class($this),
                        'model'
                    ), array());
                    $list[] = $tmp->__loadValues($row);
                }
                return $list;
            } else {
                return array();
            }
        }

        public function save() {
            $this->initAttributes();
            $sqls = array();
            $bind = array();
            foreach ($this->__attributes as $key => $attribute) {
                if (!$attribute['isChange'])
                    continue;
                $sqls[':' . $key]                     = "`$key`=:$key";
                $bind[':' . $key]                     = $attribute['value'];
                $this->__attributes[$key]['isChange'] = false;
            }
            if (isset($this->__attributes['utime']))
                $sqls[] = 'utime=unix_timestamp()';
            if (count($bind)) {
                if (!is_null($this->pk) && !$this->__attributes[$this->pk]['isChange'] && $this->__attributes[$this->pk]['value']) {
                    $bind[':' . $this->pk] = $this->__attributes[$this->pk]['value'];
                    return $this->getConnection()->setText('update ' . $this->getTableName() . ' set ' . join(',', $sqls) . ' where `' . $this->pk . '`=:' . $this->pk . ';')->bindArray($bind)->execute();
                } else {
                    $cmd      = $this->getConnection()->setText('INSERT  INTO ' . $this->getTableName() . ' SET ' . join(',', $sqls) . ';')->bindArray($bind);
                    $rowCount = $cmd->execute();
                    $id = $cmd->lastInsertId();
                    if (empty($id))
                        return false;
                    $pk        = $this->pk;
                    $this->$pk = $id;;
                    return $rowCount;
                }
            }
            return 0;
        }

        public function setOnDuplicateSet(array $keyVals) {
            $this->__onDuplicateSets = $keyVals;
        }

        public function trySave() {
            $this->initAttributes();
            $sqls       = array();
            $bind       = array();
            $isPkChange = false;
            foreach ($this->__attributes as $key => $attribute) {
                if (!$attribute['isChange'])
                    continue;
                $sqls[':' . $key]                     = "`$key`=:$key";
                $bind[':' . $key]                     = $attribute['value'];
                $this->__attributes[$key]['isChange'] = false;
                if ($key == $this->pk)
                    $isPkChange = true;
            }
            if (isset($this->__attributes['utime']))
                $sqls[] = 'utime=unix_timestamp()';
            if (count($bind)) {
                if ($isPkChange === false && !is_null($this->pk) && !$this->__attributes[$this->pk]['isChange'] && $this->__attributes[$this->pk]['value'] && $this->__forceInsert === false) {
                    $bind[':' . $this->pk] = $this->__attributes[$this->pk]['value'];
                    try {
                        $sql = 'update ' . $this->getTableName() . ' set ' . join(',', $sqls) . ' where `' . $this->pk . '`=:' . $this->pk . ';';
                        if (count($this->__conditions)) {
                            $conds = [];
                            foreach ($this->__conditions as $ci => $cv) {
                                if (isset($this->__attributes[$ci])) {
                                    $conds[':c_' . $ci] = "`$ci`=:c_$ci";
                                    $bind[':c_' . $ci]  = $cv;
                                } else {
                                    $conds[] = $cv;
                                }
                            }
                            $sql                = str_replace(';', ' and ' . join(' and ', $conds) . ';', $sql);
                            $this->__conditions = [];
                        }
                        return $this->getConnection()->setText($sql)->bindArray($bind)->execute();
                    } catch (\Exception $e) {
                        return false;
                    }
                } else {
                    $sql = 'INSERT IGNORE  INTO ' . $this->getTableName() . ' SET ' . join(',', $sqls) . ' ;';

                    if (count($this->__onDuplicateSets)) {
                        $sets = [];
                        foreach ($this->__onDuplicateSets as $si => $sv) {
                            $sets[':s_' . $si] = "`$si`=:s_$si";
                            $bind[':s_' . $si] = $sv;
                        }
                        $sql                     = str_replace(';', 'on duplicate key update ' . join(',', $sets) . ';', $sql);
                        $this->__onDuplicateSets = [];
                    }
                    $cmd = $this->getConnection()->setText($sql)->bindArray($bind);
                    $cmd->execute();
                    $id = $cmd->lastInsertId();
                    if (empty($id))
                        return false;
                    $pk                                   = $this->pk;
                    $this->$pk                            = $id;
                    $this->__attributes['id']['isChange'] = false;
                    return true;
                }
            }

        }

        /**
         * 查询表详情详细信息
         * @param string $field
         * @param array $where
         * @param bool $isSlave
         * @return array|bool
         * @throws \models\common\error\ArgsError
         * @throws \models\common\error\DbError
         */
        public function simpleSelect($field = "*", $where = array(), $isSlave = false) {
            if (empty($where)) {
                return false;
            }
            $bind    = [];
            $bindVal = [];
            foreach ($where as $key => $value) {
                if (!empty($value)) {
                    $bindVal[":$key"] = $value;
                    $bind[]           = "$key=:$key";
                }
            }
            if (empty($bind)) {
                return false;
            }
            $whereStr = implode(" and ", $bind);
            $sql      = "select {$field} from " . $this->getTableName() . " where {$whereStr}";
            if ($isSlave) {
                return $this->getReadDb()->setText($sql)->bindArray($bindVal)->queryRow();
            } else {
                return $this->getWriteDb()->setText($sql)->bindArray($bindVal)->queryRow();
            }
        }

        /**
         * 设置utf8mb4链接
         */
        public static function setUtf8mb4() {
            self::model()->getConnection()->setText('set names utf8mb4;')->execute();
        }
    }

