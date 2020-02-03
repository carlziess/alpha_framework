<?php
/*================================================================
 *  File Name：View.php
 *  Author：carlziess, chengmo9292@126.com
 *  Create Date：2016-09-03 13:03:01
 *  Description：
 ===============================================================*/
define('VIEW_EXT', '.'.\Yaf_Registry::get('config')->get('yaf.view.ext'));
class View implements \Yaf_View_Interface
{
    public $engine;
    protected $options = [],$tpl_vars = [], $tpl_dir;

    public function __construct($options = [])
    {
        $this->options = $options;
    }

    public function engine()
    {
        return $this->engine ? : ($this->engine = new \Yaf_View_Simple($this->tpl_dir, $this->options));
    }

    public function __set($name, $value)
    {
        $this->tpl_vars[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->tpl_vars[$name]);
    }

    public function __unset($name)
    {
        if(isset($this->tpl_vars[$name]))
        {
            unset($this->tpl_vars[$name]);
        }
    }

    public function clearVars()
    {
        $this->tpl_vars = [];
    }

    public function render($tpl, $tpl_vars = [])
    {
        $tpl_vars = array_merge($this->tpl_vars, $tpl_vars);
        return $this->engine()->render($tpl, $tpl_vars);
    }

    public function display($tpl, $tpl_vars = [])
    {
        exit($this->render($tpl, $tpl_vars));
    }

    public function assign($name, $value = null)
    {
        $this->tpl_vars[$name] = $value;
    }

    public function setScriptPath($path) 
    {
        if(is_readable($path)) 
        {
            $this->tpl_dir = $path;
            return true;
        }
        throw new Exception("Invalid path: {$path}");
    }

    public function getScriptPath() 
    {
        return $this->tpl_dir;
    }
}

?>
