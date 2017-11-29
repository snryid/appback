<?php
namespace app\admin\validate;
use think\Validate;

class Video extends Validate {

	protected $rule = [
		'title' => 'unique:video|require',
		'vid' => 'require',
		'des_content' => 'require',
	];

	protected $message = [
		'title.require' => '视频标题不得为空！',
		'title.unique' => '视频标题不得重复！',
		'title.max' => '文章标题长度大的大于30个字符！',
		'vid.require' => '视频所属栏目不得为空！',
		'des_content.require' => '视频描述不得为空！',
	];

	protected $scene = [
		'add' => ['title', 'vid', 'des_content'],
		'edit' => ['title', 'vid'],
	];

}
