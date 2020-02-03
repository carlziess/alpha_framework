<?php
/*================================================================
*  File Name：DB.php
*  Author：carlziess, chengmo9292@126.com
*  Create Date：2016-06-20 10:55:09
*  Description：
===============================================================*/
use \Database\MySQLi;
class DB 
{
	static public $connections = [];

	static public function getInstance($instance = 'master')
	{
        $config = (new \Yaf\Config\Ini(APPLICATION_PATH. DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'database.ini'))->database;
        $connection = !empty($config) && isset($config['driver']) ? $config['driver'] : 'mysql';
		if(!isset(static::$connections[$connection][$instance]))
        {
			if(empty($config->{$connection}[$instance]))
			{
				throw new \Exception("Database connection is not defined for [$connection-"."$instance].");
            }
            $config = $config->{$connection}[$instance];
			$config = [
				'driver' => $connection,
				'host' => $config->get('host'),
				'port' => $config->get('port'),
				'charset' => $config->get('charset'),
				'prefix' => $config->get('prefix'),
				'username' => $config->get('username'),
				'password' => $config->get('password'),
				'database' => $config->get('database'),
            ];
			static::$connections[$connection][$instance] = new MySQLi($config);
        }
		return static::$connections[$connection][$instance];
	}

    static public function __callStatic($method,$parameters)
    {
		return call_user_func_array([static::getInstance(),$method],$parameters);
	}

    
}
