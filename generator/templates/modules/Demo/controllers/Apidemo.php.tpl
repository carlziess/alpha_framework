<?php
/**
 * @name ApiDemoController
 * @author {&$AUTHOR&}
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class ApidemoController extends Yaf_Controller_Abstract 
{
    
	/** 
     * 此方法是Yaf_Controller_Abstract中的复写
     * init方法会在每个Action前执行，用作controller的初始化操作
     */
    public function init()
    {
        //关闭controller视图渲染，前后端分离不需要视图
        Yaf_Dispatcher::getInstance()->disableView();
        //注册本地类命名空间，应用目录中library里的类必须先注册为localNamespace才能被加载
        Yaf_Loader::getInstance()->registerLocalNamespace('Demo');
        //response对象用作接口返回过程中的数据设置
        $this->response = new Yaf_Response_Http();
    }

	/** 
     * 默认动作
     */
	public function indexAction() 
    {
		$params = $this->getRequest()->getQuery() + $this->getRequest()->getPost() + $this->getRequest()->getParams();
		//@tips response对象用作函数末尾进行响应数据封装，$this->response->response();写在函数其他地方并不会阻断代码的继续执行
		$this->response->setHeader('Content-Type', 'application/json;charset=utf-8');
        $this->response->setBody(json_encode(['code'=>0,'data'=>$params]));
        $this->response->response();
	}

	/** 
     * 带视图的动作,需要在init方法中把Yaf_Dispatcher::getInstance()->disableView();
     * 注释掉
     */
	public function viewAction($name = 'Stranger') 
    {
		//1. fetch query
		$get = $this->getRequest()->getQuery("get", "default value");
		//2. fetch model
		$model = new DemoModel();
		//3. assign
		$this->getView()->assign("content", $model->getContent(5555));
		$this->getView()->assign("name", $name);
		//4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
        return TRUE;
	}

}
