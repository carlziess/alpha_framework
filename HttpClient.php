<?php
/*================================================================
*  File Name：HttpClient.php
*  Author：carlziess, chengmo9292@126.com
*  Create Date：2016-08-07 01:02:27
*  Description：
===============================================================*/
use \Utility\Date;
class HttpClient
{
    static protected $sidecarHost = 'http://localhost';
    static protected $sidecarPort = 5000;
    static protected $zullHost = 'http://zull.huoyunren.com';
    static protected $zullPort = '80';
    static protected $header = false;

    static public function zull($api = '', $args = [], $method = 'POST', $timer = [])
    {
        if('' == $api || false == strpos($api,'-') || false == strpos($api,'.'))
            throw new \Exception('接口名称无效!',403);
        $config = \Yaf_Registry::get('config')->springcloud;                 
        if(
            empty($config) || 
            !isset($config['zullSecretKey']) || 
            empty($config['zullSecretKey']) || 
            !isset($config['zullAccessId']) || 
            empty($config['zullAccessId'])
        ) throw new \Exception('Zull配置无效!',403);
        $zullHost = !empty($config) && isset($config['zullHost']) ? $config['zullHost'] : static::$zullHost;
        $zullPort = !empty($config) && isset($config['zullPort']) ? $config['zullPort'] : static::$zullPort;
        $path = strtr($api, '.', '/');        
        $uri = $zullHost.$path;
        $args['accessid'] = $config['zullAccessId'];
        $args['sign'] = static::sign($path, $method, $config['zullSecretKey']);
        return static::send($timer, $uri, $args, $method);
    }

    static public function sidecar($api = '', $args = [], $method = 'POST', $timer = [])
    {
        if('' == $api || false == strpos($api,'-') || false == strpos($api,'.'))
            throw new \Exception('接口名称无效!',403);
        $config = \Yaf_Registry::get('config')->springcloud;                 
        $sidecarHost = !empty($config) && isset($config['sidecarHost']) ? $config['sidecarHost'] : static::$sidecarHost;
        $sidecarPort = !empty($config) && isset($config['sidecarPort']) ? $config['sidecarPort'] : static::$sidecarPort;
        $uri = $sidecarHost.':'.$sidecarPort.'/'.static::getRestfullPath($api);
        return static::send($uri, $args, $method, $timer); 
    }

    static protected function send($uri, $args = [], $method = 'POST', $timer = [])
    {                                                                                                                    
        if('' == $uri) 
            throw new \Exception('URI参数无效!',403);
        $startTime = Date::getMicroTime(microtime());                            
        $handle = curl_init();                                                                                           
        if('POST' == $method)                                                        
        {                                                                            
            curl_setopt($handle,CURLOPT_URL,$uri);                                   
            curl_setopt($handle,CURLOPT_POST,true);                                  
            curl_setopt($handle,CURLOPT_POSTFIELDS,http_build_query($args));         
        }                                                                            
        if('GET' == $method)                                                         
        {                                                                            
            curl_setopt($handle,CURLOPT_URL,$uri.'?'.http_build_query($args));                                 
        }
        curl_setopt($handle,CURLOPT_USERAGENT,'alphabot');                                     
        curl_setopt($handle,CURLOPT_SSL_VERIFYPEER,false);                                   
        curl_setopt($handle,CURLOPT_SSL_VERIFYHOST,false);                                   
        curl_setopt($handle,CURLOPT_RETURNTRANSFER,true);                                    
        curl_setopt($handle,CURLOPT_HEADER,static::$header);                                                               
        curl_setopt($handle,CURLOPT_NOSIGNAL,1);                                 
        $connectTimeout = isset($timer['connection_timeout']) && !empty($timer['connection_timeout']) ? : 30;    
        $executeTimeout = isset($timer['execute_timeout']) && !empty($timer['execute_timeout']) ? : 30;    
        $msTimer = isset($timer['ms']) && !empty($timer['ms']) ? : 0;
        if(0 == $msTimer)
        {
            curl_setopt($handle,CURLOPT_CONNECTTIMEOUT,$connectTimeout);             
            curl_setopt($handle,CURLOPT_TIMEOUT,$executeTimeout);                                                            
        }else{
            curl_setopt($handle,CURLOPT_CONNECTTIMEOUT_MS,$connectTimeout);             
            curl_setopt($handle,CURLOPT_TIMEOUT_MS,$executeTimeout);                                                            
        }
        $result = curl_exec($handle);                                                                                    
        $response_code = curl_getinfo($handle,CURLINFO_HTTP_CODE);               
        curl_close($handle);                                                                                             
        $endTime = Date::getMicroTime(microtime());                              
        $duration = $endTime - $startTime;                                       
        return [                                                                                                         
            'result'        =>  $result,                                                                                 
            'request_time'  =>  date("Y-m-d H:i:s",$startTime),                  
            'response_time' =>  date("Y-m-d H:i:s",$endTime),                    
            'response_code' =>  $response_code,                                  
            'duration'      =>  $duration                                        
        ];                                                                                                               
    }

    static protected function sign($method = 'POST', $path = '', $secretKey = '')
    {
        if('' == $path || '' == $secretKey) return false;
        return base64_encode(hash_hmac('sha1',utf8_encode($method."\n".Date::getMicroTime(microtime())."\n".$path),$secretKey,true));
    }
    
    static protected function getRestfullPath($api = '')
    {
        $apiArr = explode('.',$api);
        if(3 !== count($apiArr)) return false;
        array_splice($apiArr,1,0,'Vega');
        return implode($apiArr,'/');
    }


}

?>
