<?php
/*================================================================
*  File Name：Controller.php
*  Author：carlziess, chengmo9292@126.com
*  Create Date：2016-09-03 00:39:56
*  Description：
===============================================================*/
class Controller extends \Yaf\Controller_Abstract
{

    public function init()
    {
        $this->beforeInit();
        $this->responseType();
        //开启CSRF验证
        //Request::getInstance()->getCsrfToken();
    }
    
    protected function beforeInit() { }

    protected function responseType($responseType = NULL)
    {
        $responseType = NULL === $responseType ? Yaf\Registry::get('responseType') : $responseType;
        switch($responseType)
        {
            case 'json':
            case 'msgpack':
                Yaf\Dispatcher::getInstance()->disableView(); 
                Yaf\Dispatcher::getInstance()->autoRender(false);
                $this->_view->engine = NULL;
                break;
            case 'tpl':
                Yaf\Dispatcher::getInstance()->autoRender(true);
                $this->_view->engine = Template::instance();
                break;
            case 'yaf':
            default:
                $responseType = 'yaf';
                Yaf\Dispatcher::getInstance()->autoRender(true);
                $this->_view->engine = NULL;
        }
        Yaf\Registry::set('responseTyep',$responseType);
    }
    

}
?>
