<?php
namespace app\admin\controller;

class Ad extends Common
{
    protected $field=true;
    public function lst()
    {
        //获取前缀
        $prefix=config('database.prefix');
        $adposName=$prefix.'adpos';
    	$adRes=db('ad')->field('a.*,b.name')->alias('a')->join("$adposName b",'a.adpos_id = b.id')->paginate(6);
    	$this->assign([
    		'adRes'=>$adRes,
    		]);
        return view('list');
    }

    public function add()
    {
    	if(request()->isPost()){
    		$data=input('post.');
    		// 验证
    		$validate=validate('ad');
    		if(!$validate->check($data)){
    			$this->error($validate->getError());
    		}
            $ad=model('ad');
            $ad->data($data);
            $add=$ad->save();
    		if($add){
    			$this->success('添加广告成功！','lst');
    		}else{
    			$this->error('添加广告失败！');
    		}
    	}
        $adposRes=db('adpos')->field('id,name')->select();
        $this->assign([
            'adposRes'=>$adposRes,
            ]);
        return view();
    }

    public function edit($id){
    	if(request()->isPost()){
    		$data=input('post.');
    		//验证
    		$validate=validate('ad');
    		if(!$validate->check($data)){
    			$this->error($validate->getError());
    		}
    		$ad=model('ad');
            $save=$ad->update($data);
    		if($save!==false){
    			$this->success('修改广告成功！','lst');
    		}else{
    			$this->error('修改广告失败！');
    		}
    	}
        $adposRes=db('adpos')->field('id,name')->select();
    	$ad=db('ad')->find($id);
        if($ad['type']==2){
            $adflashRes=db('adflash')->where(array('ad_id'=>$id))->select();
            $this->assign([
                'adflashRes'=>$adflashRes //编辑轮换广告的时候显示轮换广告对应的广告数据
                ]);
        }else{
            $this->assign([
                'adflashRes'=>'' //编辑图片广告的时候为了防止轮换广告无数据分配出错
                ]);
        }
    	$this->assign([
    		'ad'=>$ad,
            'adposRes'=>$adposRes
    		]);
    	return view();
    }

    public function del($id){
        $ad=model('ad');
    	$del=$ad::destroy($id);
    	if($del){
    		$this->success('删除广告成功！','lst');
    	}else{
    		$this->error('删除广告失败！');
    	}
    }

    public function changeStatus(){
        $id=input('id');
        $adposId=input('adposId');
        $ads=db('ad')->find($id);
        if($ads['on']==0){
            db('ad')->where(array('adpos_id'=>$adposId))->update(['on'=>0]);
            db('ad')->where(array('id'=>$id))->update(['on'=>1]);
        }else{
            db('ad')->where(array('id'=>$id))->update(['on'=>0]);
        }
    }
    //异步删除轮换广告记录
    public function ajaxdel(){
        $id=input('id');
        $adflash=db('adflash')->find($id);
        $imgSrc=$adflash['fimg_src'];
        $imgSrc=INDEXAD.$imgSrc;
        if(file_exists($imgSrc)){
            @unlink($imgSrc);
        }
        $del=db('adflash')->delete($id);
        if($del){
            echo 1;//删除成
        }else{
            echo 2;
        }
    }


}

