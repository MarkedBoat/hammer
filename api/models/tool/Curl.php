<?php

    namespace models\tool;
    class Curl {
        static public function curlPostSafeUrl($url, $params) {
            $agent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11';
            $curl  = curl_init();
            // 启动一个CURL会话
            curl_setopt($curl, CURLOPT_URL, $url);
            // 要访问的地址
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            // 从证书中检查SSL加密算法是否存在
            curl_setopt($curl, CURLOPT_USERAGENT, $agent);
            // 模拟用户使用的浏览器
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            // 使用自动跳转
            curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
            // 自动设置Referer
            curl_setopt($curl, CURLOPT_POST, 1);
            // 发送一个常规的Post请求
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            // Post提交的数据包
            curl_setopt($curl, CURLOPT_TIMEOUT, 0);
            // 设置超时限制防止死循环
            curl_setopt($curl, CURLOPT_HEADER, 0);
            // 显示返回的Header区域内容
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            // 获取的信息以文件流的形式返回
            $tmpInfo = curl_exec($curl);
            // 执行操作
            if (curl_errno($curl)) {
                echo 'Errno:' . curl_error($curl);
                //捕抓异常
                return false;
            } else {
                curl_close($curl);
                // 关闭CURL会话
                return $tmpInfo;
                // 返回数据
            }
        }

        static public function curlPostUrl($url, $params, $returnInfo = false, $curlOpts = []) {
            $agent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11';
            $curl  = curl_init();
            // 启动一个CURL会话
            curl_setopt($curl, CURLOPT_URL, $url);
            // 要访问的地址
            curl_setopt($curl, CURLOPT_USERAGENT, $agent);
            // 模拟用户使用的浏览器
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            // 使用自动跳转
            curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
            // 自动设置Referer
            curl_setopt($curl, CURLOPT_POST, 1);
            // 发送一个常规的Post请求
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            // Post提交的数据包
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            // 设置超时限制防止死循环
            curl_setopt($curl, CURLOPT_HEADER, 0);
            // 显示返回的Header区域内容
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            // 获取的信息以文件流的形式返回
            if (count($curlOpts))
                curl_setopt_array($curl, $curlOpts);
            $tmpInfo = curl_exec($curl);
            // 执行操作
            if ($returnInfo === true)
                $tmpInfo = ['__content' => $tmpInfo, '__info' => curl_getinfo($curl)];
            if (curl_errno($curl)) {
                echo 'Errno:' . curl_error($curl);
                //捕抓异常
                return false;
            } else {
                curl_close($curl);
                // 关闭CURL会话
                return $tmpInfo;
                // 返回数据
            }
        }


        static public function curlGetSafeUrl($url) {
            $agent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11';
            $curl  = curl_init();
            // 启动一个CURL会话
            curl_setopt($curl, CURLOPT_URL, $url);
            // 要访问的地址
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            // 从证书中检查SSL加密算法是否存在
            curl_setopt($curl, CURLOPT_USERAGENT, $agent);
            // 模拟用户使用的浏览器
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            // 使用自动跳转
            curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
            // 自动设置Referer
            curl_setopt($curl, CURLOPT_POST, false);
            // 发送一个常规的Post请求
            //curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            // Post提交的数据包
            curl_setopt($curl, CURLOPT_TIMEOUT, 0);
            // 设置超时限制防止死循环
            curl_setopt($curl, CURLOPT_HEADER, 0);
            // 显示返回的Header区域内容
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            // 获取的信息以文件流的形式返回

            //header头里面写点玩意
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer access_token'));

            $tmpInfo = curl_exec($curl);
            // 执行操作
            if (curl_errno($curl)) {
                echo 'Errno:' . curl_error($curl);
                //捕抓异常
                return false;
            } else {
                curl_close($curl);
                // 关闭CURL会话
                return $tmpInfo;
                // 返回数据
            }
        }

        static public function get($url, $returnInfo = false) {
            $cookie_jar = "/data/spider/extFile/vo/youku.cookie";
            $ch         = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:16.0) Gecko/20100101 Firefox/16.0');
            //CURLOPT_REFERER => 'http://www.soku.com/channel/movie______1.html',
            //CURLOPT_COOKIE => 'SOKUSESSID=1333262037634D8K; JSESSIONID=abcPTV03Pn6d6boeZoLAt',
            curl_setopt($ch, CURLOPT_REFERER, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_FAILONERROR, 0);
            curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            //curl_setopt($curl, CURLOPT_FAILONERROR, 1);
            //curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_jar);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $tmpInfo = curl_exec($ch);
            // 执行操作
            if ($returnInfo)
                $tmpInfo = array(
                    '__content' => $tmpInfo,
                    '__info'    => curl_getinfo($ch)
                );

            if (curl_errno($ch)) {
                echo 'Errno:' . curl_error($ch);
                return false;
            } else {
                curl_close($ch);
                return $tmpInfo;
            }
        }

        public static function getUrls($urls) {
            $chs  = array();
            $rows = array();
            $opt  = array(
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11',
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_MAXREDIRS      => 5,
                CURLOPT_FAILONERROR    => 0,
                CURLOPT_AUTOREFERER    => 1,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HEADER         => FALSE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER     => array()
            );
            foreach ($urls as $key => $row) {
                $url = is_array($row) ? $row['__url'] : $row;
                $ch  = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_REFERER, $url);
                curl_setopt_array($ch, $opt);
                $chs[$key]  = $ch;
                $rows[$key] = is_array($row) ? $row : array('__url' => $url);

            }
            $mh = curl_multi_init();
            foreach ($chs as $i => $ch) {
                curl_multi_add_handle($mh, $ch);
            }
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);

            while ($active and $mrc == CURLM_OK) {
                //代替下面被注释的，防止 cpu使用过高
                if (curl_multi_select($mh) === -1) {
                    usleep(100);
                }
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
            foreach ($chs as $i => $ch) {
                if (curl_errno($ch)) {
                    echo 'Curl error: ' . var_export(curl_error($ch));
                }
                $info = curl_getinfo($ch);
                if ($info['http_code'] >= 400 || $info['http_code'] == 0)
                    var_export($info);
                $content               = curl_multi_getcontent($ch);
                $rows[$i]['__content'] = $content;
                $rows[$i]['__info']    = $info;
                curl_close($ch);
                curl_multi_remove_handle($mh, $ch);
            }
            curl_multi_close($mh);
            return $rows;
        }


        public static function ftp($host, $user, $password, $source_file, $destination_file) {
            $conn_id      = ftp_connect($host);
            $login_result = ftp_login($conn_id, $user, $password);
            if ((!$conn_id) || (!$login_result)) {
                return "FTP connection has failed! Attempted to connect to $host for user $user";
            }
            ftp_pasv($conn_id, true);
            $d = ftp_nb_put($conn_id, $destination_file, $source_file, FTP_BINARY);
            while ($d == FTP_MOREDATA) {
                $d = ftp_nb_continue($conn_id);
            }
            if ($d != FTP_FINISHED) {
                return "Error uploading $source_file";
            }
            ftp_close($conn_id);
            return true;
        }

        public static function uploadFile($fromUrl, $fileName) {
            if (!file_exists($fileName)) {
                //throw new \Exception('file not exist ~!' . $fileName);
                echo "\n not exist:$fileName\n";
                return false;
            }
            $file     = fopen($fileName, 'rb');
            $filesize = abs(filesize($fileName));
            fclose($file);
            $data = array('file' => class_exists('CURLFile') ? new \CURLFile(realpath($fileName)) : '@' . $fileName);
            $ch   = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fromUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $response = curl_exec($ch);
            var_dump($response);
            if ($response === false) {
                //throw new \Exception(curl_error($ch), 90);
                echo "\n f1:$fileName\n";
                return false;
            } else {
                $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if (200 !== $httpStatusCode) {
                    echo "\n f2:$fileName\n";
                    //throw new \Exception($response, $httpStatusCode);
                    return false;
                }
            }
            curl_close($ch);

            return $response;

        }

    }

    ?>