<?php
/*================================================================
*   File Name：Driver.php
*   Author：carlziess, lizhenglin@g7.com.cn
*   Create Date：2016-02-15 16:42:18
*   Description：
================================================================*/
namespace Cache;
abstract class Driver
{
    abstract public function has($key);

	abstract public function get($key);

    abstract public function put($key, $value, $seconds = 0);

	abstract public function delete($key);

}
