<?php
/**
 * 文件相关函数库
 * Author: Zhangzy
 */

if (!function_exists('randomStr')) {
    /**
     * 生成指定长度字符串
     * Author: Zhangzy
     * Date: 2017-08-09
     * @param $length
     * @return string
     */
    function randomStr($length)
    {
        $key = '';
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyz';
        for ($i = 0; $i < $length; $i++) {
            $key .= $pattern[mt_rand(0, 35)];
        }
        return $key;
    }
}

if (!function_exists('dump')) {
    /**
     * 浏览器友好的变量输出
     * Author: Zhangzy
     * @param mixed $var 变量
     * @param boolean $echo 是否输出字符串 默认为True 如果为false 则返回输出字符串
     * @param string $label 标签 默认为空
     * @param boolean $strict 是否严谨 默认为true
     * @return void|string
     */
    function dump($var, $echo = true, $label = null, $strict = true)
    {
        $label = ($label === null) ? '' : rtrim($label) . ' ';
        if (!$strict) {
            if (ini_get('html_errors')) {
                $output = print_r($var, true);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            } else {
                $output = $label . print_r($var, true);
            }
        } else {
            ob_start();
            var_dump($var);
            $output = ob_get_clean();
            if (!extension_loaded('xdebug')) {
                $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            }
        }
        if ($echo) {
            echo($output);
            return null;
        } else {
            return $output;
        }
    }
}

if (!function_exists('arrayToObject')) {
    /**
     * 数组转object
     * Author: Zhangzy
     * Date: 2017-06-23
     * @param array $arr
     * @return bool|object
     */
    function arrayToObject($arr)
    {
        if (gettype($arr) != 'array') {
            return false;
        }
        foreach ($arr as $k => $v) {
            if (gettype($v) == 'array' || getType($v) == 'object') {
                $arr[$k] = (object)arrayToObject($v);
            }
        }
        return (object)$arr;
    }
}

if (!function_exists('objectToArray')) {
    /**
     * object转数组
     * Author: Zhangzy
     * Date: 2017-06-23
     * @param object $obj
     * @return array|bool
     */
    function objectToArray($obj)
    {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return false;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)objectToArray($v);
            }
        }
        return $obj;
    }
}