<?php
/*================================================================
*   File Name：Folder.php
*   Author：carlziess, lizhenglin@g7.com.cn
*   Create Date：2016-02-21 13:18:26
*   Description：
================================================================*/
namespace Utility;
class Folder
{
	const READ_ALL = '0';
	const READ_FILE = '1';
	const READ_DIR = '2';

	/**
	 * 获取文件列表
	 *
	 * @param string $dir
	 * @param boolean $mode 只读取文件列表,不包含文件夹
	 * @return array
	 */
	static public function read($dir, $mode = self::READ_ALL) {
		if (!$handle = @opendir($dir)) return array();
		$files = array();
		while (false !== ($file = @readdir($handle))) {
			if ('.' === $file || '..' === $file) continue;
			if ($mode === self::READ_DIR) {
				if (static::isDir($dir . '/' . $file)) $files[] = $file;
			} elseif ($mode === self::READ_FILE) {
				if (File::isFile($dir . '/' . $file)) $files[] = $file;
			} else
				$files[] = $file;
		}
		@closedir($handle);
		return $files;
	}

	/**
	 * 删除目录
	 *
	 * @param string $dir
	 * @param boolean $f 是否强制删除
	 * @return boolean
	 */
	static public function rm($dir, $f = false) {
		return $f ? static::clearRecur($dir, true) : @rmdir($dir);
	}

	/**
	 * 删除指定目录下的文件
	 *
	 * @param string  $dir 目录
	 * @param boolean $delFolder 是否删除目录
	 * @return boolean
	 */
	static public function clear($dir, $delFolder = false) {
		if (!static::isDir($dir)) return false;
		if (!$handle = @opendir($dir)) return false;
		while (false !== ($file = readdir($handle))) {
			if ('.' === $file[0] || '..' === $file[0]) continue;
			$filename = $dir . '/' . $file;
			if (File::isFile($filename)) File::del($filename);
		}
		@closedir($handle);
		$delFolder && @rmdir($dir);
		return true;
	}

	/**
	 * 递归的删除目录
	 *
	 * @param string $dir 目录
	 * @param Boolean $delFolder 是否删除目录
	 */
	static public function clearRecur($dir, $delFolder = false) {
		if (!static::isDir($dir)) return false;
		if (!$handle = @opendir($dir)) return false;
		while (false !== ($file = readdir($handle))) {
			if ('.' === $file || '..' === $file) continue;
			$_path = $dir . '/' . $file;
			if (static::isDir($_path)) {
				static::clearRecur($_path, $delFolder);
			} elseif (File::isFile($_path))
			File::del($_path);
		}
		@closedir($handle);
		$delFolder && @rmdir($dir);
		return true;
	}

	/**
	 * 判断输入是否为目录
	 *
	 * @param string $dir
	 * @return boolean
	 */
	static public function isDir($dir) {
		return $dir ? is_dir($dir) : false;
	}

	/**
	 * 取得目录信息
	 *
	 * @param string $dir 目录路径
	 * @return array
	 */
	static public function getInfo($dir) {
		return static::isDir($dir) ? stat($dir) : array();
	}

	/**
	 * 创建目录
	 *
	 * @param string $path 目录路径
	 * @param int $permissions 权限
	 * @return boolean
	 */
	static public function mk($path, $permissions = 0770) {
		return @mkdir($path, $permissions);
	}

	/**
	 * 递归的创建目录
	 *
	 * @param string $path 目录路径
	 * @param int $permissions 权限
	 * @return boolean
	 */
	static public function mkRecur($path, $permissions = 0770) {
		if (is_dir($path)) return true;
		$_path = dirname($path);
		if ($_path !== $path) static::mkRecur($_path, $permissions);
		return static::mk($path, $permissions);
	}

}
