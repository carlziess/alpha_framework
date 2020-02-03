<?php
/**
 * @name Bootstrap
 * @author {&$AUTHOR&}
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * @see http://www.php.net/manual/en/class.yaf-bootstrap-abstract.php
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends \Yaf_Bootstrap_Abstract
{
    protected $config;
    
    /**
    * Initialization Yaf
    * @param Yaf_Dispatcher $dispatcher
    */
    public function _initYaf(Yaf_Dispatcher $dispatcher)
    {
        $this->config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set('config',$this->config);
        $router = $dispatcher->getRouter();
        null!== $this->config->routes && $router->addConfig($this->config->routes);
        $driver = $this->config->get('auth.driver');
        Yaf_Loader::getInstance($this->config['application']['library'],$this->config['yaf']['library']);
        $dispatcher->setView((new View()));
        $dispatcher->registerPlugin((new DemoPlugin()));
        Authorize::extend($driver,function()use($driver){return new $driver;});
        unset($this->config, $router, $plugin, $driver);
    }

}
