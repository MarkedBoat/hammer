<?php

    namespace models\tool;
    class Str {

        public static function getDict() {
            $str = '~,!,@,#,$,%,^,&,*,(,),_,+,|,`,-,=,\,;,:,",<,>,.,?,/,a,A,b,B,c,C,d,D,e,E,f,F,g,G,h,H,i,I,j,J,k,K,l,L,m,M,n,N,o,O,p,P,q,Q,r,R,s,S,t,T,u,U,v,V,w,W,x,X,y,Y,z,Z,1,2,3,4,5,6,7,8,9,0';
            return array_unique(explode(',', $str));
        }

        public static function dictEncode($str) {
            $str       = $str . md5($str . strlen($str));
            $dict      = self::getDict();
            $dictCount = count($dict);
            $str       = base64_encode($str);
            $strLen    = strlen($str);
            $diff      = $dictCount - $strLen;
            $diff      = ($diff !== 0) ? ($diff > 0 ? $diff : -$diff) : 7;
            $array     = array();
            for ($i = 0; $i < $strLen; $i++) {
                $char = $str[$i];
                if (in_array($char, $dict)) {
                    $baseNum   = intval(array_search($char, $dict));
                    $rightMove = $baseNum + (($i + $diff) * $diff);
                    $dictKey   = isset($dict[$rightMove]) ? $rightMove : ($rightMove % $dictCount);
                    $array[$i] = $dict[$dictKey];
                } else {
                    $array[$i] = $char;
                }
            }
            return base64_encode(join('', $array));
        }

        public static function dictDecode($str) {
            $str       = base64_decode($str);
            $dict      = self::getDict();
            $dictCount = count($dict);
            $strLen    = strlen($str);
            $diff      = $dictCount - $strLen;
            $diff      = ($diff !== 0) ? ($diff > 0 ? $diff : -$diff) : 7;
            $array     = array();
            for ($i = 0; $i < $strLen; $i++) {
                $charCoded = $str[$i];
                if (in_array($charCoded, $dict)) {
                    $dictKey       = intval(array_search($charCoded, $dict));
                    $rightMoveBase = ($i + $diff) * $diff;
                    if ($dictKey > $rightMoveBase) {
                        $dictKey   = $dictKey - $rightMoveBase;
                        $array[$i] = $dict[$dictKey];
                    } else {
                        $rightMove = $rightMoveBase;
                        for ($n = 1; $n < $dictCount; $n++) {
                            $rightMove = $rightMove + 1;
                            if ($rightMove % $dictCount == $dictKey) {
                                $baseNum   = $rightMove - $rightMoveBase;
                                $array[$i] = $dict[$baseNum];
                                break;
                            }
                        }
                    }
                } else {
                    $array[$i] = $dict[$i];
                }
            }
            $str  = base64_decode(join('', $array));
            $hash = substr($str, -32);
            $str2 = substr_replace($str, '', -32);
            return (md5($str2 . strlen($str2)) === $hash) ? $str2 : false;
        }

    }

