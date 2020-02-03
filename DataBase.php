<?php
/*================================================================
*   File Name：DataBase.php
*   Author：carlziess, lizhenglin@g7.com.cn
*   Create Date：2016-01-24 00:56:09
*   Description：
================================================================*/
use \Database\Connector\MySQLConnector;
use \Database\Instance\MySQL;
class DataBase 
{
	static public $connections = [];

	static public $registrar = [];


	static public function getInstance($instance = 'master')
	{
        $config = (new \Yaf\Config\Ini(APPLICATION_PATH. DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'database.ini'))->database;
        $connection = !empty($config) && isset($config['driver']) ? $config['driver'] : 'mysql';
		if(!isset(static::$connections[$connection]))
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
            //@todo 暂时不考虑非MySQL Instance
			static::$connections[$connection][$instance] = new MySQL(static::connect($config), $config);
		}
		return static::$connections[$connection][$instance];
	}

	static protected function connect($config)
	{
		return static::connector($config['driver'])->connect($config);
	}

    static protected function connector($driver)
    {
		if(isset(static::$registrar[$driver]))
		{
			$resolver = static::$registrar[$driver]['connector'];
			return $resolver();
		}
		switch($driver)
		{
			case 'mysql':
                return new MySQLConnector;
			default:
				return new MySQLConnector;
		}
		throw new \Exception("Database driver [$driver] is not supported.");
	}
	
    static public function extend($name, Closure $connector, $schema = null)
    {
		static::$registrar[$name] = compact('connector','schema');
	}

    static public function __callStatic($method, $parameters)
    {
		return call_user_func_array(array(static::getInstance(), $method), $parameters);
	}

    
}
