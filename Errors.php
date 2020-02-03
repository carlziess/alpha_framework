<?php
/*================================================================
*  File Name：Errors.php
*  Author：carlziess, chengmo9292@126.com
*  Create Date：2017-01-15 13:53:17
*  Description：
*  EMERG    严重错误: 导致系统崩溃无法使用
*  ALERT    警戒性错误: 必须被立即修改的错误
*  CRIT     临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
*  ERROR    一般错误: 一般性错误
*  WARN     警告性错误: 需要发出警告的错误
*  NOTICE   通知: 程序可以运行但是还不够完美的错误
*  INFO     信息: 程序输出信息
*  DEBUG    调试: 调试信息
*  SQL      SQL：SQL语句 注意只在调试模式开启时有效
===============================================================*/
class Errors
{ 
    /**
     * php错误句柄
     * @param string $errno      错误号
     * @param string $errstr     错误信息
     * @param string $errfile    错误文件
     * @param string $errline    错误行
     * @param string $errcontext 上下文
     * @return void
     */
    static public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext = '')
    {
        $errors = 'Unknown';
        switch ($errno) {
        case E_NOTICE :
        case E_USER_NOTICE :
            $errors = 'Notice';
            $logLevel = 'NOTICE';
            break;
        case E_WARNING :
        case E_USER_WARNING :
            $errors = 'Warning';
            $logLevel = 'WARN';
            break;
        case E_PARSE:
            $errors = 'Parse error';
            $logLevel = 'EMERG';
            break;
        case E_ERROR :
        case E_USER_ERROR :
            $errors = 'Fatal error';
            $logLevel = 'ERROR';
            break;
        default :
            $errors = $errno . '错误编号';
            $logLevel = 'INFO';
            break;
        }
        $message = sprintf('PHP %s:  %s in %s on line %d', $errors, $errstr, $errfile, $errline);
        $logConf = (new Yaf\Config\Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'log.ini'))->application->log;
        if ($errno && true == $logConf->record) {
            if (class_exists('Log')) {
                $params = var_export($_REQUEST, true);
                Log::record($message . "\n REQUEST :\t" . $params, $logLevel, true, true, 'PHPERROR');
            }
        }
        throw new ErrorException($errstr, $errno, $errno, $errfile, $errline);
    }

    /**
     * 处理set_error_handler不能捕获的错误
     * @return [type] [description]
     */
    static public function lastErrorHandler()
    {
        $lastError = error_get_last();
        if ($lastError != null) {
            $mes = sprintf(
                '[ERRNO:]%s [FILE:]%s [LINE:]%s [MESSAGE:]%s', 
                $lastError['type'],
                $lastError['file'],
                $lastError['line'],
                $lastError['message']
            );
            if ($lastError['type'] == E_NOTICE || $lastError['type'] == E_USER_NOTICE) {
                if (class_exists('Log')) {
                    $params = var_export($_REQUEST, true);
                    Log::record($mes . "\n REQUEST :\t" . $params, Log::NOTICE, true, true, 'PHPERROR');
                }
                return ;
            }
            try {
                throw new ErrorException(
                    $lastError['message'], 2, $lastError['type'],
                    $lastError['file'], $lastError['line']
                );
            } catch (Exception $e) {
                static::exceptionHandler($e, false);
            }
        }
    }

    /**
     * 异常句柄
     * @param Exception $exception   异常
     * @param boolean   $termination 是否立即退出
     * @return void
     */
    static public function exceptionHandler($exception, $termination = true)
    {
        if (class_exists('Log')) {
            $params = var_export($_REQUEST, true);
            Log::error(sprintf("\nREQUEST :\n%s\nTRACE :\n%s", $params, strval($e)), true, true, 'PHPERROR');
        }
        $result = [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage()
        ];
        ob_clean();
        header("Content-Type: application/json");
        echo json_encode($result);
        if ($termination) {
            die();
        }
    }

}
