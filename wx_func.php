<?php
	class Tool {
		
		static function getContent($obj) {
			return ''.$obj->Content.'';
		}

		/**
		 * 用户发送过来的信息 截取等
		 * @param  [type] $num [description]
		 * @param  [type] $str [description]
		 * @param  string $val [description]
		 * @return [type]      [description]
		 */
		static function judge($num,$str,$val='') {
			switch ($num) {
				case 0:
					return strtolower($str) == $val;
					break;
				case 1:
					return mb_substr($str,0,2,'UTF-8') == $val;
					break;
				case 2:
					return strtolower($str);
					break;
				case 3:
					return mb_substr($str,2,mb_strlen($str)-2,'UTF-8');
					break;
				
				default:
					
					break;
			}
		}

		/**
		 * cURL GET
		 * @param  [type] $url [description]
		 * @return [type]      [description]
		 */
		static function curlGet($url){
			$ch = curl_init();
			$header = "Accept-Charset: utf-8";
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$temp = curl_exec($ch);
			curl_close($ch);
			return json_decode($temp);
		}
		/**
		 * cURL POST
		 * @param  [type] $url  [description]
		 * @param  [type] $data [description]
		 * @return [type]       [description]
		 */
		static function curlPost($url,$data) {
			$curl = curl_init(); // 启动一个CURL会话
		    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
		    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
		    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
		    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
		    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
		    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
		    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
		    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
		    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
		    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
		    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
		    $tmpInfo = curl_exec($curl); // 执行操作
		    if (curl_errno($curl)) {
		       echo 'Errno'.curl_error($curl);//捕抓异常
		    }
		    curl_close($curl); // 关闭CURL会话
		    return $tmpInfo; // 返回数据
		}
		/**
		 * 验证/获取 普通access_token是否过期
		 * @param  [type] $appID     [description]
		 * @param  [type] $appSecret [description]
		 * @return [type] access_token [description]
		 */
		static function verifyAccessToken($appID,$appSecret) {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $rest = $redis->get('token');
            if (!$rest) {
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appID."&secret=".$appSecret;
                $rest = Tool::curlGet($url);
                $redis->set('token',$rest->access_token);
                $redis->expire('token',$rest->expires_in-100);
                return $rest->access_token;
            }
            return $rest;
	    }

	}
?>
