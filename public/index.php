<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
//后台删除图片用的相对路径
define('ADMINIMG', __DIR__ . '/../public/static/admin/uploads/cateimg/');
//后台删除图片用的相对路径
define('ADMIN_STATIC', __DIR__ . '/../public/static/admin/');
//前台删除图片用的相对路径
define('INDEXIMG', __DIR__ . '/../public/static/index/uploads/img/');
//删除附件图片
define('INDEXATT', __DIR__ . '/../public/static/index/uploads/att/');
//删除广告附件图片
define('INDEXAD', __DIR__ . '/../public/static/index/ad/');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
