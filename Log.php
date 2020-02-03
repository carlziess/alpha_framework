<?php
/*================================================================
*  File Name：Log.php
*  Author：carlziess, chengmo9292@126.com
*  Create Date：2016-01-15 13:52:18
*  Description：
===============================================================*/
class Log 
{

    // 日志级别 从上到下，由低到高
    const EMERG = 'EMERG'; // 严重错误: 导致系统崩溃无法使用
    const ALERT = 'ALERT'; // 警戒性错误: 必须被立即修改的错误
    const CRIT = 'CRIT'; // 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
    const ERROR = 'ERROR'; // 一般错误: 一般性错误
    const WARN = 'WARN'; // 警告性错误: 需要发出警告的错误
    const NOTICE = 'NOTIC'; // 通知: 程序可以运行但是还不够完美的错误
    const INFO = 'INFO'; // 信息: 程序输出信息
    const DEBUG = 'DEBUG'; // 调试: 调试信息
    const SQL = 'SQL'; // SQL：SQL语句 注意只在调试模式开启时有效

    // 日志信息
    static protected $_log = [];

    // 日志存储
    static protected $_storage = null;

    // 日志初始化
    static public function init($config = []) 
    {
        $type = isset($config['type']) ? $config['type'] : 'File';
        if (strpos($type, '_')) {
            $class = $type;
        } else {
            $class = 'Log_Driver_' . ucwords(strtolower($type));
        }
        unset($config['type']);
        self::$_storage = new $class($config);
    }

    /**
     * DEBUG日志输出
     * @static
     * @access public
     * @param string $message 日志信息
     * @param boolean $record  是否强制记录
     * @param boolean $save  是否直接写入
     * @param string  $class 日志类别
     * @return void
     */
    static public function debug($message, $record = false, $save = false, $class = '')
    {
        self::record($message, self::DEBUG, $record, $save, $class);
    }

    /**
     * INFO日志输出
     * @static
     * @access public
     * @param string $message 日志信息
     * @param boolean $record  是否强制记录
     * @param boolean $save  是否直接写入
     * @param string  $class 日志类别
     * @return void
     */
    static public function info($message, $record = false, $save = false, $class = '')
    {
        self::record($message, self::INFO, $record, $save, $class);
    }

    /**
     * WARN日志输出
     * @static
     * @access public
     * @param string $message 日志信息
     * @param boolean $record  是否强制记录
     * @param boolean $save  是否直接写入
     * @param string  $class 日志类别
     * @return void
     */
    static public function warn($message, $record = false, $save = false, $class = '') 
    {
        self::record($message, self::WARN, $record, $save, $class);
    }

    /**
     * ERROR日志输出
     * @static
     * @access public
     * @param string $message 日志信息
     * @param boolean $record  是否强制记录
     * @param boolean $save  是否直接写入
     * @param string  $class 日志类别
     * @return void
     */
    static public function error($message, $record = false, $save = false, $class = '') 
    {
        self::record($message, self::ERROR, $record, $save, $class);
    }

    /**
     * 记录日志 并且会过滤未经设置的级别
     * @static
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param boolean $record  是否强制记录
     * @param boolean $save  是否直接写入
     * @param string  $class 日志类别
     * @return void
     */
    static public function record($message, $level = self::ERROR, $record = false, $save = false, $class = '') 
    {
        $traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $caller = next($traces);
        if (isset($caller['class']) && $caller['class'] == 'Log') {
            $caller = next($traces);
            $caller['class'] = !empty($caller['class']) ? $caller['class'] : '';
            $caller['function'] = !empty($caller['function']) ? $caller['function'] : '';
        }
                                                                        
        $logConf = (new Yaf\Config\Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'log.ini'))->application->log;
        if ($record || false !== strpos($logConf->level, $level)) {
            if (!is_string($message))
                $message = json_encode($message);

            self::$_log[$class][] = [
                'level' => $level,
                'caller' => $caller['class'] . '.' . $caller['function'],
                'message' => $message
            ];
            if ($save)
                self::save($save);
        }
    }

    /**
     * 日志保存
     * @static
     * @access public
     * @param integer $type 日志记录方式
     * @param string $destination  写入目标
     * @return void
     */
    static public function save($save = false, $type = '', $destination = '') 
    {
        if (empty(self::$_log))
            return;
        $logConf = (new Yaf\Config\Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'log.ini'))->application->log;
        $type = $type ? $type : $logConf->type;
        if ($save === 2) {
            $type = 'sys';
        }
        if (! self::$_storage) {
            $drive = 'Log_Driver_' . ucwords($type);
            self::$_storage = new $drive;
        }
        foreach (self::$_log as $class => $logs) {
            if (strtolower($type) == 'tcp') {
                $message = self::$_storage->buildMessage($class, $logs);
            } else {
                $logsarr = [];
                foreach ($logs as $key => $row) {
                    $logsarr[] = "{$row['level']}|{$row['caller']}|{$row['message']}";
                }
                $message = implode(PHP_EOL, $logsarr);
            }
            $destination = '';
            $destination = self::_generateDestination($destination, $class);
            if (ucwords($type) == 'File'){
                self::$_storage->write($message, $class);
            }else {
                self::$_storage->write($message, $destination);
            }
        }

        // 保存后清空日志缓存
        self::$_log = [];
    }

    /**
     * 日志直接写入
     * @static
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param integer $type 日志记录方式
     * @param string $destination  写入目标
     * @param string  $class 日志类别
     * @return void
     */
    static public function write($message, $level = self::ERROR, $type = '', $destination = '', $class = '') 
    {
        $logConf = (new Yaf\Config\Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'log.ini'))->application->log;
        $type = $type ? $type : $logConf->type;
        if (! self::$_storage) {
            $drive = 'Log_Driver_' . ucwords($type);
            self::$_storage = new $drive;
        }

        $traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $caller = next($traces);
        if ($caller['class'] == 'Log') {
            $caller = next($traces);
            $caller['class'] = !empty($caller['class']) ? $caller['class'] : '';
            $caller['function'] = !empty($caller['function']) ? $caller['function'] : '';
        }
        if (strtolower($type) == 'tcp') {
            $message = self::$_storage->buildMessage(
                $class, [['level' => $level, 'caller' => $caller['class'] . '.' . $caller['function'], 'message' => $message]]
            );
        } else {
            $message = "{$level}: {$message}";
        }
        $destination = self::_generateDestination($destination, $class);
        self::$_storage->write($message, $destination);
    }

    /**
     * 生成写入目标， 如果没有指定，给出一个默认的目标
     * @param  string $destination 写入目标
     * @param  string $class       类别
     * @return string
     */
    static private function _generateDestination($destination = '', $class = '')
    {
        if (!empty($destination)) {
            return $destination;
        }
        $destination = date('y_m_d') . '.log';;
        if ($class != '') {
            $destination = $class . '_' . $destination;
        }
        return $destination;
    }
}
