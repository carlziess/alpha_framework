<?php
/*================================================================
 *  File Name：Singleton.php
 *  Author：carlziess, chengmo9292@126.com
 *  Create Date：2016-03-21 02:59:49
 *  Description：
 ===============================================================*/
trait Singleton
{                                                                                    
    /**                                                                              
     * 获取实例                                                                      
     * @return static                                                                
     */                                                                              
    static public function getInstance()                                             
    {                                                                                
        static $instance = [];                                                       
        $cls = get_called_class();                                                   
        if (!isset($instance[$cls]) || $instance[$cls] === null) {                   
            $instance[$cls] = new $cls;                                              
        }                                                                            
        return $instance[$cls];                                                      
    }                                                                                
} 
