<?php
namespace app\admin\validate;
use think\Validate;
class Image extends Validate
{

    protected $rule=[
        'title'=>'unique:image|require',
        'type_id'=>'require',
        'image_content'=>'require',
    ];


    protected $message=[
        'title.require'=>'图片标题不得为空！',
        'title.unique'=>'图片标题不得重复！',
        // 'title.max'=>'文章标题长度大的大于25个字符！',
        'type_id.require'=>'图片所属栏目不得为空！',
        'image_content.require'=>'图片描述不得为空！',
    ];

    protected $scene=[
        'add'=>['title','type_id','image_content'],
        'edit'=>['title','type_id'],
    ];





    

    




   

	












}
