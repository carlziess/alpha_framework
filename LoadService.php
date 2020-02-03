<?php
/*================================================================                   
 *   File Name：LoadService.php                                                        
 *   Author：carlziess, lzl@rsung.com
 *   Create Date：2018-06-5 01:58:58                                                 
 *   Description： 
 ================================================================*/                   
class HttpClient                                                                      
{                                                                                    
    private $sign_method = 'md5';
    private $connect_timeout = 5;
    private $execute_timeout = 5;
    private $header = false;

    private $service = [
        'dhbpc'=>[
            'appkey' => '5A9BC365C3820D1DF49202EAB8F7EF13',
            'appsecret' => 'DA30D4DAFF79D94337FD47070831C635',
            'url' => 'http://pc.newdhb.com/router',
            'partner_id' => 1000,
            'sign_method' => 'md5',
            'connect_timeout' => 5,
            'execute_timeout' => 5
        ],
        'admin'=>[
            'appkey' => '5A9BC365C3820D1DF49202EAB8F7EF13',
            'appsecret' => 'DA30D4DAFF79D94337FD47070831C635',
            'url' => 'http://pc.newdhb.com/router',
            'partner_id' => 1000,
            'sign_method' => 'md5',
            'connect_timeout' => 5,
            'execute_timeout' => 5
        ]
    ];

    public function __construct()
    {
        set_exception_handler([$this,'errorMessage']);  
    }

    /**
     * 接口代理
     * @param array $params 接口清楚参数
     * @return json
     */
    public function request($params = [])
    {
        if (empty($params)) {
            throw new Exception('必要参数为空!', 400);
        }
        $requestParameters = [
            'format' => '返回格式',
            'params' => '参数',
            'partner_id' => '合作方ID',
            'signature' => '签名',
            'sign_method' => '签名算法',
            'timestamp' => '时间戳',
            'type' => '请求类型',
            'v' => '接口版本',
        ];
        ksort($params);
        if (empty($params['app_key'])) {
            throw new Exception('app_key无效', 400);
        }
        if (empty($params['method']) || 0 ==  preg_match('/^[\w-]+(?:\d+)*+(?:[\w- .]*)$/', $params['method'])) {
            throw new Exception('请求方法无效', 400); 
        }
        $application = $this->getApplicationByMethod($method);
        $sign = $clients[$params['app_key']];
        foreach($requestParameters as $k=>$v) {
            if (empty($params[$k])) {
                throw new Exception($k . '无效', 400);
            }
            if ('format' == $k && $params[$k] !== 'json') {
                throw new Exception('format不支持', 400);
            }
            if ('sign_method' == $k && !in_array($params[$k], ['md5', 'sha256'])) {
                throw new Exception('sign_method签名算法无效', 400);
            }
            if ('type' == $k && !in_array($params[$k], ['service', 'logic', 'model'])) {
                throw new Exception('请求类型无效', 400);
            }
            if ('signature' != $k) {
                $sign .= $k . $params[$k];
            }
        }
        $sign .= $clients[$params['app_key']];
        $sign = strtoupper(md5($sign));
        return true;
    } 

    /**
     * 发送http请
     */    
    private function sendRequest($requestUri = NULL, $args = [])                                                    
    {                                                                                
        if(empty($args)) throw new Exception('请求参数不能为空!',-90006);
        if(NULL == $requestUri) throw new Exception('请求地址不能为空!',-90007);
        $handle = curl_init();                                                       
        curl_setopt($handle, CURLOPT_URL, $requestUri);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_USERAGENT, 'DHBClient');                                     
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);                                   
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);                                   
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);                                    
        curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($args));
        curl_setopt($handle, CURLOPT_HEADER, $this->header);                                           
        curl_setopt($handle, CURLOPT_NOSIGNAL, 1);                                             
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout);
        curl_setopt($handle, CURLOPT_TIMEOUT, $this->execute_timeout);                                             
        $data = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);  
        $message = '';
        if (curl_errno($handle)) {
            $code = curl_errno($handle);
            $message = curl_error($handle);
        }
        curl_close($handle);
        return ['code'=>$code,'data'=>$data, 'message'=>$message];
    }

    /**
     * 数组转字符串
     * @param array $arr 源数组
     * @description 通过递归将多维数组转换成字符串
     */
    public function array2string($arr = [])
    {
        if (empty($arr)) return '';
        $str = '';
        foreach($arr as $k=>$v){
            if (!is_array($v)) {
                $str .= $k . $v; 
            } else {
                $str .= $k . self::array2string($v); 
            }
        }   
        return $str;
    }

    /**
     * 异常处理
     * @param object $e 异常处理对象
     * @description 输出的必须是json
     */
    public function errorMessage($e)
    {
        header('Content-Type: application/json; charset=UTF-8');
        exit(json_encode(['code'=>$e->getCode(), 'data'=>'', 'message'=>$e->getMessage()]));
    }

}                                                                                    


