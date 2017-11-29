<?php
namespace app\admin\controller;

class AuthRule extends Common
{
    public function lst(){
        $ruleRes=db('authRule')->select();
        $ruleTree=model('authRule')->ruletree($ruleRes);
        $this->assign([
            'ruleTree'=>$ruleTree,
            ]);
        return view('list');
    }

    public function add(){
        if(request()->isPost()){
            $data=input('post.');
            //验证
            $validate=validate('authRule');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            $add=db('authRule')->insert($data);
            if($add){
                $this->success('添加规则成功！','lst');
            }else{
                $this->error('添加规则失败！');
            }
            return;
        }
        $ruleRes=db('authRule')->select();
        $ruleTree=model('authRule')->ruletree($ruleRes);
        $this->assign([
            'ruleTree'=>$ruleTree,
            ]);
        return view();
    }

    public function edit(){
        if(request()->isPost()){
            $data=input('post.');
            //验证
            $validate=validate('authRule');
            if(!$validate->scene('edit')->check($data)){
                $this->error($validate->getError());
            }
            $save=db('authRule')->update($data);
            if($save!==false){
                $this->success('修改规则成功！','lst');
            }else{
                $this->error('修改规则失败！');
            }
            return;
        }
        $id=input('id');
        $rules=db('authRule')->find($id);
        $ruleRes=db('authRule')->select();
        $ruleTree=model('authRule')->ruletree($ruleRes);
        $this->assign([
            'ruleTree'=>$ruleTree,
            'rules'=>$rules,
            ]);
        return view();
    }

    public function del($id){
        $cid=model('authRule')->childrenids($id);
        $cid[]=$id;
        $del=db('authRule')->delete($cid);
        if($del){
            $this->success('删除成功！','lst');
        }else{
            $this->error('删除失败！');
        }
    }

    public function ajaxlst(){
        if(request()->isAjax()){
        $ruleid=input('ruleid');
        $sonids=model('authRule')->childrenids($ruleid);
        echo json_encode($sonids);
        }else{
            $this->error('非法操作！');
        }
    }


}

