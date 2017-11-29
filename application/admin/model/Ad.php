<?php
namespace app\admin\model;
use think\Model;
class Ad extends Model
{
    protected $field=true;
	protected static function init()
    {
        Ad::beforeInsert(function ($ad) {
            $data=input('post.');
            if($data['type']==1){
                if($_FILES['img_src']['tmp_name']){
                    $file = request()->file('img_src');
                    $info = $file->move(ROOT_PATH . 'public/static/index' . DS . 'ad');
                    if($info){
                        $imgSrc=$info->getSaveName();
                        $ad['img_src']=$imgSrc;
                    }
                }
            }
            if($data['on']==1){
                db('ad')->where(array('adpos_id'=>$data['adpos_id']))->update(['on'=>0]);
            }
        });
        //后置钩子
        Ad::afterInsert(function ($ad) {
            $data=input('post.');
            if($data['type']==2){
                foreach ($_FILES['fimg_src']['tmp_name'] as $k => $v) {
                    if(!$v){
                        unset($data['flink'][$k]);
                    }
                }
                sort($data['flink']);
                // 获取表单上传文件
                $files = request()->file('fimg_src');
                foreach($files as $k=>$file){
                    // 移动到框架应用根目录/public/uploads/ 目录下
                    $info = $file->move(ROOT_PATH . 'public/static/index' . DS . 'ad');
                    if($info){
                        // echo $info->getSaveName().'----'.$data['flink'][$k].'<br>';
                        $arr=array();
                        $arr['ad_id']=$ad->id;
                        $arr['fimg_src']=$info->getSaveName();
                        $arr['flink']=$data['flink'][$k];
                        db('adflash')->insert($arr);
                    }else{
                        // 上传失败获取错误信息
                        echo $file->getError();
                    }    
                }
            }
        });

        //删除广告前置钩子
        Ad::beforeDelete(function ($ad) {
           $aid=$ad->data['id'];
           $ads=Ad::find($aid);
           if($ads['type']==1){
            $imgSrc=$ads['img_src']; 
            $imgSrc=INDEXAD.$imgSrc;
            if(file_exists($imgSrc)){
                @unlink($imgSrc);
            }
           }else{
            $fimgSrcRes=db('adflash')->field('fimg_src')->where(array('ad_id'=>$aid))->select();
            foreach ($fimgSrcRes as $k => $v) {
                $fimgSrc=INDEXAD.$v['fimg_src'];
                if(file_exists($fimgSrc)){
                @unlink($fimgSrc);
                }
            }
            db('adflash')->where(array('ad_id'=>$aid))->delete();
           }
        });

        //修改广告前置钩子
        Ad::beforeUpdate(function ($ad) {
           $data=input('post.');
           if($data['type']==1){
                if($_FILES['img_src']['tmp_name']){
                    $oldimgsrc=INDEXAD.$data['oldimgsrc'];
                    if(file_exists($oldimgsrc)){
                        @unlink($oldimgsrc);
                    }
                    $file = request()->file('img_src');
                    $info = $file->move(ROOT_PATH . 'public/static/index' . DS . 'ad');
                    if($info){
                        $imgSrc=$info->getSaveName();
                        Ad::where('id',$data['id'])->update(['img_src'=>$imgSrc]);
                    }
                }
                //启用状态
                if($data['on'] == 1){
                    Ad::where('adpos_id',$data['adpos_id'])->update(['on'=>0]);
                }
           }else{
                if(isset($_FILES['fimg_src']['tmp_name'])){
                    foreach ($_FILES['fimg_src']['tmp_name'] as $k => $v) {
                        if(!$v){
                            unset($data['flink'][$k]);
                        }
                    }
                    sort($data['flink']);
                    // 获取表单上传文件
                    $files = request()->file('fimg_src');
                    foreach($files as $k=>$file){
                        // 移动到框架应用根目录/public/uploads/ 目录下
                        $info = $file->move(ROOT_PATH . 'public/static/index' . DS . 'ad');
                        if($info){
                            // echo $info->getSaveName().'----'.$data['flink'][$k].'<br>';
                            $arr=array();
                            $arr['ad_id']=$data['id'];
                            $arr['fimg_src']=$info->getSaveName();
                            $arr['flink']=$data['flink'][$k];
                            db('adflash')->insert($arr);
                        }else{
                            // 上传失败获取错误信息
                            echo $file->getError();
                        }    
                    }
                }
                //修改链接
                if(isset($data['old_flink'])){
                    foreach ($data['old_flink'] as $k => $v) {
                        db('adflash')->where(array('id'=>$k))->update(['flink'=>$v]);
                    }
                }
                
           }
            
        });

    }




    

   
}
