<?php

    namespace models\tool;

    use models\common\error\ArgsError;
    use models\error\DataError;
    use models\error\InfoError;


    class Arr {
        private $version  = '1.0';
        private $encoding = 'UTF-8';
        private $root     = 'root';
        private $__xml    = null;
        private $__dom    = null;


        public function toXml($data, $rootNode = null, $keyName = '') {
            if (is_null($rootNode)) {
                $this->__dom = new \DomDocument('1.0', 'UTF-8');
                $rootNode    = $this->__dom;
            }
            foreach ($data as $key => $value) {
                if ($keyName) {
                    $node = $this->__dom->createElement($keyName);
                    $this->toXml($value, $node);
                } else {
                    if (is_array($value) && count($value) == 0) {
                        $node = $this->__dom->createElement($key, '');
                    } else {
                        if (is_array($value) || is_object($data)) {
                            if (isset($value[0])) {
                                $node = $this->__dom->createElement($key);
                                $this->toXml($value, $node, 'item');
                            } else {
                                $newKey = $keyName ? $keyName : $key;
                                $node   = $this->__dom->createElement($newKey);
                                $this->toXml($value, $node);
                            }
                        } else {
                           // $node = $this->__dom->createElement($keyName ? $keyName : $key, "<![CDATA[{$value}]]>");
                            $node = $this->__dom->createElement($keyName ? $keyName : $key, $value);
                        }
                    }

                }
                $rootNode->appendChild($node);

            }

            return $rootNode;
        }

        public function getXml() {
            return $this->__dom->saveXML();
        }


    }
