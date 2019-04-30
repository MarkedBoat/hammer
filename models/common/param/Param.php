<?php

    namespace models\common\param;

    use models\common\error\ArgsError;
    use models\common\error\Interruption;
    use models\Manger;


    class Param {

        public static function requireArgs($param, $args) {
            $r        = false;
            $emptyKey = '';
            foreach ($args as $k) {
                if (!isset($param[$k])) {
                    $r        = true;
                    $emptyKey = $k;
                    break;
                }
            }
            if ($r === true)
                throw new ArgsError('参数必须:' . $emptyKey, ArgsError::ERROR);
            return $r;
        }

        public static function requireArgsNotNull($param, $args) {
            $r        = false;
            $emptyKey = '';
            foreach ($args as $k) {
                if (!isset($param[$k]) || empty($param[$k])) {
                    $r        = true;
                    $emptyKey = $k;
                    break;
                }
            }
            if ($r === true)
                throw new ArgsError('参数不能为空:' . $emptyKey, ArgsError::ERROR);
            return $r;
        }

        public static function get($param, $key) {
            if (!isset($param[$key]))
                throw new ArgsError('没有参数' . $key, ArgsError::LOST);
            return $param[$key];
        }

        /*
         * 强制获取字符 串
         */
        public static function getStringImperative($param, $key, $msg = '') {
            if (!isset($param[$key]))
                throw new ArgsError($msg ? $msg : ('没有参数' . $key), ArgsError::LOST);
            if (is_string($param[$key]) || is_int($param[$key]) || is_float($param[$key])) {
                $val = strval($param[$key]);
                return $val;
            } else if (is_array($param[$key]) || is_object($param[$key])) {
                return json_encode($param[$key]);
            } else {
                throw new ArgsError($msg ? $msg : ('不能识别参数格式' . $key), ArgsError::ERROR);
            }

        }

        public static function getInt($param, $key, $msg = '', $code = 0) {
            if (!isset($param[$key]))
                throw new ArgsError($msg ? $msg : ('没有参数' . $key), $code ? $code : ArgsError::LOST, $msg, [$param]);
            return intval($param[$key]);
        }

        public static function getIntNotNull($param, $key, $msg = '', $code = 0) {
            if (!isset($param[$key]))
                throw new ArgsError($msg ? $msg : ('没有参数' . $key), $code ? $code : ArgsError::LOST);
            $tmp = intval($param[$key]);
            if (empty($tmp))
                throw new ArgsError($msg ? $msg : ('参数为不能为0:' . $key), $code ? $code : ArgsError::ERROR);
            return $tmp;
        }

        public static function tryGetInt($param, $key) {
            if (!isset($param[$key]))
                return 0;
            return intval($param[$key]);
        }

        public static function getString($param, $key, $msg = '', $code = 0) {
            if (!isset($param[$key]))
                throw new ArgsError($msg ? $msg : ('没有参数' . $key), $code ? $code : ArgsError::LOST);
            $tmp = strval($param[$key]);
            $tmp = trim($tmp);
            return $tmp;
        }

        public static function getStringNotNull($param, $key, $msg = '', $code = 0) {
            if (!isset($param[$key]))
                throw new ArgsError($msg ? $msg : ('没有参数' . $key), ArgsError::LOST, '没有参数' . $key);
            if (!is_string($param[$key]) && !is_int($param[$key]) && !is_float($param[$key]))
                throw new ArgsError($msg ? $msg : ('参数不是字符串' . $key), ArgsError::ERROR, '参数不是字符串' . $key);
            $tmp = strval($param[$key]);
            $tmp = trim($tmp);
            if (empty($tmp))
                throw new ArgsError($msg ? $msg : ('参数为不能为空' . $key), $code ? $code : ArgsError::ERROR, '参数为不能为空' . $key);
            return $tmp;
        }

        public static function tryGetString($param, $key) {
            if (!isset($param[$key]))
                return '';
            $tmp = strval($param[$key]);
            $tmp = trim($tmp);
            return $tmp;
        }

        public static function getArray($param, $key, $msg = '', $code = 0) {
            if (!isset($param[$key]))
                throw new ArgsError($msg ? $msg : ('没有参数' . $key), $code ? $code : ArgsError::LOST);
            $tmp = $param[$key];
            if (!is_array($tmp))
                throw new ArgsError($msg ? $msg : ('参数不是数组:' . $key), $code ? $code : ArgsError::ERROR);
            return $tmp;
        }

        public static function getArrayNotNull($param, $key, $msg = '', $code = 0) {
            if (!isset($param[$key]))
                throw new ArgsError($msg ? $msg : ('没有参数' . $key), $code ? $code : ArgsError::LOST);
            $tmp = $param[$key];
            if (!is_array($tmp))
                throw new ArgsError('参数不是数组:' . $key, $code ? $code : ArgsError::ERROR);
            if (empty($tmp))
                throw new ArgsError($msg ? $msg : ('参数为不能为空:' . $key), $code ? $code : ArgsError::ERROR);
            return $tmp;
        }

        public static function tryGetArray($param, $key, $macthType = true) {
            if (!isset($param[$key]))
                return [];
            $tmp = $param[$key];
            if ($macthType) {
                if (!is_array($tmp))
                    throw new ArgsError('参数不是数组:' . $key, ArgsError::ERROR);
                return $tmp;
            }
            return [];

        }

        public static function isLastVersion($newVersion, $versionStand, &$lastVersion = '') {
            $new = explode('.', $newVersion);
            $std = explode('.', $versionStand);
            foreach ($new as $k => $v) {
                if (intval($v) > intval($std[$k])) {
                    $lastVersion = $newVersion;
                    return true;
                }
                if (intval($v) < intval($std[$k])) {
                    $lastVersion = $versionStand;
                    return false;
                }
            }
            $lastVersion = $versionStand;
            return false;
        }

        /**
         * @param $versionInput string 输入版本号
         * @param $versionNew string 特性版本号
         * @param string $lastVersion string
         * @return bool true:高于等于特性版本  false:低于特性版本
         */
        public static function isNewVersion($versionInput, $versionNew, &$lastVersion = '') {
            $input = explode('.', $versionInput);
            $std   = explode('.', $versionNew);
            foreach ($input as $k => $v) {
                if ($k > 2)
                    continue;//比较前三位就行了
                if (intval($v) > intval($std[$k])) {
                    $lastVersion = $versionInput;
                    return true;
                }
                if (intval($v) < intval($std[$k])) {
                    $lastVersion = $versionNew;
                    return false;
                }
            }
            $lastVersion = $versionNew;
            if ($input[2] === $std[2]) {
                return true;//说明前三位的都是一样的，为持有新特性的第一版
            } else {
                return false;
            }
        }


        public static function jsonOutArray($title, $array) {
            Manger::addDebugInfos($array, $title);
            echo "\n//$title\n";
            echo is_array($array) || is_object($array) ? ('' . json_encode($array, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) : ("/**\n" . $array . "\n**/");
            echo "\n";
        }

        public static function outXML($title, $xmlstr) {
            $str = self::getFormatXmlStr($xmlstr);
            Manger::addDebugInfos($str, $title);
            echo str_replace("\n", "\n*", htmlspecialchars($str));

        }

        /**
         * @param $xmlstr
         * @return string
         */
        public static function getFormatXmlStr($xmlstr) {
            return self::getDomFormatStr(self::getXmlDom($xmlstr));
        }


        /**
         * //获取xml操作类
         * @param $xmlstr
         * @param string $errorMsg
         * @return \SimpleXMLElement
         * @throws ArgsError
         */
        public static function getSimpleXMLElement($xmlstr, $errorMsg = '') {
            $xml = simplexml_load_string($xmlstr);
            if (empty($xml))
                Interruption::model($errorMsg ? $errorMsg : 'xml异常', ArgsError::ERROR, '加载xml出错', [$xmlstr])->setDetailCode('XML_ERROR')->run();
            return $xml;
        }

        /**
         * @param $xmlstr
         * @param string $errorMsg
         * @return \DOMElement
         */
        public static function getXmlStringDom($xmlstr, $errorMsg = '') {
            return self::getXmlDom(self::getSimpleXMLElement($xmlstr, $errorMsg));

        }

        /**
         * @param \SimpleXMLElement $xml
         * @return \DOMElement
         */
        public static function getXmlDom(\SimpleXMLElement $xml) {
            return dom_import_simplexml($xml);
        }


        /**
         * @param \DOMElement $dom
         * @return string
         */
        public static function getDomFormatStr(\DOMElement $dom) {
            $doc               = $dom->ownerDocument;
            $doc->formatOutput = true;
            return $doc->saveXML();
        }

    }
