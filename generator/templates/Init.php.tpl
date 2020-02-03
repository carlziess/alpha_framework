<?php
/**
 * @name Init
 * @author {&$AUTHOR&}
 * @desc 在开发阶段设置E_ALL，并且DEBUG为true
 */
error_reporting(E_ALL); 
//error_reporting(E_ALL^E_NOTICE); 
define('DEBUG',true);
define('MB_STRING',(int)function_exists('mb_get_info'));

?>
