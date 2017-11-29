<?php
use think\Route;
//配置路由

//api.tp5.com ==> www.tp5.com/index.php/api
Route::domain('api', 'api');

//获取验证码
Route::get('code/:time/:token/:username/:is_exist', 'code/get_code');

//用户注册
Route::post('user/register', 'user/register');

//用户登陆
Route::post('user/login', 'user/login');

//用户第三方登陆
Route::post('user/qq', 'user/qq_login');

//用户上传头像
Route::post('user/icon', 'user/upload_head_img');

//用户修改密码
Route::post('user/change_pwd', 'user/change_pwd');

//用户找回密码
Route::post('user/find_pwd', 'user/find_pwd');

//检测用户是否收藏
Route::post('user/check_collect', 'user/check_collect');

//用户收藏
Route::post('user/user_collect', 'user/user_collect');

//用户取消收藏
Route::post('user/user_deselect', 'user/user_deselect');

//用户个人中心获取收藏
Route::post('user/get_collect', 'user/get_collect');

//用户绑定手机号/邮箱
Route::post('user/bind_username', 'user/bind_username');

//用户修改昵称
Route::post('user/nickname', 'user/set_nickname');

//app获取视频类型
Route::post('video/video_type', 'video/video_type');

//app获取视频类型总数
Route::post('video/sum_video', 'video/sum_video');

//app获取视频
Route::post('video/port_video', 'video/port_video');

//app获取图片
Route::post('image/app_image', 'image/app_image');

//app获取新闻类型
Route::post('news/new_type', 'news/new_type');

//app获取新闻type下的总数
Route::post('news/sum_news', 'news/sum_news');

//app获取新闻
Route::post('news/query', 'news/query');

//app获取推荐新闻
Route::post('news/recommend', 'news/recommend');

//app获取朋友圈总数
Route::post('friends/sum_tribune', 'friends/sum_tribune');

//app获取交流内容
Route::post('friends/exchange', 'friends/exchange');

//app获取评论总数
Route::post('friends/sum_comment', 'friends/sum_comment');

//app获取评论内容
Route::post('friends/discuss', 'friends/discuss');

//app添加评论内容
Route::post('friends/add_comment', 'friends/add_comment');

//app发表朋友圈内容
Route::post('friends/pub_article', 'friends/pub_article');

//app朋友圈点赞
Route::post('friends/lick', 'friends/lick');

//app朋友圈删除内容
Route::post('friends/del_content', 'friends/del_content');

//app获取图片类型
Route::post('image/img_type', 'image/image_type');

//app获取图片总数
Route::post('image/img_sum', 'image/image_sum');

//app获取评论内容
Route::post('image/get_img', 'image/get_image');

//app获取评论内容
Route::post('user/get_publish', 'user/get_publish');
