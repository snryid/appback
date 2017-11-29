<?php
namespace app\admin\validate;
use think\validate;

class Ad extends validate
{
	protected $rule=[
		'adpos_id'=>'require|number',
		'ad_name'=>'require|unique:ad',
		'on'=>'require|number',
		'type'=>'require|number',
	];

	protected $message=[
		'adpos_id.require'=>'所属广告位不得为空！',
		'adpos_id.unique'=>'所属广告位id必须是数字！',
		'ad_name.require'=>'广告名称不得为空！',
		'ad_name.unique'=>'广告名称不得重复！',
		'on.number'=>'广告开启状态值必须是数字！',
		'on.require'=>'广告开启状态值不得为空！',
		'type.number'=>'广告类型值必须是数字！',
		'type.require'=>'广告类型值不得为空！',
	];

	protected $scene = [
        'add'  =>  ['adpos_id','ad_name','on','type'],
        'edit'  =>  ['adpos_id','ad_name','on','type'],
    ];

}