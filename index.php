<?php
require_once(dirname(__FILE__).'/city_id.php');
require_once(dirname(__FILE__).'/wx_func.php');
require_once(dirname(__FILE__).'/bdfy_api.php');
/**
 * 微信类
 */
class WeChat {

	private $token;
	private $appID;
	private $appSecret;
	private $token_url;
	private $access_token;	//普通的accesst_token，不是网页授权的

	/**
	 * 构造函数
	 * Token、AppID、AppSecret、access_token_URL
	 */
	function __construct() {
		$this->token = "weixinapps";
		$this->appID = "wx5ed9e5bc124d5f9c";
		$this->appSecret = "4962db15e726f9347c8083207a09155a";
		$this->token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appID."&secret=".$this->appSecret;
		$this->access_token = Tool::verifyAccessToken($this->appID,$this->appSecret);
	}
	/**
	 * 和微信服务器交互验证
	 */
	public function verify(){
			$timestamp= $_GET['timestamp'];
			$nonce    = $_GET['nonce'];
			$echostr  = $_GET['echostr'];
			$signature= $_GET['signature'];
			$array    = array();
			$array    = array($timestamp,$nonce,$this->token);
			sort($array);
			//2将排序的三个参数拼接用shal加密
			$tmpstr   = implode('',$array);//join拼接
			$tmpstr   = sha1($tmpstr);
			//3将加密字符串与signature对比,判断请求是否来自微信
			if($tmpstr == $signature && $echostr){
				//第一次接入微信api接口时
				$this->setCustomMenu();
				return $echostr;
			} else{
				return $this->reponseMsg();
			}
		}

		/**
		 * 微信服务器发送过来的XML
		 * event 事件
		 * 		- subscribe 关注
		 * 		- location 	位置信息 	
		 * 		- click 	点击事件
		 * 				- v1001_weather 	天气
		 * 				- v1001_translate 	翻译
		 * 				...
		 * 
		 */
		private function reponseMsg(){
			//1获取到微信推送过来的post数据（xml格式）
			$postArr = $GLOBALS['HTTP_RAW_POST_DATA'];
			$postObj = simplexml_load_string($postArr);
			//判断数据包是否是订阅事件推送
			if(Tool::judge(0,$postObj->MsgType,'event')){
			//如果是关注subscribe事件
				if(Tool::judge(0,$postObj->Event,'subscribe')){
				//回复用户消息
					$content = "❤锦瑟无端五十弦❤\n❤一弦一柱思华年❤\n
❤庄生晓梦迷蝴蝶❤\n❤望帝春心托杜鹃❤\n
❤沧海月明珠有泪❤\n❤蓝田日暖玉生烟❤\n
❤此情可待成追忆❤\n❤只是当时已惘然❤\n";
					return$this->sendText($postObj,$content);
				} elseif (Tool::judge(0,$postObj->Event,'location')) {
					$content = "当前所在位置纬度：".$postObj->Latitude."经度：".$postObj->Longitude;
					return $this->sendText($postObj,$content);
				} elseif (Tool::judge(0,$postObj->Event,'click')) {
					$status = Tool::judge(2,$postObj->EventKey);
					switch ($status) {
						case 'v1001_weather':
							$content = "亲！要想查询天气请请输入城市名称";
							return $this->sendText($postObj,$content);
							break;
						case 'v1001_translate':
							$content = "亲！想要翻译请输入 翻译+要翻译的内容\n例：\n翻译apple";
							return $this->sendText($postObj,$content);
							break;
						
						default:
							$content = "what???";
							return $this->sendText($postObj,$content);
							break;
					}
				}
			} else {
				$city = city_id();
				$text = Tool::getContent($postObj->Content);
				if (array_key_exists(strtolower($postObj->Content),$city)) {
					$city_id= $city[$text];
					$url    = "http://www.weather.com.cn/data/cityinfo/".$city_id.".html";
					$obj    = Tool::curlget($url);
					$content= "城市：".$obj->weatherinfo->city."\n最低温度：".$obj->weatherinfo->temp1."\n最高温度：".$obj->weatherinfo->temp2."\n天气：".$obj->weatherinfo->weather."\n最后更新时间：".$obj->weatherinfo->ptime."\n";
					return $this->sendText($postObj,$content);
				} elseif (Tool::judge(1,$postObj->Content,'翻译')) {

					$obj = translate(Tool::judge(3,$postObj->Content.'',''), 'en', 'zh');
					return $this->sendText($postObj,$obj['trans_result'][0]['dst']);
				} else {
					$content = '人家都不晓得你在说啥子咧！😒';
					return $this->sendText($postObj,$content);
				}
			} 
		}
		/**
		 * 发送消息
		 * @param  [type] $obj  [description]
		 * @param  [type] $text [description]
		 * @return [type]       [description]
		 */
		private function sendText($obj,$text) {
			$toUser = $obj->FromUserName;
	        $fromUser=$obj->ToUserName;
	        $time =time();
	        $msgType = 'text';
	        $template ="<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType><Content><![CDATA[%s]]></Content></xml>";
	        $info  = sprintf($template ,$toUser,$fromUser,$time,$msgType,$text);
	        return $info;
		}
		//用户授权获取code
		public function UserAuthorizeOne() {
			//授权成功回调
			$back_url = "http://www.dppblog.com/wx/index.php";
			$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->appID."&redirect_uri=".urlencode($back_url)."&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
			header("Location: ".$url);
		}
		//获取用户基本信息
		public function UserAuthorizeTwo() {
			//通过appID、appSecret、code获取access_token和open_id
			$code = $_GET['code'];
	        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->appID."&secret=".$this->appSecret."&code=".$code."&grant_type=authorization_code ";
	        $obj = Tool::curlGet($url);
	        $access_token = $obj->access_token;
	        $open_id = $obj->openid;
	        //通过access_token、open_id获取用户信息
	        $user_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$open_id."&lang=zh_CN";
	        $rest = Tool::curlGet($user_url);
			header("Location:http://www.dppblog.com/wx/map.php"); 
		}
		//创建自定义菜单
		private function setCustomMenu() {
			$data = '{
			   "button":[
			   {  
			        "type":"click",
			        "name":"天气",
			        "key":"V1001_WEATHER"
			    },{ 
			        "type":"click",
			        "name":"翻译",
			        "key":"V1001_TRANSLATE"
			    },{ 
			        "type":"view",
			        "name":"订单",
			        "url":"http://www.dppblog.com/wx/index.php?yanzhen=ss"
			    }
			    ]
			}';
		 	$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$this->access_token;
		 	return Tool::curlPost($url,$data);
		}
}
$wx = new Wechat();

if (array_key_exists('yanzhen',$_GET)) {
	var_dump('ssss');
	$wx->UserAuthorizeOne();
} elseif (array_key_exists('state',$_GET)) {
	$wx->UserAuthorizeTwo();
} else {
	echo $wx->verify();
}

