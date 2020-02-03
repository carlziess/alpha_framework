<?php
if(!extension_loaded('yaf'))die('Not Install Yaf');  
define('APPLICATION_PATH', dirname(dirname(__FILE__)));
Yaf_Loader::import(APPLICATION_PATH . '/Init.php');   
$application = new Yaf_Application( APPLICATION_PATH . '/conf/application.ini');
$application->bootstrap()->run();


?>
