<?php
/*================================================================
 *  File Name：Rsa.php
 *  Author：carlziess, chengmo9292@126.com
 *  Create Date：2016-09-21 22:39:49
 *  Description：
 ===============================================================*/
namespace Encryptions;
if(false == extension_loaded('openssl')) throw new \Exception('not found openssl extensions.',500);
class Rsa 
{
    static public function privateKeyEncode($plainText = '', $privateKey = '')
    {
        if('' == $plainText || '' == $privateKey) throw new \Exception('The plain text and private key can not be null.',500);
        if(false == $privateKey = openssl_pkey_get_private($privateKey)) throw new \Exception('The private key it is invalid.',500);
        if(false === openssl_private_encrypt($plainText, $crypted, $privateKey)) throw new \Exception('Encrytion failed.',500);
        openssl_free_key(openssl_pkey_get_private($privateKey));
        return static::strToHex(base64_encode($crypted));
    }

    static public function privateKeyDecode($cipherText = '', $privateKey = '')
    {
        if('' == $cipherText || '' == $privateKey) throw new \Exception('The cipher text and private key can not be null.',500);
        if(false == $privateKey = openssl_pkey_get_private($privateKey)) throw new \Exception('The private key it is invalid',500);
        if(false === openssl_private_decrypt(base64_decode(static::hexToStr($cipherText)), $decrypted, $privateKey)) throw new \Exception('Decryption failed',500);
        openssl_free_key(openssl_pkey_get_private($privateKey));
        return $decrypted;
    }

    static public function publicKeyEncode($plainText = '', $publicKey = '')
    {
        if('' == $plainText || '' == $publicKey) throw new \Exception('The plain text and public key can not be null.',500);
        if(false == $publicKey = openssl_pkey_get_public($publicKey)) throw new \Exception('The public key it is invalid',500);
        if(false === openssl_public_encrypt($plainText,$encrypted,$publicKey)) throw new \Exception('Encryption failed.',500);
        openssl_free_key($publicKey);
        return static::strToHex(base64_encode($encrypted));
    }

    static public function publicKeyDecode($cipherText = '', $publicKey = '')
    {
        if('' == $cipherText || '' == $publicKey) throw new \Exception('The cipher text and public key can not be null',500);
        if(false == $publicKey = openssl_pkey_get_public($publicKey)) throw new \Exception('The public key it is invalid',500);
        if(false === openssl_public_decrypt(base64_decode(static::hexToStr($cipherText)), $decrypted,$publicKey)) throw new \Exception('Decryption failed.',500); 
        openssl_free_key($publicKey);
        return $decrypted;
    }

    static protected function hexToStr($hex = '')
    {
        if('' == $hex) return false;
        $string = '';
        for($i = 0; $i < strlen($hex) - 1; $i+=2){
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }

    static protected function strToHex($string = '')
    {
        if('' == $string) return false;
        $hex = '';
        for($i = 0; $i < strlen($string); $i++){
            $hex .= dechex(ord($string[$i]));
        }
        return strtoupper($hex);
    }

}




