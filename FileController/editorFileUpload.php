<?php
/**
 * ueditor编辑器插件中文件上传
 * Author: Zhangzy
 */

header('Content-type: text/html; charset=utf-8');
header('Access-Control-Allow-Origin: *');
//header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept,X-Requested-With');
header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');

// options请求就只需要输出头部信息就OK了。
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit; // finish preflight CORS requests here
}

/** 可添加域名/IP等限制 */

$time_now = time();

$cfg_arr = include_once 'config.php';
include_once 'function.php';

$err_arr = $cfg_arr['ERR_ARR'];

$action = $_REQUEST['action']; //操作类型
$call_back = isset($_REQUEST['callback']) ? $_REQUEST['callback'] : '';

switch ($action) {
    case 'uploadimage': //上传图片
    case 'uploadvideo': //上传多媒体
    case 'uploadfile': //上传文件
        $is_single_up = isset($_POST['is_single']) && 1 == $_POST['is_single'] ? 1 : 0; //是否单图直接上传(用于处理跨域问题)
        $from_domain = isset($_POST['from_domain']) && '' != $_POST['from_domain'] ? $_POST['from_domain'] : $cfg_arr['WWW_DOMAIN']; //来源域名，区分前后台(用于处理跨域问题) @TODO 与配置项同步
        uploadFile($action, $_FILES, $cfg_arr, $err_arr, $from_domain, $is_single_up, $call_back);
        break;
    case 'uploadscrawl': //上传涂鸦
        $base64_data = isset($_POST['upfile']) ? $_POST['upfile'] : '';
        uploadBase64($base64_data, $cfg_arr, $err_arr, $call_back);
        break;
    case 'listimage': //列出图片
    case 'listfile': //列出文件
        $list_size = $cfg_arr['LIST_SIZE'];
        $size = isset($_REQUEST['size']) ? htmlspecialchars($_REQUEST['size']) : $list_size;
        $start = isset($_REQUEST['start']) ? htmlspecialchars($_REQUEST['start']) : 0;
        listFile($action, $start, $size, $cfg_arr, $call_back);
        break;
    case 'catchimage': //远程文件
        $remote_source = isset($_REQUEST['source']) ? $_REQUEST['source'] : '';
        remoteImg($remote_source, $cfg_arr, $err_arr, $call_back);
        break;
    default:
        $json_ret = json_encode(['state' => $err_arr['URL_ILLEGAL']['msg']]);
        if ('' != $call_back) {
            exit($call_back . '(' . $json_ret . ')');
        } else {
            exit($json_ret);
        }
        break;
}

/**
 * 上传文件
 * Author: Zhangzy
 * @param string $action 操作类型
 * @param array $up_file 上传文件
 * @param array $cfg_arr 配置项
 * @param array $err_arr 错误提示
 * @param string $from_domain 来源域名 - 用于区分前后台(单图片上传跨域)
 * @param int $is_single_up 是否单图直接上传。1是0否
 * @param string $call_back 针对jsonp的回调函数
 */
function uploadFile($action, $up_file, $cfg_arr, $err_arr, $from_domain, $is_single_up = 0, $call_back = '')
{
    if (empty($up_file) || !(0 < count($up_file))) { //没有选择上传文件
        $json_ret = json_encode(['state' => $err_arr['NO_FILE_UPLOAD']['msg']]);
        if ('' != $call_back) {
            exit($call_back . '(' . $json_ret . ')');
        } else {
            exit($json_ret);
        }
    }

    $accept_type_arr = $cfg_arr['ACCEPT_FILE_TYPE'];
    $file_name = $up_file['upfile']['name'];
    $path_info = pathinfo($file_name);
    $ext = $path_info['extension'];
    $temp_name = date('YmdHis') . '_' . floor(microtime() * 1000) . randomStr(3) . '.' . $ext;

    //判断文件类型/大小
    $save_type_path = 'image';
    switch ($action) {
        case 'uploadimage': //上传图片
            if (!in_array($ext, $accept_type_arr['image'])) {
                $json_ret = json_encode(['state' => $err_arr['FILE_TYPE_ERROR']['msg']]);
                if ('' != $call_back) {
                    exit($call_back . '(' . $json_ret . ')');
                } else {
                    exit($json_ret);
                }
            }
            if ($up_file['upfile']['size'] > $cfg_arr['UP_IMG_SIZE_LIMIT']) {
                $json_ret = json_encode(['state' => $err_arr['IMG_SIZE_TOO_LARGE']['msg']]);
                if ('' != $call_back) {
                    exit($call_back . '(' . $json_ret . ')');
                } else {
                    exit($json_ret);
                }
            }
            $save_type_path = 'image';
            break;
        case 'uploadvideo': //上传多媒体
            if (!in_array($ext, $accept_type_arr['video'])) {
                $json_ret = json_encode(['state' => $err_arr['FILE_TYPE_ERROR']['msg']]);
                if ('' != $call_back) {
                    exit($call_back . '(' . $json_ret . ')');
                } else {
                    exit($json_ret);
                }
            }
            if ($up_file['upfile']['size'] > $cfg_arr['UP_VIDEO_SIZE_LIMIT']) {
                $json_ret = json_encode(['state' => $err_arr['VIDEO_SIZE_TOO_LARGE']['msg']]);
                if ('' != $call_back) {
                    exit($call_back . '(' . $json_ret . ')');
                } else {
                    exit($json_ret);
                }
            }
            $save_type_path = 'video';
            break;
        case 'uploadfile': //上传文件
            if (!in_array($ext, $accept_type_arr['file'])) {
                $json_ret = json_encode(['state' => $err_arr['FILE_TYPE_ERROR']['msg']]);
                if ('' != $call_back) {
                    exit($call_back . '(' . $json_ret . ')');
                } else {
                    exit($json_ret);
                }
            }
            if ($up_file['upfile']['size'] > $cfg_arr['UP_FILE_SIZE_LIMIT']) {
                $json_ret = json_encode(['state' => $err_arr['FILE_SIZE_TOO_LARGE']['msg']]);
                if ('' != $call_back) {
                    exit($call_back . '(' . $json_ret . ')');
                } else {
                    exit($json_ret);
                }
            }
            $save_type_path = 'file';
            break;
        default:
            $json_ret = json_encode(['state' => $err_arr['URL_ILLEGAL']['msg']]);
            if ('' != $call_back) {
                exit($call_back . '(' . $json_ret . ')');
            } else {
                exit($json_ret);
            }
            break;
    }

    //php.ini中配置
    $ini_post_max_size = ini_get('post_max_size'); //post数据大小(8M)
    $ini_upload_max_filesize = ini_get('upload_max_filesize'); //文件上传大小(2M)
    $tmp_post_max_size = explode('M', $ini_post_max_size);
    $post_max_size = $tmp_post_max_size[0] * 1024 * 1024;
    $tmp_upload_max_filesize = explode('M', $ini_upload_max_filesize);
    $upload_max_filesize = $tmp_upload_max_filesize[0] * 1024 * 1024;

    if ($up_file['upfile']['size'] > $post_max_size) { //文件超出php.ini中表单大小限制
        $json_ret = json_encode(['state' => $err_arr['FILE_SIZE_GT_POST_SIZE']['msg']]);
        if ('' != $call_back) {
            exit($call_back . '(' . $json_ret . ')');
        } else {
            exit($json_ret);
        }
    }
    if ($up_file['upfile']['size'] > $upload_max_filesize) { //文件超出php.ini中上传文件大小限制
        $json_ret = json_encode(['state' => $err_arr['FILE_SIZE_GT_UP_SIZE']['msg']]);
        if ('' != $call_back) {
            exit($call_back . '(' . $json_ret . ')');
        } else {
            exit($json_ret);
        }
    }

    //文件上传目录
    $save_path = $cfg_arr['UP_FILE_PATH'];

    if (false === is_writable($save_path)) { //上传目录不可写
        $json_ret = json_encode(['state' => $err_arr['UP_PATH_NOT_WRITABLE']['msg']]);
        if ('' != $call_back) {
            exit($call_back . '(' . $json_ret . ')');
        } else {
            exit($json_ret);
        }
    }
    $path = $save_path . $save_type_path . '/' . date('Ymd') . '/';
    if (!is_dir($path)) {
        if (false === mkdir($path, 0777, true)) {
            $json_ret = json_encode(['state' => $err_arr['SAVE_PATH_MAKE_FAILED']['msg']]);
            if ('' != $call_back) {
                exit($call_back . '(' . $json_ret . ')');
            } else {
                exit($json_ret);
            }
        }
    }

    $file_name = $path . $temp_name;
    $handle_file_name = str_replace('../', '/', $file_name); //用于返回
    if (!move_uploaded_file($up_file['upfile']['tmp_name'], $file_name)) {
        $json_ret = json_encode(['state' => $err_arr['FILE_MOVE_FAILED']['msg']]);
        if ('' != $call_back) {
            exit($call_back . '(' . $json_ret . ')');
        } else {
            exit($json_ret);
        }
    } else {
        $ret_arr = [
            'state' => 'SUCCESS',
            //'url' => 'uploadimage' == $action ? $handle_file_name : $cfg_arr['UP_FILE_DOMAIN'].$handle_file_name,
            'url' => $handle_file_name,
            'title' => $temp_name,
            'original' => $up_file['upfile']['name'] . '.' . $ext,
            'type' => '.' . $ext,
            'size' => $up_file['upfile']['size'],
        ];
        if (1 == $is_single_up) {
            header('Location:http://' . $from_domain . '/s_up?res=' . json_encode($ret_arr));
        } else {
            if ('' != $call_back) {
                exit($call_back . '(' . json_encode($ret_arr) . ')');
            } else {
                exit(json_encode($ret_arr));
            }
        }
    }
}

/**
 * 上传base64编码图片 - 涂鸦
 * Author: Zhangzy
 * @param $base64_data
 * @param $cfg_arr
 * @param $err_arr
 * @param string $call_back 针对jsonp的回调函数
 */
function uploadBase64($base64_data, $cfg_arr, $err_arr, $call_back = '')
{
    if (empty($base64_data) || '' == $base64_data) { //没有选择上传文件
        $json_ret = json_encode(['state' => $err_arr['NO_FILE_UPLOAD']['msg']]);
        if ('' != $call_back) {
            exit($call_back . '(' . $json_ret . ')');
        } else {
            exit($json_ret);
        }
    }

    $img = base64_decode($base64_data);
    $ori_name = 'scrawl.png';
    $file_size = strlen($img);

    if ($file_size > $cfg_arr['UP_IMG_SIZE_LIMIT']) {
        $json_ret = json_encode(['state' => $err_arr['IMG_SIZE_TOO_LARGE']['msg']]);
        if ('' != $call_back) {
            exit($call_back . '(' . $json_ret . ')');
        } else {
            exit($json_ret);
        }
    }
    $save_type_path = 'image';

    //php.ini中配置
    $ini_post_max_size = ini_get('post_max_size'); //post数据大小(8M)
    $ini_upload_max_filesize = ini_get('upload_max_filesize'); //文件上传大小(2M)
    $tmp_post_max_size = explode('M', $ini_post_max_size);
    $post_max_size = $tmp_post_max_size[0] * 1024 * 1024;
    $tmp_upload_max_filesize = explode('M', $ini_upload_max_filesize);
    $upload_max_filesize = $tmp_upload_max_filesize[0] * 1024 * 1024;

    if ($file_size > $post_max_size) { //文件超出php.ini中表单大小限制
        $json_ret = json_encode(['state' => $err_arr['FILE_SIZE_GT_POST_SIZE']['msg']]);
        if ('' != $call_back) {
            exit($call_back . '(' . $json_ret . ')');
        } else {
            exit($json_ret);
        }
    }
    if ($file_size > $upload_max_filesize) { //文件超出php.ini中上传文件大小限制
        $json_ret = json_encode(['state' => $err_arr['FILE_SIZE_GT_UP_SIZE']['msg']]);
        if ('' != $call_back) {
            exit($call_back . '(' . $json_ret . ')');
        } else {
            exit($json_ret);
        }
    }

    //文件上传目录
    $save_path = $cfg_arr['UP_FILE_PATH'];

    if (false === is_writable($save_path)) { //上传目录不可写
        $json_ret = json_encode(['state' => $err_arr['UP_PATH_NOT_WRITABLE']['msg']]);
        if ('' != $call_back) {
            exit($call_back . '(' . $json_ret . ')');
        } else {
            exit($json_ret);
        }
    }
    $path = $save_path . $save_type_path . '/' . date('Ymd') . '/';
    if (!is_dir($path)) {
        if (false === mkdir($path, 0777, true)) {
            $json_ret = json_encode(['state' => $err_arr['SAVE_PATH_MAKE_FAILED']['msg']]);
            if ('' != $call_back) {
                exit($call_back . '(' . $json_ret . ')');
            } else {
                exit($json_ret);
            }
        }
    }

    $temp_name = date('YmdHis') . '_' . floor(microtime() * 1000) . randomStr(3) . '.png';

    $file_name = $path . $temp_name;
    $handle_file_name = str_replace('../', '/', $file_name); //用于返回
    if (!(file_put_contents($file_name, $img) && file_exists($file_name))) {
        $json_ret = json_encode(['state' => $err_arr['FILE_MOVE_FAILED']['msg']]);
        if ('' != $call_back) {
            exit($call_back . '(' . $json_ret . ')');
        } else {
            exit($json_ret);
        }
    } else {
        $ret_arr = [
            'state' => 'SUCCESS',
            'url' => $handle_file_name,
            'title' => $temp_name,
            'original' => $ori_name,
            'type' => '.png',
            'size' => $file_size,
        ];
        if ('' != $call_back) {
            exit($call_back . '(' . json_encode($ret_arr) . ')');
        } else {
            exit(json_encode($ret_arr));
        }
    }
}

/**
 * 远程图片处理
 * Author: Zhangzy
 * @param $remote_data
 * @param $cfg_arr
 * @param $err_arr
 * @param string $call_back 针对jsonp的回调函数名
 */
function remoteImg($remote_data, $cfg_arr, $err_arr, $call_back = '')
{
    $list = [];
    if ('' != $remote_data && 0 < count($remote_data)) {
        foreach ($remote_data as $img_url) {
            $info = saveRemoteImg($img_url, $cfg_arr, $err_arr);
            $list[] = [
                'state' => $info['state'],
                'url' => $info['url'],
                'size' => $info['size'],
                'title' => htmlspecialchars($info['title']),
                'original' => htmlspecialchars($info['original']),
                'source' => htmlspecialchars($img_url),
            ];
        }
    }
    $ret_arr = [
        'state' => 0 < count($list) ? 'SUCCESS' : 'ERROR',
        'list' => $list,
    ];
    if ('' != $call_back) {
        exit($call_back . '(' . json_encode($ret_arr) . ')');
    } else {
        exit(json_encode($ret_arr));
    }
}

/**
 * 保存远程图片至服务器
 * Author: Zhangzy
 * @param $img_url
 * @param $cfg_arr
 * @param $err_arr
 * @return array
 */
function saveRemoteImg($img_url, $cfg_arr, $err_arr)
{
    $ret_arr = [];
    $accept_type_arr = $cfg_arr['ACCEPT_FILE_TYPE'];

    $img_url = str_replace('&amp;', '&', $img_url);
    //http开头验证
    if (strpos($img_url, 'http') !== 0) {
        return [
            'state' => $err_arr['NOT_HTTP_LINK']['msg'],
            'url' => '',
            'size' => 0,
            'title' => '',
            'original' => '',
        ];
    }
    //获取请求头并检测死链
    $heads = get_headers($img_url);
    if (!(stristr($heads[0], '200') && stristr($heads[0], 'OK'))) {
        return [
            'state' => $err_arr['IS_DEAD_LINK']['msg'],
            'url' => '',
            'size' => 0,
            'title' => '',
            'original' => '',
        ];
    }
    //格式验证(扩展名验证和Content-Type验证)
    $file_type = strtolower(strrchr($img_url, '.'));
    $file_type = ltrim($file_type, '.');
    if (!in_array($file_type, $accept_type_arr['image'])) {
        return [
            'state' => $err_arr['FILE_TYPE_ERROR']['msg'],
            'url' => '',
            'size' => 0,
            'title' => '',
            'original' => '',
        ];
    }

    //文件上传目录
    $save_path = $cfg_arr['UP_FILE_PATH'];

    if (false === is_writable($save_path)) { //上传目录不可写
        return [
            'state' => $err_arr['UP_PATH_NOT_WRITABLE']['msg'],
            'url' => '',
            'size' => 0,
            'title' => '',
            'original' => '',
        ];
    }
    $path = $save_path . 'image/' . date('Ymd') . '/';
    if (!is_dir($path)) {
        if (false === mkdir($path, 0777, true)) {
            return [
                'state' => $err_arr['SAVE_PATH_MAKE_FAILED']['msg'],
                'url' => '',
                'size' => 0,
                'title' => '',
                'original' => '',
            ];
        }
    }

    $temp_name = date('YmdHis') . '_' . floor(microtime() * 1000) . randomStr(3) . '.png';

    $file_name = $path . $temp_name;
    $handle_file_name = str_replace('../', '/', $file_name); //用于返回

    //打开输出缓冲区并获取远程图片
    ob_start();
    $context = stream_context_create(['http' => ['follow_location' => false]]);
    readfile($img_url, false, $context);
    $img = ob_get_contents();
    ob_end_clean();
    preg_match('/[\/]([^\/]*)[\.]?[^\.\/]*$/', $img_url, $m);
    $ori_name = $m ? $m[1] : '';
    $file_size = strlen($img);

    if ($file_size > $cfg_arr['UP_IMG_SIZE_LIMIT']) {
        return [
            'state' => $err_arr['IMG_SIZE_TOO_LARGE']['msg'],
            'url' => '',
            'size' => 0,
            'title' => '',
            'original' => '',
        ];
    }

    //php.ini中配置
    $ini_post_max_size = ini_get('post_max_size'); //post数据大小(8M)
    $ini_upload_max_filesize = ini_get('upload_max_filesize'); //文件上传大小(2M)
    $tmp_post_max_size = explode('M', $ini_post_max_size);
    $post_max_size = $tmp_post_max_size[0] * 1024 * 1024;
    $tmp_upload_max_filesize = explode('M', $ini_upload_max_filesize);
    $upload_max_filesize = $tmp_upload_max_filesize[0] * 1024 * 1024;

    if ($file_size > $post_max_size) { //文件超出php.ini中表单大小限制
        return [
            'state' => $err_arr['FILE_SIZE_GT_POST_SIZE']['msg'],
            'url' => '',
            'size' => 0,
            'title' => '',
            'original' => '',
        ];
    }
    if ($file_size > $upload_max_filesize) { //文件超出php.ini中上传文件大小限制
        return [
            'state' => $err_arr['FILE_SIZE_GT_UP_SIZE']['msg'],
            'url' => '',
            'size' => 0,
            'title' => '',
            'original' => '',
        ];
    }

    if (!(file_put_contents($file_name, $img) && file_exists($file_name))) {
        return [
            'state' => $err_arr['FILE_MOVE_FAILED']['msg'],
            'url' => '',
            'size' => 0,
            'title' => '',
            'original' => '',
        ];
    } else {
        $ret_arr = [
            'state' => 'SUCCESS',
            'url' => $handle_file_name,
            'title' => $temp_name,
            'original' => $ori_name,
            'type' => '.png',
            'size' => $file_size,
        ];
        return $ret_arr;
    }
}

/**
 * 文件/图片列表
 * Author: Zhangzy
 * @param $action
 * @param $start
 * @param $size
 * @param $cfg_arr
 * @param string $call_back 针对jsonp的回调函数名
 */
function listFile($action, $start, $size, $cfg_arr, $call_back = '')
{
    $end = (int)$start + (int)$size;
    //获取文件列表
    $accept_type_arr = $cfg_arr['ACCEPT_FILE_TYPE'];
    $child_path = 'listfile' == $action ? 'file' : 'image';
    $path = $_SERVER['DOCUMENT_ROOT'] . '/Uploads/' . $child_path . '/';
    $allow_files = 'listfile' == $action ? implode('|', $accept_type_arr['file']) : implode('|', $accept_type_arr['image']);
    $files = getFiles($path, $allow_files);
    if (!(0 < count($files))) {
        $ret_arr = [
            'state' => 'no match file',
            'list' => [],
            'start' => $start,
            'total' => 0
        ];
        if ('' != $call_back) {
            exit($call_back . '(' . json_encode($ret_arr) . ')');
        } else {
            exit(json_encode($ret_arr));
        }
    }
    //获取指定范围的列表
    $len = count($files);
    for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--) {
        $list[] = $files[$i];
    }
    //返回数据
    $result = [
        'state' => 'SUCCESS',
        'list' => $list,
        'start' => $start,
        'total' => count($files)
    ];
    if ('' != $call_back) {
        exit($call_back . '(' . json_encode($result) . ')');
    } else {
        exit(json_encode($result));
    }
}

/**
 * 遍历文件夹取文件
 * Author: Zhangzy
 * @param $path
 * @param $allow_files
 * @param array $files
 * @return array|null
 */
function getFiles($path, $allow_files, &$files = array())
{
    if (!is_dir($path)) return null;
    if (substr($path, strlen($path) - 1) != '/') $path .= '/';
    $handle = opendir($path);
    while (false !== ($file = readdir($handle))) {
        if ($file != '.' && $file != '..') {
            $path2 = $path . $file;
            if (is_dir($path2)) {
                getFiles($path2, $allow_files, $files);
            } else {
                if (preg_match("/\.(" . $allow_files . ")$/i", $file)) {
                    $files[] = [
                        'url' => substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
                        'mtime' => filemtime($path2)
                    ];
                }
            }
        }
    }
    return $files;
}
