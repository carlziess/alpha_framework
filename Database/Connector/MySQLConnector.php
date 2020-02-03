<?php
/*================================================================
*   File Name：MySQLConnector.php
*   Author：carlziess, lizhenglin@g7.com.cn
*   Create Date：2016-01-25 14:35:54
*   Description：
================================================================*/
namespace Database\Connector;
use PDO;
class MySQLConnector 
{

	protected $options = array(
			PDO::ATTR_CASE => PDO::CASE_NATURAL,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
			PDO::ATTR_STRINGIFY_FETCHES => false,
			PDO::ATTR_EMULATE_PREPARES => false,
	);

	protected function options($config)
	{
		$options = (isset($config['options'])) ? $config['options'] : array();

		return $options + $this->options;
	}
	
	public function connect($config)
	{
		$dsn = "mysql:host={$config['host']};dbname={$config['database']}";
		if (!empty($config['port']))
		{
			$dsn .= ";port={$config['port']}";
		}
		if (isset($config['unix_socket']))
		{
			$dsn .= ";unix_socket={$config['unix_socket']}";
		}

		$connection = new PDO($dsn, $config['username'], $config['password'], $this->options($config));
		$collation = NULL;
		$charset = $config['charset'];
		$names = "set names '$charset'".( ! is_null($collation) ? " collate '$collation'" : '');
		$connection->prepare($names)->execute();
		if (isset($config['strict']) && $config['strict'])
		{
			$connection->prepare("set session sql_mode='STRICT_ALL_TABLES'")->execute();
		}
		return $connection;
	}

}

