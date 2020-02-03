<?php
/**
 * @name IndexController
 * @author {&$AUTHOR&}
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends Controller 
{
    
	/** 
     * beforeInit方法会在每个Action前执行，用作controller的初始化操作
     */
    public function beforeInit()
    {
        Yaf_Loader::getInstance()->registerLocalNamespace('Demo');
        Yaf_Registry::set('responseType','tpl');
    }

	/** 
     * 默认动作
     */
	public function indexAction() 
    {
        $params = $this->getRequest()->getQuery() + $this->getRequest()->getPost() + $this->getRequest()->getParams();
        $this->getView()->assign('content', 'haha');   
        $this->getView()->assign('ar',['a'=>100,'b'=>200,'c'=>300,'d'=>['400','500','800']]);
	}


}
