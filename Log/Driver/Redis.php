<?php

class Log_Driver_Redis
{

    protected $config = array(
            'log_time_format' => 'Y-m-d H:i:s',
            'log_file_size' => 2097152,
            'log_path' => ''
    );
    private $_platform = '';

    private $_redisClient = null;
    
    // 实例化并传入参数
    public function __construct ($config = array())
    {
        try {
            $this->_redisClient = @new Redis;
            $host = C('log.redishost') ? C('log.redishost') : '';
            $port = C('log.redisport') ? C('log.redisport') : '';
            @$this->_redisClient->connect(
                $host, $port
            );
        } catch (Exception $e) {
            Debug::log($e);
            return ;
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
            $requesturi = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
            $data[] = sprintf(
                "%s|%s|%s|%s|%s|%s|%s|%s|[%s] %s\n",
                $now,
                $this->_platform,
                isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '',
                helper::getIP(),
                $requesturi,
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
        if (!$this->_redisClient) {
            return false;
        }
        if (!is_array($logs)) {
            $message = json_encode($logs);
            $logs = $this->buildMessage('LOG', [['message' => $message]]);
        }
        foreach ($logs as $key => $row) {
            try {
                //@ socket_write($this->_socketClient, $row, strlen($row));
                $this->_redisClient->lpush('log', $row);
            } catch (Exception $e) {
                //Debug::info('Tcp 日志写入失败:' . $e->getMessage());
            }
        }
        return true;
    }

    // public function __destruct()
    // {
    //     try {
    //         @ socket_close($this->_socketClient);
    //     } catch (Exception $e) {
    //         //Debug::info($e->getMessage());
    //     }
        
    // }
}
