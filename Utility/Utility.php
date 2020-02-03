<?php
/*================================================================
*   File Name：Utility.php
*   Author：carlziess, lizhenglin@g7.com.cn
*   Create Date：2016-02-15 10:26:28
*   Description：
================================================================*/
namespace Utility;
class Utility
{

	/**
	 * 递归合并两个数组
	 * @param array $array1 数组1
	 * @param array $array2	数组2
	 * @return array 合并后的数组
	 */
	static public function mergeArray($array1, $array2) {
		foreach ($array2 as $key => $value) {
			if (!isset($array1[$key])) {
				$array1[$key] = $value;
			} elseif (is_array($array1[$key]) && is_array($value)) {
				$array1[$key] = self::mergeArray($array1[$key], $array2[$key]);
			} elseif (is_numeric($key) && $array1[$key] !== $array2[$key]) {
				$array1[] = $value;
			} else
				$array1[$key] = $value;
		}
		return $array1;
	}

	/**
	 * 将字符串首字母小写
	 * @param string $str
	 *        	待处理的字符串
	 * @return string 返回处理后的字符串
	 */
	static public function lcfirst($str) {
		$str[0] = strtolower($str[0]);
		return $str;
	}

	/**
	 * 获得随机数字符串
	 * @param int $length
	 *        	随机数的长度
	 * @return string 随机获得的字串
	 */
	static public function generateRandStr($length) {
		$mt_string = 'AzBy0CxDwEv1FuGtHs2IrJqK3pLoM4nNmOlP5kQjRi6ShTgU7fVeW8dXcY9bZa';
		$randstr = '';
		for ($i = 0; $i < $length; $i++) {
			$randstr .= $mt_string[mt_rand(0, 61)];
		}
		return $randstr;
	}
	
	/**
	 * 生成32位guid
	 */
	static public function guid(){
		if(function_exists('com_create_guid')){
			$uuid = com_create_guid();
		}else{
			mt_srand((double)microtime() * 10000); // optional for php 4.2.0 and up.
			$charid = strtoupper(md5(uniqid(rand(),1)));
			$hyphen = chr(45); // "-"
			$uuid = chr(123) . 		// "{"
			substr($charid, 0, 8) . 
			$hyphen . substr($charid, 8, 4) .
			$hyphen . substr($charid, 12, 4) . 
			$hyphen . substr($charid, 16, 4) . 
			$hyphen . substr($charid, 20, 12) . 
			chr(125); // "}"
		}
		$uuid = str_replace(array(
				'-',
				'{',
				'}'
		), '', $uuid);
		return $uuid;
	}
	
	/**
	 * 对字符串中的参数进行替换
	 * 该函优化了php strtr()实现, 在进行数组方式的字符替换时支持了两种模式的字符替换:
	 * @example <pre>
	 *          1. echo Utility::strtr("I Love {you}",array('{you}' =>
	 *          'lili'));
	 *          结果: I Love lili
	 *          2. echo Utility::strtr("I Love
	 *          #0,#1",array('lili','qiong'));
	 *          结果: I Love lili,qiong
	 *          <pre>
	 * @see WindLangResource::getMessage()
	 * @param string $str        	
	 * @param string $from        	
	 * @param string $to
	 *        	可选参数,默认值为''
	 * @return string
	 */
	static public function strtr($str, $from, $to = ''){
		if (is_string($from)) return strtr($str, $from, $to);
		if (isset($from[0])) {
			foreach ($from as $key => $value) {
				$from['#' . $key] = $value;
				unset($from[$key]);
			}
		}
		return !empty($from) ? strtr($str, $from) : $str;
	}
	/**
	 * 获取IP
	 * @return string
	 */
	public	static function ip(){
		static $ip;
		if(null===$ip){
			$ip = getenv('HTTP_CLIENT_IP');
			if(!$ip || !(strcasecmp($ip, 'unknown'))){
				$ip = getenv('HTTP_X_FORWARDED_FOR');
				if(!$ip || !(strcasecmp($ip,'unknown'))){
					$ip = getenv('REMOTE_ADDR');
					if(!$ip || !(strcasecmp($ip, 'unknown'))){
						if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')){
							$ip = $_SERVER['REMOTE_ADDR'];
						}
					}
				}
			}
			$ip = $ip ? : 'unknown';
		}
		return $ip;
	}
	
	/**
	 * @param string $code
	 * @param string $data
	 * @param string $msg
	 */
	static public function ajax($code=0,$data=null,$msg=null)
	{
		header('Content-Type:application/json;charset=UTF-8');
		$arr['code'] = $code;
		if(null !== $data)$arr['data'] = $data;
		if(null !== $msg)$arr['msg'] = $msg;
		$res = json_encode($arr,JSON_NUMERIC_CHECK);
		die($res);
	}
	
	/**
	 * @param array $data
	 */
	static public function data($code=0,$data=null,$msg=null)
	{
		$arr['code'] = $code;
		if(null !== $data)$arr['data'] = $data;
		if(null !== $msg)$arr['msg'] = $msg;
		$res = json_encode($arr,JSON_UNESCAPED_UNICODE);
		die($res);
	}
	
	/**
	 * 
	 * @param string $err
	 * @param unknown $msg
	 */
	static public function xheditor($err,$msg=[])
	{
		$arr = [
				'err' => $err,
				'msg' => $msg
		];
		die(json_encode($arr,JSON_UNESCAPED_UNICODE));
	}
	
	/**
	 * 
	 * @param string $code
	 * @param string $msg
	 * @param array $args
	 */
	static public function dwz($code,$msg='',$args=[])
	{
		$data = [
				'statusCode' => $code,
				'message' => $msg
		];
		if (!empty($args)){
			$data = array_merge($data,$args);
		}
		echo json_encode($data,JSON_UNESCAPED_UNICODE);
		die;
	}
	
	/**
	 * 
	 * @param unknown $value
	 * @return unknown|mixed
	 */
	static public function value($value)
	{
		return (is_callable($value) && ! is_string($value)) ? call_user_func($value) : $value;
	}
	
	
}
