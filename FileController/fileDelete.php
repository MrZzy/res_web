<?php
/**
 * 删除文件接口
 * 仅供服务器直接访问，GET/POST方法
 * 需在服务端使用file_get_contents()调用
 * Author: Zhangzy
 */

$cfg_arr = include_once 'config.php';
include_once 'function.php';

$err_arr = $cfg_arr['ERR_ARR'];

clearstatcache();

$time_now = time();

$att_id = $_REQUEST['a_id']; //主要用于删除多个(英文半角逗号隔开1,2,3,4)
$att_url = $_REQUEST['a_url'];

/** 没有文件需要删除 */
if ((!isset($att_id) || '' == $att_id) && (!isset($att_url) || '' == $att_url)) {
    exit(json_encode($err_arr['NO_FILE_TO_DELETE']));
}

if (isset($att_url) && '' != $att_url) { //根据URL删除单文件
    $tmp_url = preg_replace('/^http:\/\/[^\/]*\//', '/', $att_url);
    $tmp_url = strip_tags(urldecode($tmp_url));

    if (file_exists('..' . $tmp_url)) {
        unlink('..' . $tmp_url);
    }
}

exit(json_encode($err_arr['SUCCESS']));
