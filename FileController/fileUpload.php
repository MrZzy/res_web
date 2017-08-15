<?php
/**
 * 文件上传
 * Author: Zhangzy
 */

header("Content-type: text/html; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, Accept,X-Requested-With");

/** 可添加域名/IP等限制 */

$time_now = time();

$cfg_arr = include_once 'config.php';
include_once 'function.php';

$err_arr = $cfg_arr['ERR_ARR'];
$accept_type_arr = $cfg_arr['ACCEPT_FILE_TYPE'];

if(empty($_FILES)) { //没有选择上传文件
    exit(json_encode($err_arr['NO_FILE_UPLOAD']));
}

$filename   = $_FILES['file']['name'];
$path_info  = pathinfo($filename);
$ext        = $path_info['extension'];
$temp_name  = date('YmdHis') . '_' . floor(microtime() * 1000) . randomStr(3) . '.' . $ext;

//判断文件类型
if (!in_array($ext, $accept_type_arr['all_file'])) {
    exit(json_encode($err_arr['FILE_TYPE_ERROR']));
}

//php.ini中配置
$ini_post_max_size = ini_get('post_max_size'); //post数据大小(8M)
$ini_upload_max_filesize = ini_get('upload_max_filesize'); //文件上传大小(2M)
$tmp_post_max_size = explode('M', $ini_post_max_size);
$post_max_size = $tmp_post_max_size[0] * 1024 * 1024;
$tmp_upload_max_filesize = explode('M', $ini_upload_max_filesize);
$upload_max_filesize = $tmp_upload_max_filesize[0] * 1024 * 1024;

if ($_FILES['file']['size'] > $post_max_size) { //文件超出php.ini中表单大小限制
    exit(json_encode($err_arr['FILE_SIZE_GT_POST_SIZE']));
}
if ($_FILES['file']['size'] > $upload_max_filesize) { //文件超出php.ini中上传文件大小限制
    exit(json_encode($err_arr['FILE_SIZE_GT_UP_SIZE']));
}

//判断文件大小
if (in_array($ext, $accept_type_arr['image'])) { //图片文件
    if ($_FILES['file']['size'] > $cfg_arr['UP_IMG_SIZE_LIMIT']) {
        exit(json_encode($err_arr['IMG_SIZE_TOO_LARGE']));
    }
} elseif (in_array($ext, $accept_type_arr['video'])) { //多媒体文件
    if ($_FILES['file']['size'] > $cfg_arr['UP_VIDEO_SIZE_LIMIT']) {
        exit(json_encode($err_arr['VIDEO_SIZE_TOO_LARGE']));
    }
} else { //其他类型文件
    if ($_FILES['file']['size'] > $cfg_arr['UP_FILE_SIZE_LIMIT']) {
        exit(json_encode($err_arr['FILE_SIZE_TOO_LARGE']));
    }
}

//文件上传目录
$save_path = $cfg_arr['UP_FILE_PATH'];

$tmp_type = explode(' ', $_FILES['file']['type']); // image/jpeg; binary
$tmp_type = 0 < count($tmp_type) ? $tmp_type[0] : 'file/'; // image/jpeg
$tmp_arr = explode('/', $tmp_type);
if ('application' == strtolower($tmp_arr[0])) {
    $save_type_path = 'file';
} else {
    $save_type_path = $tmp_arr[0];
}

if (false === is_writable($save_path)) { //上传目录不可写
    exit(json_encode($err_arr['UP_PATH_NOT_WRITABLE']));
}
$path = $save_path . $save_type_path . '/'.date('Ymd') . '/';
if(!is_dir($path)) {
    if (false === mkdir($path, 0777, true)) {
        exit(json_encode($err_arr['SAVE_PATH_MAKE_FAILED']));
    }
}

$filename   = $path . $temp_name;
$handle_file_name = str_replace('../', '/', $filename); //用于返回
if(!move_uploaded_file($_FILES['file']['tmp_name'], $filename)) {
    exit(json_encode($err_arr['FILE_MOVE_FAILED']));
} else {
    $ret_arr = [
        'state' => 1,
        'att_url' => $handle_file_name,
        'full_url' => $cfg_arr['UP_FILE_DOMAIN'] . $handle_file_name,
    ];
    exit(json_encode($ret_arr));
}
