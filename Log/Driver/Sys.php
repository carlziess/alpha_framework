<?php

class Log_Driver_Sys
{

    protected $config = array(
            'log_time_format' => 'Y-m-d H:i:s',
            'log_file_size' => 2097152,
            'log_path' => ''
    );
    
    // 实例化并传入参数
    public function __construct ($config = array())
    {
        global $app;
        $this->config['log_path'] = $app->getLogRoot();
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 日志写入接口
     *
     * @access public
     * @param string $log
     *            日志信息
     * @param string $destination
     *            写入目标
     * @return void
     */
    public function write ($log, $destination = '')
    {
        $now = date($this->config['log_time_format']);
        if (empty($destination))
            $destination = date('y_m_d') . '.log';
        $destination = $this->config['log_path'] . $destination;

		syslog(LOG_NOTICE, "[{$now}]".$_SERVER['REMOTE_ADDR'].' '.$_SERVER['REQUEST_URI']."\r\n{$log}\r\n");

    }
}
