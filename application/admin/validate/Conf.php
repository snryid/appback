<?php
namespace app\admin\validate;
use think\validate;

class Conf extends validate
{
	protected $rule=[
		'cname'=>'require|max:60|unique:conf',
		'ename'=>'require|max:60|unique:conf',
		'dt_type'=>'require|number',
		'cf_type'=>'require|number',
	];

	protected $message=[
		'cname.require'=>'中文名称不得为空！',
		'cname.email'=>'必须是邮箱格式！',
		'cname.unique'=>'中文名称不得重复！',
		'ename.unique'=>'英文名称不得重复！',
		'ename.require'=>'英文名称不得为空！',
	];

	protected $scene=[
		'add'=>['cname','ename','dt_type','cf_type'],
		'edit'=>['cname','ename','dt_type','cf_type'],
	];
}