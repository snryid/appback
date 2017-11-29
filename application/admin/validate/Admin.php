<?php
namespace app\admin\validate;
use think\validate;

class Admin extends validate
{
	protected $rule=[
		'uname'=>'require|unique:admin',
		'groupid'=>'require',
		'password'=>'require|min:6',
	];

	protected $message=[
		'uname.require'=>'管理员名称不得为空！',
		'uname.unique'=>'管理员名称不得重复！',
		'groupid.require'=>'所属用户组不得为空',
		'password.require'=>'管理员密码不能为空！',
		'password.min'=>'管理员密码不能少于6位！',
	];

	protected $scene=[
		'add'=>['uname','password','groupid'],
		'edit'=>['uname','password'=>'min:6','groupid'],
	];
}