server {
    listen 80;
    server_name {&$NGINX_SERVER_NAME&};
    access_log  /data/web_log/nginx_log/{&$APP_NAME&}.access.log  moss;
    error_log  /data/web_log/nginx_log/{&$APP_NAME&}.error.log error;
    root {&$APP_PATH&}/public/;
    index  index.php;
    try_files $uri $uri/ /index.php$is_args$args;
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

