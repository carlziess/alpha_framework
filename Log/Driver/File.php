<?php
/*================================================================
*  File Name：File.php
*  Author：carlziess, chengmo9292@126.com
*  Create Date：2018-01-16 17:46:51
*  Description：
===============================================================*/
class Log_Driver_File 
{

    const DEFAULT_NAME = 'default';
    const TODAY_EXT = '.today.log';
    const EXT = '.log';
    protected $config = array(
            'log_time_format' => 'Y-m-d H:i:s',
            'log_file_size' => 2097152,
            'log_path' => ''
    );
    
    // 实例化并传入参数
    public function __construct()
    {
        $appPath = Yaf\Registry::get('config')->application->directory;
        $this->config['log_path'] = $appPath . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
    }

    /**
     * 日志写入接口
     * @access public
     * @param string $log 日志信息
     * @param string $destination 写入目标
     * @return void
     */
    public function write($log, $destination = '')
    {
        $class = $destination;
        $now = date($this->config['log_time_format']);
        if (empty($class))
            $class = self::DEFAULT_NAME;
        $text = "{$now}|" . $_SERVER['REMOTE_ADDR'] . '|' . $_SERVER['REQUEST_URI'] . "|{$class}|{$log}####\r\n";
        $destination = $this->config['log_path'] . $class . self::TODAY_EXT;
        $path = dirname($destination);
        if (!is_dir($path) && ! @mkdir($path, 0777, true)) {
            throw new Exception('创建日志目录失败', 2);
        }

        if (($fp = @fopen($destination, 'a')) === false) {
            throw new Exception("Unable to append to log file: {$class}", 2);
        }
        @flock($fp, LOCK_EX);
        // 检测日志文件日期，过期则备份日志文件重新生成
        clearstatcache();
        $dateFormat = '_y_m_d';
        $filecDate = date($dateFormat, filectime($destination));
        if ($filecDate != date($dateFormat) && is_file($destination)) {
            try {
                @ rename(
                    $destination,
                    $this->config['log_path'] . $class . $filecDate. self::EXT
                );
            } catch (Exception $e) {
                @ Log::warn('尝试重命名｛' . $destination . '｝失败，请检查文件和文件夹权限', true, true);
            }
            @flock($fp, LOCK_UN);
            @fclose($fp);
            @file_put_contents($destination, $text, FILE_APPEND | LOCK_EX);
        }else{
            @fwrite($fp, $text);
            @flock($fp, LOCK_UN);
            @fclose($fp);
        }
    }
}
