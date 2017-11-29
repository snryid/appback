<?php
namespace app\admin\validate;
use think\validate;

class ModelFields extends validate
{
	protected $rule=[
		'model_id'=>'require',
		'field_type'=>'require',
		'field_cname'=>'require|unique:model_fields',
		'field_ename'=>'require|unique:model_fields',
	];

	protected $message=[
		'model_id.require'=>'所属模型不得为空！',
		'field_type.require'=>'字段类型不得为空！',
		'field_cname.unique'=>'字段中文名称不得重复！',
		'field_cname.require'=>'字段中文名称不能为空！',
		'field_ename.require'=>'字段英文名称不能为空！',
		'field_ename.unique'=>'字段英文名称不得重复！',
	];

	protected $scene=[
		'add'=>['model_id','field_type','field_cname','field_ename'],
		'edit'=>['model_id','field_type','field_cname','field_ename'],
	];
}