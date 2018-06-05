<?php
/**
 * Date: 2018/6/5
 * Time: 16:57
 */
namespace CharlesEasy\Http;

class Http {

	public function httpPost($url,$param,$verify='',$header=''){
		$res = $this->httpRequestOnce($url,$param,'post',$verify,$header);
		if($res && $res["result"] === false){
			$res = $this->httpRequestOnce($url,$param,'post',$verify,$header);
		}
		return $res;
	}

	public function httpGet($url,$param){
		$res = $this->httpRequestOnce($url,$param,'get',false);
		if($res && $res["result"] === false){
			$res = $this->httpRequestOnce($url,$param,'get',false);
		}
		return $res;
	}


	/**
	 * 发起一次Curl模拟的http请求
	 * @param string $url 请求地址
	 * @param array|string $param
	 *              array:在内部,自动被http_build_query()转换
	 *              string:必须经http_build_query()转换
	 * @param string $type get|post方式
	 * @param string $verify 证书验证方式 ''|true|false
	 * @param array $header header设置
	 *                   array("Host:127.0.0.1",
	 *                   "Content-Type:application/x-www-form-urlencoded",
	 *                   'Referer:http://127.0.0.1/toolindex.xhtml',
	 *                   'User-Agent: Mozilla/4.0 (compatible; MSIE .0; Windows NT 6.1; Trident/4.0; SLCC2;)');
	 * @return array
	 */
	public function httpRequestOnce($url,$param,$type='post',$verify='',$header=''){
		if(!empty($param) && is_array($param)){
			$param = http_build_query($param);
		}
		$curlHandle = curl_init($url.($type!='post'?"?$param":''));										// 初始化curl
		$options = array(
			CURLOPT_HEADER => false,            // 不显示返回的Header区域内容
			CURLOPT_RETURNTRANSFER => true,    // 获取的信息以文件流的形式返回
			CURLOPT_CONNECTTIMEOUT => 20,       // 连接超时
			CURLOPT_TIMEOUT => 40                // 总超时
		);
		$options[CURLOPT_DNS_USE_GLOBAL_CACHE] = false;
		if($type=='post'){
			$options[CURLOPT_POST] = true;
			$options[CURLOPT_POSTFIELDS] = $param;
		}
		if($verify!==''){
			$options[CURLOPT_SSL_VERIFYPEER] = $verify; // 验证对方提供的（读取https）证书是否有效，过期，或是否通过CA颁发的！
			$options[CURLOPT_SSL_VERIFYHOST] = $verify; // 从证书中检查SSL加密算法是否存在
		}
		if($header!==''){
			$options[CURLOPT_HTTPHEADER] = $header; //header信息设置
		}
		curl_setopt_array($curlHandle,$options);
		$httpResult = curl_exec($curlHandle);
		$errorMsg = curl_error($curlHandle);
		if (false === $httpResult || !empty($errorMsg)){
			$errorNo = curl_errno($curlHandle);
			$errorInfo = curl_getinfo($curlHandle);
			curl_close($curlHandle);
			return array(
				'result' => false,
				'msg' => $errorMsg,
				'url' => "[$type]$url?".urldecode($param),
				'errno' => $errorNo,
				'errinfo' => $errorInfo
			);
		}
		curl_close($curlHandle);//关闭curl
		return array(
			'result' => true,
			'msg' => $httpResult,
			'url' => "[$type]$url?".urldecode($param)
		);
	}
}