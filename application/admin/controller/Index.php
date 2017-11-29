<?php
namespace app\admin\controller;

use think\db;
use think\Request;

class Index extends Common
{
    public function index()
    {
        $request = Request::instance();
        $ip      = $request->ip();

        $info = array(
            'language'             => $_SERVER['HTTP_ACCEPT_LANGUAGE'],
            'OS'                   => PHP_OS,
            'server_software'      => $_SERVER["SERVER_SOFTWARE"],
            'PHP_move'             => php_sapi_name(),
            'remote_port'          => $_SERVER['SERVER_PORT'],
            'ThinkPHP'             => THINK_VERSION,
            'updata'               => ini_get('upload_max_filesize'),
            'overtime'             => ini_get('max_execution_time') . '秒',
            'severTime'            => date("Y年n月j日 H:i:s"),
            'BJtime'               => gmdate("Y年n月j日 H:i:s", time() + 8 * 3600),
            'ServerName'           => $_SERVER['SERVER_NAME'],
            'IP'                   => gethostbyname($_SERVER['SERVER_NAME']),
            'memory'               => round((disk_free_space(".") / (1024 * 1024)), 2) . 'M',
            'register_globals'     => get_cfg_var("register_globals") == "1" ? "ON" : "OFF",
            'magic_quotes_gpc'     => (1 === get_magic_quotes_gpc()) ? 'YES' : 'NO',
            'magic_quotes_runtime' => (1 === get_magic_quotes_runtime()) ? 'YES' : 'NO',
        );
        $video   = db('video')->count();
        $image   = db('image')->count();
        $article = db('article')->count();
        $user    = db('user')->count();
        $admin   = db('admin')->count();

        // $todey = time() - (24 * 3600);
        // $todey   = date('Y-m-d H:i:s', $todey);
        // 获取今天的数据
        $video_d   = db('video')->whereTime('add_time', 'd')->count();
        $image_d   = db('image')->whereTime('add_time', 'd')->count();
        $article_d = db('article')->whereTime('time', 'd')->count();
        $user_d    = db('user')->whereTime('add_time', 'd')->count();
        $admin_d   = db('admin')->whereTime('create_time', 'd')->count();
        // 获取昨天的数据
        $video_yt    = db('video')->whereTime('add_time', 'yesterday')->count();
        // dump($video_yt);die;
        $image_yt    = db('image')->whereTime('add_time', 'yesterday')->count();
        $article_yt  = db('article')->whereTime('time', 'yesterday')->count();
        $user_yt     = db('user')->whereTime('add_time', 'yesterday')->count();
        $admin_yt    = db('admin')->whereTime('create_time', 'yesterday')->count();
        // 获取本周的数据
        $video_w    = db('video')->whereTime('add_time', 'w')->count();
        $image_w    = db('image')->whereTime('add_time', 'w')->count();
        $article_w  = db('article')->whereTime('time', 'w')->count();
        $user_w     = db('user')->whereTime('add_time', 'w')->count();
        $admin_w    = db('admin')->whereTime('create_time', 'w')->count();
         // 获取本月的数据
        $video_m    = db('video')->whereTime('add_time', 'm')->count();
        $image_m    = db('image')->whereTime('add_time', 'm')->count();
        $article_m  = db('article')->whereTime('time', 'm')->count();
        $user_m     = db('user')->whereTime('add_time', 'm')->count();
        $admin_m    = db('admin')->whereTime('create_time', 'm')->count();

        $this->assign(array(
            'video'     => $video,
            'image'     => $image,
            'article'   => $article,
            'user'      => $user,
            'info'      => $info,
            'admin'     => $admin,
            'video_d'   => $video_d,
            'image_d'   => $image_d,
            'article_d' => $article_d,
            'user_d'    => $user_d,
            'admin_d'   => $admin_d, 
            'video_yt'   => $video_yt,
            'image_yt'   => $image_yt,
            'article_yt' => $article_yt,
            'user_yt'    => $user_yt,
            'admin_yt'   => $admin_yt,
            'video_w'   => $video_w,
            'image_w'   => $image_w,
            'article_w' => $article_w,
            'user_w'    => $user_w,
            'admin_w'   => $admin_w,
            'video_m'   => $video_m,
            'image_m'   => $image_m,
            'article_m' => $article_m,
            'user_m'    => $user_m,
            'admin_m'   => $admin_m,
        ));

        $this->assign('info', $info);
        $this->assign('ip', $ip);
        return view();
    }
}
