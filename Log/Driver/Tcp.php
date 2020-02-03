<?php

class Log_Driver_Tcp
{

    protected $config = array(
            'log_time_format' => 'Y-m-d H:i:s',
            'log_file_size' => 2097152,
            'log_path' => ''
    );
    private $_platform = '';

    private $_socketClient = null;
    
    // 实例化并传入参数
    public function __construct ($config = array())
    {
        try {
            $this->_socketClient = @socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
            $res = socket_connect(
                $this->_socketClient,
                C('log.tcpip') ? C('log.tcpip') : '127.0.0.1',
                C('log.tcpport') ? C('log.tcpport') : '10200'
            );
        } catch (Exception $e) {
            //nothing todo
            //Debug::error($e);
        }
        $this->_platform = C('log.platform') ? C('log.platform') : 'g7s';

    }

    public function buildMessage($class, $logs)
    {
        global $app;
        $now = date($this->config['log_time_format']);
        $data = [];
        foreach ($logs as $log) {
            $data[] = sprintf(
                "%s|%s|%s|%s|%s|%s|%s|[%s] %s\n",
                $now,
                $this->_platform,
                helper::getIP(),
                $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                isset($log['level']) ? $log['level'] : 'INFO',
                $class,
                $app->getAppName(),
                isset($log['caller']) ? $log['caller'] : '',
                str_replace(["\n", "\r"], '<CR>', isset($log['message']) ? $log['message'] : '')
            );
        }
        return $data;
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
    public function write ($logs, $destination = '')
    {
        if (!is_array($logs)) {
            $message = json_encode($logs);
            $logs = $this->buildMessage('LOG', [['message' => $message]]);
        }
        foreach ($logs as $key => $row) {
            try {
                @ socket_write($this->_socketClient, $row, strlen($row));
            } catch (Exception $e) {
                //Debug::info('Tcp 日志写入失败:' . $e->getMessage());
            }
        }
        return true;
    }

    public function __destruct()
    {
        try {
            @ socket_close($this->_socketClient);
        } catch (Exception $e) {
            //Debug::info($e->getMessage());
        }
        
    }
}
