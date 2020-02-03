可以按照以下步骤来部署和运行程序:
1.请确保机器{&$DEV_PC&}已经安装了Yaf框架, 并且已经加载入PHP;
2.把{&$APP_NAME&}目录Copy到Nginx服务器中，需要保证apps文件夹和系统library（框架部分）
在同级目录，并且需要将生成的应用拷贝到apps/{&$APP_NAME&},并将应用的webroot指向{&$APP_NAME&}的www目录
3.nginx配置示例
server {
    listen 80;
    server_name {&$APP_NAME&}.com;
    access_log  /logpath/{&$APP_NAME&}.access.log  main;
    error_log  /logpath/{&$APP_NAME&}.error.log;
    root /data/codes/alpha_framework/apps/{&$APP_NAME&}/www;
    index  inside.php index.htm;
    try_files $uri $uri/ /index.php$is_args$args;
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

}

4.需要在php.ini里面启用如下配置，生产的代码才能正确运行：
	yaf.environ="product"
5.重启Webserver;
6.访问http://{&$APP_NAME&}.com,出现{"code":0,"data":"Hellow Word!"}, 表示运行成功,否则请查看php错误日志;
