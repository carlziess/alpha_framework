<?php
/*================================================================
*   File Name：Aes.php
*   Author：carlziess, lizhenglin@g7.com.cn
*   Create Date：2016-02-20 10:21:32
*   Description：
================================================================*/
namespace Encryptions;
use \Utility\Strings as Str;
class Aes
{
	static protected $cipher = MCRYPT_RIJNDAEL_256;
	static protected $mode = MCRYPT_MODE_CBC;
	static protected $block = 32;
	static public function encrypt($value)
	{
		$iv = mcrypt_create_iv(static::iv_size(),static::randomizer());
		$value = static::pad($value);
		$value = mcrypt_encrypt(static::$cipher,static::key(),$value,static::$mode,$iv);
		return base64_encode($iv.$value);
	}

	static public function decrypt($value)
	{
		$value = base64_decode($value);
		$iv = substr($value,0,static::iv_size());
		$value = substr($value,static::iv_size());
		$key = static::key();
		$value = mcrypt_decrypt(static::$cipher,$key,$value,static::$mode,$iv);
		return static::unpad($value);
	}

	
	static public function randomizer()
	{
		if(defined('MCRYPT_DEV_URANDOM'))
		{
			return MCRYPT_DEV_URANDOM;
			
		}elseif(defined('MCRYPT_DEV_RANDOM')){
			
			return MCRYPT_DEV_RANDOM;
		}else{
			
			mt_srand();
			return MCRYPT_RAND;
		}
	}

	static protected function iv_size()
	{
		return mcrypt_get_iv_size(static::$cipher,static::$mode);
	}
	
	static protected function pad($value)
	{
		$pad = static::$block - (Str::length($value) % static::$block);
		return $value .= str_repeat(chr($pad), $pad);
	}

	
	static protected function unpad($value)
	{
		$pad = ord($value[($length = Str::length($value)) - 1]);
		if($pad and $pad < static::$block)
		{
			if(preg_match('/'.chr($pad).'{'.$pad.'}$/',$value))
			{
				return substr($value,0,$length - $pad);
			}else{
				
				throw new \Exception("Decryption error. Padding is invalid.");
			}
		}
		return $value;
	}

	static protected function key()
	{
		return \Yaf_Registry::get('config')->secure->key;
	}

}
