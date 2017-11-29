<?php
namespace app\admin\controller;
use think\Controller;
class Login extends Controller
{
    public function index(){
        if(session('uname')&&session('id')){
            $this->error('您已经登录成功，请勿重复登录！','Index/index');
        }
        if(request()->isPost()){
            $data=input('post.');
            if(!captcha_check($data['code'])){
             //验证失败
                $this->error('验证码错误！');
            };
            $loginStatus=model('admin')->login($data);
            if($loginStatus==1){
                $this->success('登录成功！','Index/index');
            }elseif($loginStatus==4){
                $this->error('当前账号已被禁用！');
            }else{
                $this->error('用户名或者密码错误！');
            }
            return;
        }
        return view();
    }


}

