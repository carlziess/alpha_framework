<?php

abstract class Log_Driver_Abstract
{
    protected $_platform;
    protected $config = array(
        'log_time_format'   => 'Y-m-d H:i:s',
        'log_file_size'     => 2097152,
        'log_path'          => '',
    );

    public function __construct($config = array())
    {
        global $app;

        $this->config['log_path'] = $app->getLogRoot();
        $this->config = array_merge($this->config, $config);
        $this->_platform = C('log.platform') ? C('log.platform') : 'g7s';
    }

    public function buildMessage($class, $logs)
    {
        global $app;

        $requesturi = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
        $serverAddr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
        $requestIp  = helper::getIP();
        $appName    = $app->getAppName();

        $data = [];
        foreach ($logs as $log) {
            $data[] = sprintf(
                "%s|%s|%s|%s|%s|%s|%s|[%s] %s\n####",
                $this->_platform,
                $serverAddr,
                $requestIp,
                $requesturi,
                isset($log['level']) ? $log['level'] : 'INFO',
                $class,
                $appName,
                isset($log['caller']) ? $log['caller'] : '',
                $log['message']
            );
        }
        return $data;
    }

    abstract function write($logs, $destination = '');
}
