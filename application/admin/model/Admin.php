<?php
namespace app\admin\model;
use think\Model;
class Admin extends Model
{
    
    public function login($data){
        $uname=$data['uname'];
        $password=md5($data['password']);
        $admins=Admin::get(['uname'=>$uname]);
        if($admins){
            $_password=$admins['password'];
            if($_password==$password){
                if($admins['status']==0){
                    return 4;//管理员被禁用
                }
                session('uname',$uname);
                session('id',$admins['id']);
                return 1;//密码正确可以登录
            }else{
                return 2;//密码出错
            }
        }else{
            return 3;//用户不存在
        }
    }



    

   
}
