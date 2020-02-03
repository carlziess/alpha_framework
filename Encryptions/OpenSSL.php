<?php
/*================================================================
 *  File Name：OpenSSL.php
 *  Author：carlziess, chengmo9292@126.com
 *  Create Date：2016-09-21 09:50:32
 *  Description：
 ===============================================================*/
namespace Encryptions;
class OpenSSL
{
    
    static protected $cipher = 'AES-256-CBC';

    static public function encrypt($plainText = '', $cipher = '', $key = '',$options = OPENSSL_RAW_DATA)
    {
        if ('' === $plainText) throw new \Exception('The plain text and key can not be null', 500); 
        $cipher = '' !== $cipher ? $cipher : static::$cipher;
        if (!in_array($cipher,openssl_get_cipher_methods())) throw new \Exception('The cipher method is invalid');
        $config = (new \Yaf\Config\Ini(APPLICATION_PATH. DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'security.ini'))->secure;
        $key = '' !== $key ? $key : $config->key;
        $iv = static::iv(static::ivlength($cipher));
        $cipherTextRaw = openssl_encrypt($plainText, $cipher, $key, $options, $iv);
        $hmac = hash_hmac('sha256', $cipherTextRaw, $key, true);
        return base64_encode($iv.$hmac.$cipherTextRaw);
    }

    static public function decrypt($cipherText = '', $cipher = '', $key = '', $options= OPENSSL_RAW_DATA)
    {
        if ('' === $cipherText) throw new \Exception('The cipher text and key can not be null',500); 
        $cipher = '' !== $cipher ? $cipher : static::$cipher;
        if (!in_array($cipher,openssl_get_cipher_methods())) throw new \Exception('The cipher method is invalid');
        $config = (new \Yaf\Config\Ini(APPLICATION_PATH. DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'security.ini'))->secure;
        $key = '' !== $key ? $key : $config->key;
        $cipherTextRaw = base64_decode($cipherText);
        $ivlength = static::ivlength($cipher);
        $iv = substr($cipherTextRaw, 0 ,$ivlength);
        $hmac = substr($cipherTextRaw, $ivlength, 32);
        $cipherText = substr($cipherTextRaw,$ivlength + 32);
        return openssl_decrypt($cipherText, $cipher, $key, $options, $iv);
    }

    static protected function ivlength($cipher = '')
    {
        return openssl_cipher_iv_length($cipher); 
    }

    static protected function iv($ivlength = '')
    {
        return openssl_random_pseudo_bytes($ivlength);    
    }

    //public key/private key encryption/decryption.
    static public function privateKeyEncode($plainText = '', $privateKey = '')
	{
        if ('' == $plainText || '' == $privateKey) 
        if (false == $privateKey = openssl_pkey_get_private($privateKey))
        (openssl_private_encrypt($plainText, $crypted, $privateKey)) or die(false);
		openssl_free_key(openssl_pkey_get_private($privateKey));
		return static::strToHex(base64_encode($crypted));
	}
	
	static public function privateKeyDecrypt($cipherHexText = '', $privateKey = '', $ajax = false)
    {
        if ('' === $cipherHexText || '' === $privateKey)  
            throw new \Exception('The cipher text and private key can not be null',500);
        if (false === $privateKeyId = openssl_pkey_get_private($privateKey))
            throw new \Exception('The private key is invalid',500);
        $padding = $ajax ? OPENSSL_NO_PADDING : OPENSSL_PKCS1_PADDING;
        $cipherText = base64_decode(pack('H*', $cipherHexText));
        if (false === openssl_private_decrypt($cipherText, $plainText, $privateKeyId, $padding)) 
            throw new \Exception('Private key descryption failed.',500);
		openssl_free_key($privateKeyId);
		return $plainText;
	}
	
	static public function publicKeyEncode($plainText = '', $publicKey = '')
	{
        if ('' == $plainText || '' == $publicKey)
            return false;
        if (false == $publicKey = openssl_pkey_get_public($publicKey))
            return false;
        (openssl_public_encrypt($plainText,$encrypted,$publicKey)) or die(false);
		openssl_free_key($publicKey);
		return static::strToHex(base64_encode($encrypted));
	}
	
	static public function publicKeyDecode($cipherText = '', $publicKey = '')
	{
        if ('' == $cipherText || '' == $publicKey)
            return false;
        if (false == $publicKey = openssl_pkey_get_public($publicKey))
            return false;
        (openssl_public_decrypt(base64_decode(static::hexToStr($cipherText)), $decrypted,$publicKey)) or die(false);
		openssl_free_key($publicKey);
		return $decrypted;
	}
	
	private static function hexToStr($hex = '')
	{
		if ('' == $hex) return false;
		$string = '';
		for($i = 0; $i < strlen($hex) - 1; $i+=2){
			$string .= chr(hexdec($hex[$i].$hex[$i+1]));
		}
		return $string;
	}
	
	private static function strToHex($string = '')
	{
		if ('' == $string) return false;
		$hex = '';
		for($i = 0; $i < strlen($string); $i++){
			$hex .= dechex(ord($string[$i]));
		}
		return strtoupper($hex);
	}

}

