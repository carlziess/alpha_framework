<?php
/*================================================================
*  File Name：Cookies.php
*  Author：carlziess, chengmo9292@126.com
*  Create Date：2016-02-20 18:55:40
*  Description：
===============================================================*/
namespace Utility;
use Utility\Arrays;
class Cookies
{
    const forever = 525600;
    static public $jar = [];
    static public function has($name) 
    {
        return !is_null(static::get($name));
    }

    static public function get($name, $default = null) 
    {
        if (isset(static::$jar[$name]))
        {
            return static::parse(static::$jar[$name]['value']);
        }
        if (!is_null($value = Arrays::array_get($_COOKIE, $name))) {
            return static::parse($value);
        }
        return $default;
    }

    static public function put($name, $value, $expiration = 0, $path = '/', $domain = null, $secure = false, $httponly = false) 
    {
        if ($expiration instanceof \DateTime) {
            $expiration = $expiration->format('U');
        } else {
            if ($expiration !== 0) {
                $expiration = time() + ($expiration * 60);
            }
        }
        //签名原始数据防串改
        $value = static::hash($value) . '+' . $value;
        static::$jar[$name] = compact('name', 'value', 'expiration', 'path', 'domain', 'secure', 'httponly');
        return setcookie($name, $value, $expiration, $path, $domain, $secure, $httponly);
    }

    static public function forever($name, $value, $path = '/', $domain = null, $secure = false) 
    {
        return static::put($name, $value, static::forever, $path, $domain, $secure);
    }

    static public function forget($name, $path = '/', $domain = null, $secure = false) 
    {
        return static::put($name, null, - 2628000, $path, $domain, $secure);
    }

    static public function hash($value) 
    {
        $config = (new \Yaf\Config\Ini(APPLICATION_PATH. DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'security.ini'));
        return hash_hmac('sha256', $value, $config->secure->key);
    }

    static public function parse($value) 
    {
        $segments = explode('+', $value);
        if (!(count($segments) >= 2)) {
            return null;
        }
        $value = implode('+', array_slice($segments, 1));
        if ($segments[0] == static::hash($value)) {
            return $value;
        }
        return null;
    }
}
