<?php
/*================================================================
 *   File Name：Strings.php
 *   Author：carlziess, lizhenglin@g7.com.cn
 *   Create Date：2016-02-20 22:32:24
 *   Description：
 ================================================================*/
namespace Utility;
class Strings
{
    const UTF8 = 'utf-8';
    const GBK = 'gbk';

    /**
     * 截取字符串,支持字符编码,默认为utf-8
     * 
     * @param string $str 要截取的字符串编码
     * @param int $start     开始截取
     * @param int $length    截取的长度
     * @param string $charset 原妈编码,默认为UTF8
     * @param boolean $dot    是否显示省略号,默认为false
     * @return string 截取后的字串
     */
    static public function substr($str, $start, $length, $charset = self::UTF8, $dot = false) {
        switch (strtolower($charset)) {
        case self::GBK:
            $str = self::substrForGbk($str, $start, $length, $dot);
            break;
        case self::UTF8:
            $str = self::substrForUtf8($str, $start, $length, $dot);
            break;
        default:
            $str = substr($str, $start, $length);
        }
        return $str;
    }

    static public function length($value)                                            
    {                                                                                
        return (MB_STRING) ? mb_strlen($value, 'UTF-8') : strlen($value);            
    }                                                                                

    /**
     * 求取字符串长度
     * 
     * @param string $str  要计算的字符串编码
     * @param string $charset 原始编码,默认为UTF8
     * @return int
     */
    static public function strlen($str, $charset = self::UTF8) 
    {
        switch (strtolower($charset)) {
        case self::GBK:
            $count = self::strlenForGbk($str);
            break;
        case self::UTF8:
            $count = self::strlenForUtf8($str);
            break;
        default:
            $count = strlen($str);
        }
        return $count;
    }

    /**
     * 将变量的值转换为字符串
     *
     * @param mixed $input   变量
     * @param string $indent 缩进,默认为''
     * @return string
     */
    static public function varToString($input, $indent = '') 
    {
        switch (gettype($input)) {
        case 'string':
            return "'" . str_replace(array("\\", "'"), array("\\\\", "\\'"), $input) . "'";
        case 'array':
            $output = "array(\r\n";
            foreach ($input as $key => $value) {
                $output .= $indent . "\t" . self::varToString($key, $indent . "\t") . ' => ' . self::varToString(
                    $value, $indent . "\t");
                $output .= ",\r\n";
            }
            $output .= $indent . ')';
            return $output;
        case 'boolean':
            return $input ? 'true' : 'false';
        case 'NULL':
            return 'NULL';
        case 'integer':
        case 'double':
        case 'float':
            return "'" . (string) $input . "'";
        }
        return 'NULL';
    }

    /**
     * 将数据用json加密
     *
     * @param mixed $value 需要加密的数据
     * @param string $charset 字符编码
     * @return string 加密后的数据
     */
    static public function jsonEncode($value, $charset = self::UTF8) 
    {
        return Json::encode($value, $charset);
    }

    /**
     * 将json格式数据解密
     *
     * @param string $value 待解密的数据
     * @param string $charset 解密后字符串编码
     * @return mixed 解密后的数据
     */
    static public function jsonDecode($value, $charset = self::UTF8) 
    {
        return Json::decode($value, true, $charset);
    }

    /**
     * 以utf8格式截取的字符串编码
     * 
     * @param string $str  要截取的字符串编码
     * @param int $start      开始截取
     * @param int $length     截取的长度，默认为null，取字符串的全长
     * @param boolean $dot    是否显示省略号，默认为false
     * @return string
     */
    static public function substrForUtf8($str, $start, $length = null, $dot = false) 
    {
        $l = strlen($str);
        $p = $s = 0;
        if (0 !== $start) {
            while ($start-- && $p < $l) {
                $c = $str[$p];
                if ($c < "\xC0")
                    $p++;
                elseif ($c < "\xE0")
                    $p += 2;
                elseif ($c < "\xF0")
                    $p += 3;
                elseif ($c < "\xF8")
                    $p += 4;
                elseif ($c < "\xFC")
                    $p += 5;
                else
                    $p += 6;
            }
            $s = $p;
        }

        if (empty($length)) {
            $t = substr($str, $s);
        } else {
            $i = $length;
            while ($i-- && $p < $l) {
                $c = $str[$p];
                if ($c < "\xC0")
                    $p++;
                elseif ($c < "\xE0")
                    $p += 2;
                elseif ($c < "\xF0")
                    $p += 3;
                elseif ($c < "\xF8")
                    $p += 4;
                elseif ($c < "\xFC")
                    $p += 5;
                else
                    $p += 6;
            }
            $t = substr($str, $s, $p - $s);
        }

        $dot && ($p < $l) && $t .= "...";
        return $t;
    }

    /**
     * 以gbk格式截取的字符串编码
     * 
     * @param string $str  要截取的字符串编码
     * @param int $start      开始截取
     * @param int $length     截取的长度，默认为null，取字符串的全长
     * @param boolean $dot    是否显示省略号，默认为false
     * @return string
     */
    static public function substrForGbk($str, $start, $length = null, $dot = false) 
    {
        $l = strlen($str);
        $p = $s = 0;
        if (0 !== $start) {
            while ($start-- && $p < $l) {
                if ($str[$p] > "\x80")
                    $p += 2;
                else
                    $p++;
            }
            $s = $p;
        }

        if (empty($length)) {
            $t = substr($str, $s);
        } else {
            $i = $length;
            while ($i-- && $p < $l) {
                if ($str[$p] > "\x80")
                    $p += 2;
                else
                    $p++;
            }
            $t = substr($str, $s, $p - $s);
        }

        $dot && ($p < $l) && $t .= "...";
        return $t;
    }

    /**
     * 以utf8求取字符串长度
     * 
     * @param string $str     要计算的字符串编码
     * @return int
     */
    static public function strlenForUtf8($str) 
    {
        $l = strlen($str);
        $p = $c = 0;
        while ($p < $l) {
            $a = $str[$p];
            if ($a < "\xC0")
                $p++;
            elseif ($a < "\xE0")
                $p += 2;
            elseif ($a < "\xF0")
                $p += 3;
            elseif ($a < "\xF8")
                $p += 4;
            elseif ($a < "\xFC")
                $p += 5;
            else
                $p += 6;
            $c++;
        }
        return $c;
    }

    /**
     * 以gbk求取字符串长度
     * 
     * @param string $str     要计算的字符串编码
     * @return int
     */
    static public function strlenForGbk($str) 
    {
        $l = strlen($str);
        $p = $c = 0;
        while ($p < $l) {
            if ($str[$p] > "\x80")
                $p += 2;
            else
                $p++;
            $c++;
        }
        return $c;
    }

    /**
     * 小写(utf-8)
     * @param string $str
     */
    static public function toLower($str)
    {
        return (MB_STRING) ? mb_strtolower($str, 'UTF-8') : strtolower($str);
    }

    /**
     * 生成随机串
     * @param int $length
     * @param string $type
     */
    static public function random($length, $type = 'alnum')	
    {
        return substr(str_shuffle(str_repeat(static::pool($type), 5)), 0, $length);
    }

    /**
     * 生成guid
     */
    static public function guid() 
    {                                                                                            
        if(function_exists('com_create_guid')){                                 
            $uuid = com_create_guid();                                                                                   
        }else{                                                                                                           
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up. 
            $charid = strtoupper(md5(uniqid(rand(), true)));                     
            $hyphen = chr(45);// "-"                                                                                     
            $uuid = chr(123)// "{"                                                                                       
                .substr($charid, 0, 8).$hyphen                                       
                .substr($charid, 8, 4).$hyphen                                       
                .substr($charid,12, 4).$hyphen                                       
                .substr($charid,16, 4).$hyphen                                       
                .substr($charid,20,12)                                                                                       
                .chr(125);// "}"                                                                                             
        }                                                                                                                
        $uuid = str_replace(array('-', '{', '}'), '', $uuid);                    
        return $uuid;                                                                                                    
    } 

    static protected function pool($type)
    {
        switch ($type){
        case 'alpha':
            return 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        case 'alnum':
            //return '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            //去除不太容易识别的干扰项
            return '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ';

        default:
            throw new \Exception("Invalid random string type [$type].");
        }
    }

    static public function byteLength($string)
    {
        return mb_strlen($string, '8bit');
    }

    static public function byteSubstr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length === null ? mb_strlen($string, '8bit') : $length, '8bit');
    }

    static public function base64UrlDecode($input)
    {
        return base64_decode(strtr($input, '-_', '+/')); 
    }
    
    static public function base64UrlEncode($input)
    {
        return strtr(base64_encode($input), '+/', '-_');
    }
}
