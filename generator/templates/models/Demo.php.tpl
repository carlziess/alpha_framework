<?php
/**
 * @name DemoModel
 * @desc Demo数据获取类, 可以访问数据库，文件，其它系统等
 * 此文件中的代码只用作示例
 * @author {&$AUTHOR&}
 */
class DemoModel
{

    protected $database      = 'demo';
    protected $table         = 'demo';
    
    public function getContent($id = 0)
    {
        if(0 == $id) return [];
        return ['code'=>200,'data'=>'111111111'];
    }
    
    public function example($username = '', $id = '')
    {
        if('' == $username || '' == $id) return [];
        $sql1 = 'SELECT * FROM '.$this->database.'.'.$this->table.' WHERE `username` = ?;';
        $sql2 = 'SELECT id FROM '.$this->database.'.'.$this->table.' LIMIT 100;';
        $sql3 = 'INSERT INTO `demo` (`id`, `username`,`realname`, `mobile`, `bindmobile`, `deleted`) VALUES  (?, ?, ?, ?, ? ,?);';
        $sql4 = 'UPDATE `demo` SET `orgname`=?,`nickname`=? WHERE `id`=?;';
        $sql5 = 'DELETE FROM `demo`.`demo` WHERE `id`=?;';
        $sql6 = 'SELECT * FROM '.$this->database.'.'.$this->table.' WHERE `username` = ?;';
        $sql7['count'] = 'SELECT count(*) as total FROM '.$this->database.'.'.$this->table.';';
        $sql7['sql'] = 'SELECT * FROM '.$this->database.'.'.$this->table.';';
        //return DB::getInstance('master')->getRow('s',$sql1,['username'=>$username]) ? : false; 
        //return DB::getInstance('slave')->getRow('',$sql2,[]) ? : false; 
        //return DB::getInstance('slave')->getRow_bak('',$sql2,[]) ? : false; 
        //return DB::getInstance('master')->insert('isssss',$sql3,['id'=>55,'username'=>'testname','realname'=>'realname','mobile'=>'131212','bindmobile'=>'1','deleted'=>'0']) ? : false; 
        //return DB::getInstance('master')->update('sss',$sql4,['orgname'=>'我是你二大爷','nickname'=>'我是你大爷','id'=>$id]);
        //return DB::getInstance('master')->delete('s',$sql5,['id'=>'55']);
        //return DB::getInstance('slave')->getOne('',$sql1,['username'=>$username]); 
        //$data['runId'] = DB::getInstance('slave')->runId(); 
        //return $data;
        //return DB::getInstance('slave')->getList('s',$sql6,['username'=>$username],1,100); 
        return DB::getInstance('slave')->getList('',$sql7,[],3,100); 
    }

    public function transcation()
    {
        $sql3 = "INSERT INTO `demo` (`id`, `username`,`realname`, `mobile`, `bindmobile`, `deleted`) VALUES  (?, ?, ?, ?, ? ,?);";
        $sql4 = "UPDATE `demo` SET `orgname`=?,`nickname`=? WHERE `id`=?;";
        $id = '000155A28474FBB99736CF9E3A7C079E';
        try
        {
            DB::getInstance('master')->beginTransaction(); 
            DB::getInstance('master')->insert('isssss',$sql3,['id'=>55,'username'=>'testname','realname'=>'realname','mobile'=>'131212','bindmobile'=>'1','deleted'=>'0']); 
            DB::getInstance('master')->update('sss',$sql4,['orgname'=>'我是你二大爷3','nickname'=>'我是你大爷3','id'=>$id]);
        }catch(Exception $e){
            DB::getInstance('master')->rollBack(); 
            throw $e;
        }
        DB::getInstance('master')->commit(); 
        return true;
    }


}
