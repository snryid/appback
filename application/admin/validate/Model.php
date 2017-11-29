<?php
namespace app\admin\validate;
use think\validate;

class Model extends validate
{
	protected $rule=[
		'model_name'=>'require|unique:model',
		'table_name'=>'require|unique:model',
		'status'=>'require|number',
	];

	protected $message=[
		'model_name.require'=>'模型名称不得为空！',
		'model_name.unique'=>'模型名称不得重复！',
		'table_name.require'=>'附加表名不能为空！',
		'table_name.unique'=>'附加表名称不得重复！',
		'status.number'=>'模型值必须是数字！',
		'status.require'=>'模型状态必选！',
	];

	protected $scene=[
		'add'=>['model_name','table_name','status'],
		'edit'=>['model_name','table_name','status'],
	];
}