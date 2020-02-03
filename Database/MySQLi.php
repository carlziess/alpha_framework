<?php
/*================================================================
*  File Name：MySQLi.php
*  Author：carlziess, chengmo9292@126.com
*  Create Date：2016-06-19 17:57:22
*  Description：
*  About the types and the first you should know
*  Character Description
*  i    corresponding variable has type integer
*  d    corresponding variable has type double
*  s    corresponding variable has type string
*  b    corresponding variable is a blob and will be sent in packets
*  Exception:
*  code => 1292 : parameters types error 
===============================================================*/
namespace Database;
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
class MySQLi
{
    private $connections;
    private $transaction = [MYSQLI_TRANS_START_READ_ONLY,MYSQLI_TRANS_START_READ_WRITE,MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT];
    public function __construct($config)
	{
        $host = isset($config['host']) && !empty($config['host']) ? $config['host'] : '127.0.0.1';
        $username = isset($config['username']) && !empty($config['username']) ? $config['username'] : 'root';
        $password = isset($config['password']) && !empty($config['password']) ? $config['password'] : '';
        $database = isset($config['database']) && !empty($config['database']) ? $config['database'] : '';
        $port = isset($config['port']) && !empty($config['port']) ? $config['port'] : '3306';
        $charset = isset($config['charset']) && !empty($config['charset']) ? $config['charset'] : 'utf8';
        $this->connections = new \mysqli($host,$username,$password,$database,$port);
        if(mysqli_connect_errno())
            throw new \Exception('MySQLi Connect Failed',mysqli_connect_errno());
        $this->connections->set_charset($charset);
    }

    public function getOne($types = '',$sql = '', array $parameters = [])
    {
        if('' == $sql) 
            throw new \Exception('parameters is not valid!',403);
        $stmt = $this->connections->prepare($sql);
        if(false == $stmt)
            throw new \Exception($this->connections->error,$this->connections->errno);
        try
        {
            if(0 < count($parameters))
            {
                $refArgs[] = $types;
                foreach($parameters as &$v) {$refArgs[] = &$v;}
                @(new \ReflectionMethod('mysqli_stmt', 'bind_param'))->invokeArgs($stmt,$refArgs);
            }
            $stmt->execute();
            $obj = $stmt->get_result();
            $data = $obj->fetch_assoc() ? : [];
        }catch(\mysqli_sql_exception $e){
            throw new \Exception($e->getMessage(),$e->getCode());
        }
        return $data;
    }

    public function getRow($types = '',$sql = '', array $parameters = [])
    {
        if('' == $sql) 
            throw new \Exception('sql statement is not valid!',403);
        $stmt = $this->connections->prepare($sql);
        if(false == $stmt)
            throw new \Exception($this->connections->error,$this->connections->errno);
        try
        {
            if(0 < count($parameters))
            {
                $refArgs[] = $types;
                foreach($parameters as &$v) {$refArgs[] = &$v;}
                @(new \ReflectionMethod('mysqli_stmt', 'bind_param'))->invokeArgs($stmt,$refArgs);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $total = $result->num_rows;
            //if($total > 1000)
            //    throw new \Exception('max rows limit 1000',403);
        }catch(\mysqli_sql_exception $e){
            throw new \Exception($e->getMessage(),$e->getCode());
        }
        $data = [];
        for($i = 1; $i <= $total; $i++)
        {
            $data[] = $result->fetch_assoc(); 
        }
        $stmt->close();
        return $data;
    }
    
    public function getList($types = '', array $sql = [], array $parameters = [], $pageNo, $pageSize) 
    {  
        if(!isset($sql['count']) || empty($sql['count']) || !isset($sql['sql']) || empty($sql['sql'])) 
            throw new \Exception('sql statement is not valid!',403);
        $pageNo = 0 >= (int)$pageNo ? 1 : $pageNo;
        $pageSize = 0 >= (int)$pageSize ? 10 : $pageSize;
        //if((int)$pageSize > 1000)
        //    throw new \Exception('max pageSize limit 1000',403);
        $total = self::getTotal($types,$sql['count'],$parameters);
        if(0 == $total)
            return ['total'=>0,'pageNo'=>$pageNo,'pageSize'=>$pageSize,'result'=>[]];
        $tail = strripos($sql['sql'],';');
        $pages = ceil($total / $pageSize);  
        if($pageNo >= $pages) $pageNo = $pages;  
       	$offset = ($pageNo - 1) * $pageSize;  
        $sql = 0 < $tail ? substr($sql['sql'],0,$tail).' LIMIT '.$offset.','.$pageSize : $sql['sql'].' LIMIT '.$offset.','.$pageSize ;
        $stmt = $this->connections->prepare($sql);
        if(false == $stmt)
            throw new \Exception($this->connections->error,$this->connections->errno);
        try
        {
            if(0 < count($parameters))
            {
                $refArgs[] = $types;
                foreach($parameters as &$v) {$refArgs[] = &$v;}
                @(new \ReflectionMethod('mysqli_stmt', 'bind_param'))->invokeArgs($stmt,$refArgs);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $rows = $result->num_rows;
        }catch(\mysqli_sql_exception $e){
            throw new \Exception($e->getMessage(),$e->getCode());
        }
        $data['total'] = $total;
        $data['pageNo'] = $pageNo;
        $data['pageSize'] = $pageSize;
        for($i = 1; $i <= $rows; $i++)
        {
            $data['result'][] = $result->fetch_assoc(); 
        }
        $stmt->close();
        return $data;
    }  

    public function insert($types = '',$sql = '', array $parameters = [])
    {
        $stmt = $this->bindParams($types,$sql,$parameters);
        if(false == $stmt)
            throw new \Exception('Sql statements error,parameters bind has failed.',403);
        try
        {
            $stmt->execute();
        }catch(\mysqli_sql_exception $e){
            throw new \Exception($e->getMessage(),$e->getCode());
        }
        $data = ['insert_id'=>$stmt->insert_id,'affected_rows'=>$stmt->affected_rows];
        $stmt->close();
        return $data;
    }

    public function update($types = '',$sql = '', array $parameters = [])
    {
        $stmt = $this->bindParams($types,$sql,$parameters);
        if(false == $stmt)
            throw new \Exception('Sql statements error,parameters bind has failed.',403);
        try
        {
            $stmt->execute();
        }catch(\mysqli_sql_exception $e){
            throw new \Exception($e->getMessage(),$e->getCode());
        }
        $data = ['affected_rows'=>$stmt->affected_rows];
        $stmt->close();
        return $data;
    }

    public function delete($types = '',$sql = '', array $parameters = [])
    {
        $stmt = $this->bindParams($types,$sql,$parameters);
        if(false == $stmt)
            throw new \Exception('Sql statements error,parameters bind has failed.',403);
        try
        {
            $stmt->execute();
        }catch(\mysqli_sql_exception $e){
            throw new \Exception($e->getMessage(),$e->getCode());
        }
        $data = ['affected_rows'=>$stmt->affected_rows];
        $stmt->close();
        return $data;
    }

    public function beginTransaction()
    {
        return $this->connections->begin_transaction();
    }

    public function rollBack()
    {
        return $this->connections->rollback();
    }

    public function commit()
    {
       return $this->connections->commit();  
    }

    private function bindParams($types = '',$sql = '', $parameters = []) 
    {
        $stmt = $this->connections->prepare($sql);
        if(false == $stmt)
            throw new \Exception($this->connections->error,$this->connections->errno);
        if(0 < count($parameters))
        {
            $refArgs[] = $types;
            foreach($parameters as $k=>&$v){ $refArgs[] = &$v;}
            @(new \ReflectionMethod('mysqli_stmt', 'bind_param'))->invokeArgs($stmt,$refArgs);
        }
        return $stmt;
    }
    
    public function getTotal($types = '',$sql = '', array $parameters = [])
    {
        if('' == $sql || false == stripos($sql,'count(*) as total')) return 0; 
        $stmt = $this->connections->prepare($sql);
        if(false == $stmt)
            throw new \Exception($this->connections->error,$this->connections->errno);
        try
        {
            if(0 < count($parameters))
            {
                $refArgs[] = $types;
                foreach($parameters as &$v) {$refArgs[] = &$v;}
                @(new \ReflectionMethod('mysqli_stmt', 'bind_param'))->invokeArgs($stmt,$refArgs);
            }
            $stmt->execute();
            $obj = $stmt->get_result();
            $result = $obj->fetch_assoc();
        }catch(\mysqli_sql_exception $e){
            throw new \Exception($e->getMessage(),$e->getCode());
        }
        return $result['total'] > 0 ? $result['total'] : 0;
    }

    //Warning:Do not fucking use this,if you don't know what you want do.
    public function query($sql = '')
    {
        if('' == $sql) throw new \Exception('Sql statements is not valid!',403);
        $data = [];
        $query = $this->connections->query($sql);
        if ($query) {
            while($data[] = $query->fetch_assoc());
        }
        $this->connections->next_result();
        return array_filter($data); 
    }

    public function runId()
    {
        return ['pid'=>getmypid(),'threadId'=>$this->connections->thread_id]; 
    }


}

?>
