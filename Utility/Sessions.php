<?php
/*================================================================
*  File Name：Sessions.php
*  Author：carlziess, chengmo9292@126.com
*  Create Date：2018-06-18 11:34:11
*  Description：
*  Session使用K/V,prefix作为key name的前缀。
===============================================================*/
namespace Utility;
class Sessions
{
    static public function getSession($name = '')
    {
        if ('' == $name) return null;
        $prefix = (new \Yaf\Config\Ini(APPLICATION_PATH. DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'session.ini'))->prefix;
        return false == empty($prefix) ? $_SESSION[$prefix . $name] : $_SESSION[$name];
    } 

}

