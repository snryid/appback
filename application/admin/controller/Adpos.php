<?php
namespace app\admin\controller;

class Adpos extends Common
{
    public function lst()
    {
    	$adposRes=db('adpos')->paginate(2);
    	$this->assign([
    		'adposRes'=>$adposRes,
    		]);
        return view('list');
    }

    public function add()
    {
    	if(request()->isPost()){
    		$data=input('post.');
    		//验证
    		$validate=validate('Adpos');
    		if(!$validate->check($data)){
    			$this->error($validate->getError());
    		}
    		$add=db('adpos')->insert($data);
    		if($add){
    			$this->success('添加广告位成功！','lst');
    		}else{
    			$this->error('添加广告位失败！');
    		}
    	}
        return view();
    }

    public function edit($id){
    	if(request()->isPost()){
    		$data=input('post.');
    		//验证
    		$validate=validate('Adpos');
    		if(!$validate->check($data)){
    			$this->error($validate->getError());
    		}
    		$save=db('adpos')->update($data);
    		if($save!==false){
    			$this->success('修改广告位成功！','lst');
    		}else{
    			$this->error('修改广告位失败！');
    		}
    	}
    	$adpos=db('adpos')->find($id);
    	$this->assign([
    		'adpos'=>$adpos,
    		]);
    	return view();
    }

    public function del($id){
    	$adpos=model('adpos');
        $del=$adpos::destroy($id);
    	if($del){
    		$this->success('删除广告位成功！','lst');
    	}else{
    		$this->error('删除广告位失败！');
    	}
    }
}

