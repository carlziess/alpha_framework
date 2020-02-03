<?php
/*================================================================
*   File Name：RedisConnection.php
*   Author：carlziess, lizhenglin@g7.com.cn
*   Create Date：2016-02-15 18:04:13
*   Description：
================================================================*/
namespace Cache\Connector;
class RedisConnection 
{
	protected $conf;
	protected $connection;
	static protected $databases = [];	
	public function __construct($conf)
	{
        $this->conf = $conf;
		$this->conf['database'] = isset($this->conf['database']) ? $this->conf['database'] : 0;
		if(isset($this->conf['prefix'])){
			$this->conf['prefix'] .= '_';
		}
	}

    static public function getInstance($instance = 'master')
    {
        if(!isset(static::$databases[$instance]))
        {
            $config = (new \Yaf\Config\Ini(APPLICATION_PATH. DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'cache.ini'))->cache->toArray();
            if (!isset($config['driver']) || $config['driver'] != 'redis') {
                throw new \Exception("Redis driver [$instance] is not defined.");
            }

            if(!isset($config['redis']['master']) || empty($config['redis']['master'])) {
                throw new \Exception("Redis database [$instance] is not defined.");
            }
            static::$databases[$instance] = new static($config['redis']['master']);
        }
        return static::$databases[$instance];
    }
	
	protected function connect()
    {
		if(!is_null($this->connection)) return $this->connection;
        $this->connection = new \Redis();
		if(isset($this->conf['sock'])){
			$this->connection->connect($this->conf['sock']);
        }else{
            $func = $this->conf['persistent'] ? 'pconnect' : 'connect';
            try
            {
			    $this->connection->$func($this->conf['host'], $this->conf['port'], $this->conf['timeout']);
            }catch(Exception $e){
                throw new Exception($e->getMessage(),$e->getCode());
            }
		}
		if(isset($this->conf['password'])) {
			$this->connection->auth($this->conf['password']);
		}
		$this->connection->select($this->conf['database']);
		if(isset($this->conf['prefix'])) {
			$this->connection->setOption(\Redis::OPT_PREFIX, $this->conf['prefix']);
        }
		return $this->connection;
	}

	public function getConnection()
	{
		return $this->connect();
	}
	
	public function __call($method, $parameters)
	{
		return call_user_func_array([$this->connect(),$method], $parameters);
	}
	
	static public function __callStatic($method, $parameters)
	{
		return call_user_func_array([static::db(),$method], $parameters);
	}
	
	public function __destruct()
	{
		if ($this->connection)
		{
			$this->connection->close();
		}
	}

}
