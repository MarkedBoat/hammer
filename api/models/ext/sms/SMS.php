<?php
    /**
     * Created by PhpStorm.
     * User: dingdayu
     * Date: 2017/11/24
     * Time: 10:06
     */

    namespace models\ext\sms;
    class SMS {
        /**
         * @var string 业务表示，已开通账号名称
         */
        private static $AXJ_NAME = "";

        /**
         * @var string 账号密码
         */
        private static $AXJ_PASS = "";

        /**
         * @var string 发送URL
         */
        public static $AXJ_SEND_URL = "http://124.251.7.232:9007/axj_http_server/sms";

        /**
         * @var = 单例
         */
        private static $_instance;

        public static function getInstance() {
            if (!(self::$_instance instanceof self)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * @param array $mobiles 手机号，支持数组和字符串
         * @param string $content 短信内容
         * @param string $sendTime 发送时间：YYYYMMDDHHMMSS格式,为空表示立即发送
         * @param string $subId 业务通道号码
         * @return array                    [批次id, 状态码]
         *                                          状态码：00:成功,01:参数异常,02:手机号异常,03:发送时间异常,04:短信内容异常
         */
        public function send($mobiles = [], $content = '', $sendTime = '', $subId = '') {
            is_array($mobiles) && $mobiles = implode(',', $mobiles);

            $parameter = [
                'name'     => self::$AXJ_NAME,
                'pass'     => self::$AXJ_PASS,
                'subid'    => $subId,
                'mobiles'  => trim($mobiles),
                'content'  => $content,
                'SendTime' => $sendTime
            ];

            $ret = self::curl(self::$AXJ_SEND_URL, $parameter);
            return explode(',', trim($ret));
        }

        /**
         * @param array $mobiles 手机号，支持数组和字符串
         * @param string $content 短信内容
         * @param string $sendTime 发送时间：YYYYMMDDHHMMSS格式,为空表示立即发送
         * @param string $subId 业务通道号码
         * @return array                    [批次id, 状态码]
         */
        public function sendAdvance($mobiles = [], $content = '', $sendTime = '', $subId = '', &$resultSMS = []) {
            is_array($mobiles) && $mobiles = implode(',', $mobiles);
            $parameter = [
                'name'     => self::$AXJ_NAME,
                'pass'     => self::$AXJ_PASS,
                'subid'    => $subId,
                'mobiles'  => trim($mobiles),
                'content'  => $content,
                'SendTime' => $sendTime
            ];
            $ret       = self::curl(self::$AXJ_SEND_URL, $parameter);
            $reuslt    = explode(',', trim($ret));
            $stas      = [
                '00' => 'ok',
                '01' => 'param_error',
                '02' => 'tel_error',
                '03' => 'time_error',
                '04' => 'content_error'
            ];
            $resultSMS = $reuslt;
            $reuslt[1] = isset($stas[$reuslt[1]]) ? $stas[$reuslt[1]] : $reuslt[1];
            return $reuslt;
        }

        /**
         * 发起网络请求
         *
         * @param string $url
         * @param array $data
         * @param array $header
         * @param string $request
         * @return mixed
         */
        public static function curl($url = '', $data = [], $header = [], $request = "POST") {
            $data = http_build_query($data);

            $ch = curl_init();
            if (strtolower($request) == 'get') {
                $url .= $data;
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // 不校验ssl证书，因为无法保证服务器上正确配置
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            $result = curl_exec($ch);
            //var_dump(curl_error($ch));
            curl_close($ch);
            return $result;
        }

        /**
         * 禁止克隆
         */
        private function __clone() {
        }
    }