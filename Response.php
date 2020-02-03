<?php
/*================================================================
*  File Name：Response.php
*  Author：carlziess, chengmo9292@126.com
*  Create Date：2016-10-04 10:46:40
*  Description：
===============================================================*/
class Response extends Yaf\Response\Http
{
    use Singleton;

    protected $contentType = [
        'json'  =>  'application/json',
        'png'   =>  'image/png',
        'html'  =>  'text/html'
    ];

    public function send($data = '')
    {
        $responseType = Yaf\Registry::get('responseType');
        $contentType = isset($responseType) ? $responseType : 'json'; 
        if (true === array_key_exists($contentType, $this->contentType))
        {
            $this->setHeader('Content-Type',$this->contentType[$contentType].';charset=utf-8');
            $responseData = 'json' === $contentType ? json_encode($data) : $data;
            $this->setBody($responseData);
            $this->response();
            die();
        }
        die(json_encode(['code'=>0,'data'=>[]]));
    }


}
