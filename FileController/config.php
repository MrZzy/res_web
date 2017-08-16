<?php
/**
 * 文件上传配置
 * Author: Zhangzy
 */

return [
    'WWW_DOMAIN' => 'http://www.yii.com', //网站前台域名
    'ADM_DOMAIN' => 'http://adm.yii.com', //网站后台域名
    'UP_FILE_DOMAIN' => 'http://res.yii.com', //资源服务器域名 @TODO 与网站配置同步
    'UP_IMG_SIZE_LIMIT' => 1024 * 1024 * 10, //上传图片文件大小限制(单张10M)
    'UP_VIDEO_SIZE_LIMIT' => 1024 * 1024 * 200, //上传视/音频文件大小限制(单文件200M)
    'UP_FILE_SIZE_LIMIT' => 1024 * 1024 * 100, //上传文档及其他类型文件大小限制(单文件100M)
    'ACCEPT_FILE_TYPE' => [ //允许上传的文件类型
        'image' => ['gif', 'jpg', 'jpeg', 'png', 'bmp'], //图片文件类型
        'video' => ['flv', 'swf', 'mkv', 'avi', 'rm', 'rmvb', 'mpeg', 'mpg', 'ogg',
            'ogv', 'mov', 'wmv', 'mp4', 'webm', 'mp3', 'wav', 'mid'
        ], //视频文件类型
        'file' => [
            'png', 'jpg', 'jpeg', 'gif', 'bmp',
            'flv', 'swf', 'mkv', 'avi', 'rm', 'rmvb', 'mpeg', 'mpg',
            'ogg', 'ogv', 'mov', 'wmv', 'mp4', 'webm', 'mp3', 'wav', 'mid',
            'rar', 'zip', 'tar', 'gz', '7z', 'bz2', 'cab', 'iso',
            'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt', 'md', 'xml'
        ], //文件类型
        'all_file'=> [
            'png', 'jpg', 'jpeg', 'gif', 'bmp',
            'flv', 'swf', 'mkv', 'avi', 'rm', 'rmvb', 'mpeg', 'mpg',
            'ogg', 'ogv', 'mov', 'wmv', 'mp4', 'webm', 'mp3', 'wav', 'mid',
            'rar', 'zip', 'tar', 'gz', '7z', 'bz2', 'cab', 'iso',
            'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt', 'md', 'xml'
        ], //所有文件类型
    ],
    'UP_FILE_TEMP_PATH' => 'Temp/', //临时存放目录
    'UP_FILE_PATH' => '../Uploads/', //文件保存目录

    'LIST_SIZE' => 20, //列表分页大小

    'ERR_ARR' => [ //错误信息
        'URL_ILLEGAL'               => ['state' => -1, 'code' => 'URL_ILLEGAL', 'msg' => '请求路径异常'],
        'SERVER_ERROR'              => ['state' => 0, 'code' => 'SERVER_ERROR', 'msg' => '服务器异常'],
        'SUCCESS'                   => ['state' => 1, 'code' => 'SUCCESS', 'msg' => '成功'],
        'NO_FILE_UPLOAD'            => ['state' => 1000, 'code' => 'NO_FILE_UPLOAD', 'msg' => '没有选择上传文件'],
        'UP_PATH_NOT_WRITABLE'      => ['state' => 1001, 'code' => 'UP_PATH_NOT_WRITABLE', 'msg' => '上传目录没有写入权限'],
        'SAVE_PATH_MAKE_FAILED'     => ['state' => 1002, 'code' => 'SAVE_PATH_MAKE_FAILED', 'msg' => '文件保存目录创建失败'],
        'FILE_SIZE_GT_POST_SIZE'    => ['state' => 1003, 'code' => 'FILE_SIZE_GT_POST_SIZE', 'msg' => '文件大小超出php.ini中表单大小限制'],
        'FILE_SIZE_GT_UP_SIZE'      => ['state' => 1004, 'code' => 'FILE_SIZE_GT_UP_SIZE', 'msg' => '文件大小超出php.ini中上传文件大小限制'],
        'IMG_SIZE_TOO_LARGE'        => ['state' => 1005, 'code' => 'IMG_SIZE_TOO_LARGE', 'msg' => '文件大小超出限制(10M)'], //与上文大小配置同步
        'VIDEO_SIZE_TOO_LARGE'      => ['state' => 1006, 'code' => 'VIDEO_SIZE_TOO_LARGE', 'msg' => '文件大小超出限制(200M)'], //与上文大小配置同步
        'FILE_SIZE_TOO_LARGE'       => ['state' => 1007, 'code' => 'FILE_SIZE_TOO_LARGE', 'msg' => '文件大小超出限制(100M)'], //与上文大小配置同步
        'FILE_MOVE_FAILED'          => ['state' => 1008, 'code' => 'FILE_MOVE_FAILED', 'msg' => '文件传输失败'],
        'FILE_UPLOAD_FAILED'        => ['state' => 1009, 'code' => 'FILE_UPLOAD_FAILED', 'msg' => '文件上传失败'],
        'NO_FILE_TO_DELETE'         => ['state' => 1010, 'code' => 'NO_FILE_TO_DELETE', 'msg' => '没有需要删除的文件'],
        'FILE_DELETE_FAILED'        => ['state' => 1011, 'code' => 'FILE_DELETE_FAILED', 'msg' => '文件删除失败'],
        'FILE_TYPE_ERROR'           => ['state' => 1012, 'code' => 'FILE_TYPE_ERROR', 'msg' => '不支持的文件类型'],
        'NOT_HTTP_LINK'             => ['state' => 1013, 'code' => 'NOT_HTTP_LINK', 'msg' => '非http链接'],
        'IS_DEAD_LINK'              => ['state' => 1014, 'code' => 'IS_DEAD_LINK', 'msg' => '链接不可用'],
        'LINK_CONTENT_TYPE_ERROR'   => ['state' => 1015, 'code' => 'LINK_CONTENT_TYPE_ERROR', 'msg' => '链接ContentType异常'],
        'FILE_NOT_EXIST'            => ['state' => 1016, 'code' => 'FILE_NOT_EXIST', 'msg' => '文件不存在'],
    ],
];
