<?php
/*================================================================
*   File Name：MySQL.php
*   Author：carlziess, lizhenglin@g7.com.cn
*   Create Date：2016-01-25 14:35:22
*   Description：
================================================================*/
namespace Database\Instance;
use PDO,PDOStatement,PDOException;
class MySQL
{
	private $pdo;
	private $config;

	public function __construct(PDO $pdo,$config)
	{
		$this->pdo = $pdo;
		$this->config = $config;
	} 
	
    private function execute($sql, $params) 
    {  
        try{  
            $stmt = $this->pdo->prepare($sql); 
            if($params!==null) {  
                if(is_array($params) || is_object($params)) {  
                    $i=1;  
                    foreach($params as $param){  
                        $stmt->bindValue($i++,$param);  
                    }  
                }else{  
                    $stmt->bindValue(1,$params);  
                }  
            }  
            if($stmt->execute()){  
                return $stmt;  
            }  
        }catch(PDOException $e){
			throw new PDOException($e);
		}
    } 

    public function getOne($sql, $params)
    {
        return self::execute($sql,$params)->fetchColumn(); 
    }
    
    public function getRow($sql, $params)
    {
        return self::execute($sql,$params)->fetch(PDO::FETCH_ASSOC); 
    }

    public function insert($sql, $params)
    {
        return self::execute($sql,$params) ? $this->pdo->lastInsertId() : FALSE; 
    } 
    
    public function delete($sql, $params)
    {
        return self::execute($sql,$params)->rowCount(); 
    }
 
    public function update($sql, $params) 
    {  
        return self::execute($sql,$params)->rowCount();  
    } 

    public function query($sql, $params) 
    {  
        return self::execute($sql,$params)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();  
    }

    public function rollBack()
    {  
        return $this->pdo->rollBack();  
    }

    public function commit() 
    {  
        return $this->pdo->commit();  
    }  
    
    /**
     * 分页
     * @param string $countSql
     * @param string $selectSql
     * @param array $params
     * @param integer $pageNo
     * @param integer $pageSize
     */
    public function page($countSql, $selectSql, $params = [], &$pageNo, $pageSize) 
    {  
        if($pageNo <= 0) $pageNo = 1; 
        if($pageSize <= 0) $pageSize = 15;
        $count = self::getOne($countSql,$params);  
        $pageCount = ceil($count / $pageSize);  
        if($pageNo > $pageCount) $pageNo = $pageCount;  
       	$offset = ($pageNo - 1) * $pageSize;  
       	if($offset<0) $offset = 0;
        $sql = $selectSql.' LIMIT '.$offset.','.$pageSize;  
        $rs = self::query($sql,$params) ? self::query($sql,$params) : [];
        return array('pageNo'=>$pageNo,'pageSize'=>$pageSize,'result'=>$rs,'total'=>$count); 
    }  

    public function driver()
    {
    	return $this->config['driver'];
    }
    
    public function __call($method,$parameters)
    {
    	echo 'QueryError:', var_export([$method,$parameters,date('Y-m-d H:i:s',time())],true);
    	return FALSE;
    }
}  

