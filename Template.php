<?php
/*================================================================
 *  File Name：Template.php
 *  Author：carlziess, chengmo9292@126.com
 *  Create Date：2016-09-03 13:37:48
 *  Description：
 ===============================================================*/
use \Utility\Folder;
class Template 
{
    public $templateDir; 
    private $tpl_include_files,$tpl;

    static private $instance = NULL;
    static public function instance()
    {
        if(static::$instance === NULL)
        {
            static::$instance = new static;
        }
        return static::$instance;
    }

    public function __construct()
    {
        $this->templateDir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
    }

    public function render($__tpl,$__data)
    {
        ob_start() && (empty($__data) || extract($__data, EXTR_SKIP));
        try
        {
            $__contents = $this->template($__tpl);
            eval('?>'.$__contents);
        }catch(\Exception $e){
            $objfile = \Yaf_Registry::get('config')->get('template.cache_path') . $tpl;
            if(is_file($objfile))
            {
                unlink($objfile);
            }
            ob_get_clean(); 
            throw $e;
        }
        return ob_get_clean();
    }

    public function setScriptPath($path) {}

    public function template($tpl)
    {
        $this->needClear = (boolean)Yaf_Registry::get('config')->get('template.needClear');		
        $autoCompile = (boolean)Yaf_Registry::get('config')->get('template.autoCompile');
        $tplfile = $this->templateDir.$tpl;
        $objfile = Yaf_Registry::get('config')->get('template.cache_path').$tpl;
        $iscompile = true;
        if(is_file($objfile))
        {
            $str = file_get_contents($objfile);
            $pos = strpos($str,"\r\n");
            if($autoCompile===true)
            {
                $last_modified_time = [];
                $tpl_include_files = substr($str,0,$pos);
                if(!empty($tpl_include_files) && $tpl_include_files = @unserialize($tpl_include_files) )
                {
                    foreach($tpl_include_files as $f)
                    {
                        $last_modified_time[] = is_file($f) ? filemtime($f) : time();
                    }
                    if(filemtime($objfile) == max($last_modified_time))
                    {
                        $iscompile = false;
                        $str = substr($str,$pos);
                    }
                    unset($tpl_include_files,$f,$last_modified_time);
                }
            }else{
                $iscompile = false;
                $str = substr($str,$pos);
            }
        }
        if(true === $iscompile)
        {
            $this->tpl_include_files[] = $objfile;
            $this->tpl_include_files[] = $tplfile;
            $dir = pathinfo($objfile, PATHINFO_DIRNAME);
            if(false == is_dir($dir))
            {
                if(false == Folder::mkRecur($dir))
                {
                    trigger_error("Can not create $dir");
                }
            }
            $str = file_get_contents($tplfile);
            if(strlen($str)==0)
            {
                trigger_error('Template file Not found or have no access!', E_USER_ERROR);
            }
            $this->parse($str);
            $prefix = "\r\n";
            $postfix = "\r\n<!--{ Template cached: carlziess3721@gmail.com, " . date('Y-m-d H:i:s') . " }-->\r\n";
            if($autoCompile===true)
            {
                $prefix = serialize($this->tpl_include_files) . "\r\n";
            }
            $str = $str . $postfix;
            file_put_contents($objfile, $prefix . $str);
            chmod($objfile,0770);
        }
        return $str;
    }

    protected function parse(&$str)
    {
        static::compile_layouts($str);
        $this->compile_includes($str);
        static::compile_yields($str);
        static::compile_section($str);
        $str = str_replace('{CR}', "<?php echo \"\\r\";?>", $str);
        $str = str_replace('{LF}', "<?php echo \"\\n\";?>", $str);
        $str = preg_replace("/\{if\s+(.+?)\}/", "<?php if(\\1) { ?>", $str);
        $str = preg_replace("/\{else\}/", "<?php } else { ?>", $str);
        $str = preg_replace("/\{elseif\s+(.+?)\}/", "<?php } elseif (\\1) { ?>", $str);
        $str = preg_replace("/\{\/if\}/", "<?php } ?>", $str);                       
        $str = preg_replace_callback("/\{loop\s+(\S+)\s+(\S+)\}/", function($r){ 
            return $this->addquote("<?php if(isset({$r[1]}) && is_array({$r[1]})) foreach({$r[1]} as {$r[2]}) { ?>");
        }, $str);                                                                    
        $str = preg_replace_callback("/\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}/", function($r){
            return $this->addquote("<?php if(isset({$r[1]}) && is_array({$r[1]})) foreach({$r[1]} as {$r[2]}=>{$r[3]}) { ?>");
        }, $str);                                                                    
        $str = preg_replace("/\{\/loop\}/", "<?php } ?>", $str);                     
        $str = preg_replace("/\{url\(([^}]+)\)\}/", "<?php echo U(\\1);?>", $str); 
        $str = preg_replace("/\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\s*\(([^{}]*)\))\}/", "<?php echo \\1;?>", $str);
        $str = preg_replace("/\{\\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\(([^{}]*)\))\}/", "<?php echo \$\\1;?>", $str); 
        $str = preg_replace("/\{(\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/", "<?php echo \\1;?>", $str);
        $str = preg_replace_callback("/\{(\\$[a-zA-Z0-9_\.\[\]\'\"\$\x7f-\xff]+)\}/", function($r){
            return $this->addquote("<?php echo {$r[1]};?>");                         
        }, $str);                                                                    
        $str = preg_replace_callback("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\x7f-\xff][+\-\>\$\'\"\,\[\]\(\)a-zA-Z0-9_\x7f-\xff]+)\}/s", function($r){
            return $this->addquote("<?php echo {$r[1]};?>");                         
        }, $str);                                                                    
        $str = preg_replace("/\{([A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*)\}/", "<?php echo \\1;?>", $str); 
        $str = preg_replace("/\{([a-zA-Z0-9_]*::?\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/", "<?php echo \\1;?>", $str);
        $str = preg_replace_callback("/\{([a-zA-Z0-9_]*::?\\\$[a-zA-Z0-9_\.\[\]\'\"\$\x7f-\xff]+)\}/", function($r){
            return $this->addquote("<?php echo {$r[1]};?>");                         
        }, $str);     
        $str = preg_replace("/\?\>\s*\<\?php[\r\n\t ]*/", "", $str);             
        $str = preg_replace('/^[\r\n]+/', '', $str);                             
        $this->removeComments($str);                                             
        $str = preg_replace('/{\*(.*)\*}/msU', "<!--{ \\1 }-->", $str);          
        if ($this->needClear===true)                                             
        {                                                                        
            $str = preg_replace(
                [                                           
                    '/<!--[^!]*-->/',                                                
                    '/\/\*.+?\*\//',                                                 
                    '/[^:|"]\/\/[^\r\n]*[\r\n]/is',                                  
                    '/^[\?|php]\s*([,;:\{\}])\s*/',                                  
                    '/[\n\r\t]/'                                                     
                ],                                                               
                [                                                               
                    '',                                                              
                    '',                                                              
                    '',                                                              
                    '\\1',                                                           
                    ''                                                               
                ],                                                                   
                $str
            );                                                               
        }                                                                        
        $str = trim($str);  
    }

    protected function addquote($var)
    {
        preg_match_all("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", $var, $vars);
        foreach($vars[1] as $k => $v)
        {
            if(is_numeric($v))
            {
                $var = str_replace($vars[0][$k], "[$v]", $var);
            }else{
                $var = str_replace($vars[0][$k], "['$v']", $var);
            }
        }
        return str_replace("\\\"", "\"", $var);
    }

    protected function removeComments(&$str)
    {
        if($this->needClear === true)
        {
            $this->compile_clean($str);
        }
        $str = str_replace(['<?php exit?>', '<?php exit;?>'], ['', ''], $str); 
        $str = preg_replace("/([\r\n]+)[\t ]+/s", "\\1", $str);
        $str = preg_replace("/[\t ]+([\r\n]+)/s", "\\1", $str); 
        $str = preg_replace("/\<\!\-\-\s*\{(.+?)\}\s*\-\-\>/s", "{\\1}", $str);
        $str = preg_replace("/\<\!\-\-\s*\-\-\>/s", "", $str); 
        $str = preg_replace("/\<\!\-\-\s*[^\<\{]*\s*\-\-\>/s", "", $str);
        $str = preg_replace("/\<\!\-\-\s*[^\<\{]*\s*\-\-\>/s", "", $str);
    }

    protected function compile_clean(&$str)
    {
        if(preg_match_all("|<script[^>]*>(.*)</script>|Usi", $str, $tvar))
        {
            foreach($tvar[1] as $k => $v)
            {
                if($v!=='' || strlen($v)) 
                {
                    $v = preg_replace("/\/\/\s*[a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/", "", $v); 					
                    $v = preg_replace("/\/\*[^\/]*\*\//s", "", $v);					
                    $str = str_replace($tvar[1][$k], $v, $str);
                }
            }
        }
        if(preg_match_all("|<style[^>]*>(.*)</style>|Usi", $str, $tvar))
        {
            foreach($tvar[1] as $k => $v)
            {
                if($v!=='' || strlen($v)) 
                {
                    $v = preg_replace("/\/\*[^\/]*\*\//s", "", $v);	
                    $str = str_replace($tvar[1][$k], $v, $str);
                }
            }
        }
    }

    static protected function compile_layouts(&$str)
    {
        if(false === strpos($str, '@layout')) return;
        $lines = preg_split("/(\r?\n)/", $str);
        $pattern = static::matcher('layout');
        $lines[] = preg_replace($pattern, '$1@include$2', $lines[0]);
        $str = implode("\r\n", array_slice($lines, 1));
    }

    static protected $yields = [];

    static protected function compile_yields(&$str)
    {
        $pattern = '/@yield\s*\([\'|"](.*)[\'|"]\)/';
        if(preg_match_all($pattern,$str,$match))
        {
            foreach($match[1] as $k => $v)
            {
                static::$yields[$v] = $match[0][$k];
            }
        }
    }

    static protected function compile_section(&$str)
    {
        $pattern = '/\s*@section\s*\([\'|"](.*)[\'|"]\)(.+)\s*@endsection\s*/iUs';
        if(preg_match_all($pattern,$str,$match))
        {
            if(count(static::$yields))
            {
                $str = str_replace($match[0],'',$str);
                foreach($match[1] as $k => $v)
                {
                    foreach(static::$yields as $m => $n)
                    {
                        if(false !== ($key = array_search($m,$match[1])) && false!==strpos($match[2][$k], $n))
                        {
                            $match[2][$k] = str_replace($n,$match[2][$key],$match[2][$k]);
                        }
                    }
                    if(isset(static::$yields[$v]))
                    {
                        $str = str_replace(static::$yields[$v],$match[2][$k],$str);
                    }
                }
            }
        }
        $str = str_replace(static::$yields,'',$str);
        static::$yields = NULL;
    }

    protected function compile_includes(&$str)
    {
        $countSubTpl = preg_match_all('/\@include\s*\((.*)\)/', $str, $tvar);
        while($countSubTpl > 0)
        {
            foreach($tvar[1] as $k => $subfile)
            {
                eval("\$subfile = $subfile;");
                if(is_file($this->templateDir . $subfile .VIEW_EXT))
                {
                    $findfile = $this->templateDir . $subfile . VIEW_EXT;
                }elseif(is_file($subfile)){
                    $findfile = $subfile;
                }elseif(is_file($subfile . VIEW_EXT)){
                    $findfile = $subfile .VIEW_EXT;
                }elseif(is_file($this->templateDir . $subfile)){
                    $findfile = $this->templateDir . $subfile;
                }else{
                    $findfile = '';
                }
                if(!empty($findfile))
                {
                    $subTpl = file_get_contents($findfile);
                    static::compile_layouts($subTpl);
                    $this->tpl_include_files[] = $findfile;
                }else{ 
                    $subTpl = 'SubTemplate not found:' . $subfile;
                }
                $str = str_replace($tvar[0][$k], $subTpl, $str);
            }
            $countSubTpl = preg_match_all('/\@include\s*\((.*)\)/', $str, $tvar);
        }
    }

    static public function matcher($function)
    {
        return '/(\s*)@'.$function.'(\s*\(.*\))/';
    }

}
