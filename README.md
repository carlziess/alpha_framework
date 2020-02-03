# Alpha Framework

Alpha is a web application framework base on yaf, I believe development must be an enjoyable.
You can send comments, patches, questions [here on gitlab](https://github.com/carlziess/alpha_framework/issues).
Also you can give this project your star or the new features.
Do not only talk without actions.Using your codes tell us what's the better.
# Table of contents

1. [Requirements](#requirements)
   * [Installation](#installation)
   * [Compile Yaf In Linux ](#compile-yaf-in-linux)

2. [Documents And Examples](#documents-and-examples)
   * [Directory Trees](#directory-trees)
   * [Generate an App](#generate-an-app)
   * [Nginx Configures](#nginx-configures)
   * [Default Controller](#default-controller)
   * [Default Model](#default-model)
   * [Run Your First Application](#run-your-first-application)


-----

## Requirements
- PHP 7.0 +
- Yaf 3.0.5
- PDO (PDO_MySQL)
- MySQLi
- MySQLnd (The API Extensions must be MySQLi)
- Redis

## Installation
Yaf is a PECL extension, thus you can simply install it by:

```
$pecl install yaf
```
### Compile Yaf in Linux
```
$/path/to/phpize
$./configure --with-php-config=/path/to/php-config
$make && make install
```
## Documents And Examples
Yaf manual could be found at: http://www.php.net/manual/en/book.yaf.php

### Directory Trees

```
├── Bootstrap.php
├── conf	/* Application's configure. */
├── controllers
├── helper	
├── Init.php
├── library	
├── logs	/* Debug Logs  */
├── models
├── modules
├── plugins
├── public
├── readme.txt
├── service
└── tools 

```
### Generate An App

```
$/generator/makeapp [ApplicationPath]
```
### Nginx Configures

```
server {
    listen 80;
    server_name demo.com;
    access_log  /data/web_log/nginx_log/demo.access.log  moss;
    error_log  /data/web_log/nginx_log/demo.error.log error;
	root /data/web_data/webroot/apps/demo/public;
    index  index.php;
    try_files $uri $uri/ /index.php$is_args$args;
    location ~ \.php$ {
		fastcgi_pass 127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Default Controller

```
<?php                                                                                                                                                                                                   
/**                                                                                                                      
 * @name IndexController                                                                                                 
 * @author lizhenglin                                                                                                     
 * @desc default controller                                                                                                     
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php           
 */                                                                                                                      
class IndexController extends Controller                            
{                                                                                                                        
                  
    //beforeInit controller. same as __construct
    public function beforeInit()                                                                                               
    {                                     
        //disable view layer.
        Yaf\Registry::set('responseType','json');                               
    }                                                                                                                    
                                                                                                                         
    /**                                                                                                                  
     * default action                                                                                                          
     */                                                                                                                  
    public function indexAction()                                                                                        
    {                                                                                                                    
        $args = 'application/json' === 
                Request::getInstance()->getContentType() ? json_decode(Request::getInstance()->getRawBody(), true) : $this->getRequest()->getPost(); 
        $args = !empty($args) ? : 'Hellow Word!';                            
        Response::getInstance()->setHeader('p3p', "CP='CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR'");
        Response::getInstance()->send(['code'=>200,'data'=>['result'=>$args],'message'=>'']);                                                                                 
    } 
}

?>

```
### Default Model

```
<?php
/**                                                                                  
 * @name DemoModel                                                                   
 * @desc Default Model                          
 * @author lizhenglin                                                                 
 */                                                                                  
class DemoModel                                                                      
{                                                                                    
                                                                                                                                   
    public function example($username = '', $id = '')                                
    {                                                                                
        if('' == $username || '' == $id) return [];  
        $sql = 'SELECT id,name FROM user_table WHERE `username` = ? AND `id` = ?;';
        return DB::getInstance('master')->getRow('ss',$sql,['username'=>$username,'id'=>1]) ? : [];
    }
}

?>

```

### Run Your First Application
http://demo.com



