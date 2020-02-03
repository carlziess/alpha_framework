<?php
/**
 * @name ErrorController
 * @desc 错误控制器,
 * 在发生未捕获的异常时刻被调用,每个应用都必须要有ErrorController.
 * DEBUG为true时程序异常时会显示详细的代码栈
 * @see http://www.php.net/manual/en/yaf-dispatcher.catchexception.php
 * @author {&$AUTHOR&}
 */
class ErrorController extends Yaf_Controller_Abstract 
{
    public function errorAction($exception) 
    {
        Yaf_Dispatcher::getInstance()->disableView();
        $code = $exception->getCode();
        $message = $exception->getMessage();
        if(true === DEBUG)
        {
            echo '<pre/>';
            echo 'code:'.$code.'<br/>';
            echo 'file:'.$exception->getFile().'<br/>';
            echo 'line:'.$exception->getLine().'<br/>';
            echo 'message:'.$message.'<br/>';
            echo 'detail:<br/>';
            echo var_export(json_decode(json_encode($exception->getTrace()),true));
            die();
        }
		switch ($code) 
        {                                         
        case YAF_ERR_NOTFOUND_MODULE:                                            
        case YAF_ERR_NOTFOUND_CONTROLLER:                                        
        case YAF_ERR_NOTFOUND_ACTION:                                            
        case YAF_ERR_NOTFOUND_VIEW:                                              
            $code = 404;
            $message   = $message ? : '404 Not Found'; 
            break; 
        case 404:                                                              
            $message   = $message ? : '404 Not Found'; 
            break;                                              
        case 401:                                               
            $message   = $message ? : '401 Unauthorized';                                       
            break;                                              
        case 403:   
            $message   = $message ? : '403 Forbidden';                                          
            break;                                                  
        case 500:   
            $message   = $message ? : 'HTTP/1.1 500 Internal Server Error';                            
            break;                                                  
        default :                                                   
            $message   = $message ? : 'HTTP/1.1 500 Internal Server Error';
            break;                                                                   
        }
        header('marker_code:'.$code);
        exit(json_encode(['code' => $code,'message' => $message]));
	}

}
