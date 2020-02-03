<?php
/*================================================================
*   File Name：Cache.php
*   Author：carlziess, lizhenglin@g7.com.cn
*   Create Date：2016-02-15 13:49:03
*   Description：
================================================================*/
class Cache
{
    static public $drivers = [];
    static public $registrar = [];

	static public function getInstance($instance = 'master')
    {
        $config = (new \Yaf\Config\Ini(APPLICATION_PATH. DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'cache.ini'))->cache;
        $driver = !empty($config) && isset($config['driver']) ? $config['driver'] : 'redis';
		if(!isset(static::$drivers[$driver]))
        {
            if(!isset($config[$driver][$instance]))
            {
                throw new \Exception("Cache connections is not defined for [$driver-$instance]", 400);
            }
			static::$drivers[$driver][$instance] = static::factory($driver, $instance);
		}
		return static::$drivers[$driver][$instance];
	}
	
	static protected function factory($driver,$instance)
	{
		if(isset(static::$registrar[$driver]))
		{
			$resolver = static::$registrar[$driver];
			return $resolver();
		}
		switch ($driver)
		{
			case 'file':
				return new Cache\Instance\File(\Yaf_Application::app()->getConfig()->get('cache.path'));
			case 'redis':
				return new \Cache\Instance\Redis(\Cache\Connector\RedisConnection::getInstance($instance));
			case 'database':
				return new Cache\Instance\Database(\Yaf_Application::app()->getConfig()->get('cache.key'));
			default:
				throw new \Exception("Cache driver {$driver} is not supported.",-1001);
		}
	}

	
	static public function extend($driver, Closure $resolver)
	{
		static::$registrar[$driver] = $resolver;
	}
    	

	static public function __callStatic($method, $parameters)
	{
		return call_user_func_array(array(static::getInstance(), $method), $parameters);
    }

}
