<?php
namespace app\admin\controller;

class Admin extends Common
{

    public function lst()
    {
        $adminRes = db('admin')->alias('a')->field('a.*,g.title')->join('auth_group g', 'a.groupid=g.id')->paginate(10);
        $this->assign([
            'adminRes' => $adminRes,
        ]);
        return view('list');
    }

    public function add()
    {
        if (request()->isPost()) {
            $data = input('post.');
            if (empty($data['admin_icon'])) {
                $this->error('请选择头像！');
            }
            //验证
            $validate = validate('admin');
            if (!$validate->scene('add')->check($data)) {
                $this->error($validate->getError());
            }
            $data['password']    = md5($data['password']);
            $data['create_time'] = time();
            $data['last_time']   = time();
            $add                 = db('admin')->insertGetId($data);
            $_data               = array(); //对应管理员和用户组
            $_data['uid']        = $add;
            $_data['group_id']   = $data['groupid'];
            $_data['admin_icon'] = $data['admin_icon'];
            $addGroupAccess      = db('authGroupAccess')->insert($_data);
            if ($add && $addGroupAccess) {
                $this->success('添加管理员成功！', 'lst');
            } else {
                $this->error('添加管理员失败！');
            }
            return;
        }
        //所属用户组
        $groupRes  = db('authGroup')->select();
        $icon_path = db('admin_icon')->where('img_type', 1)->select();
        $this->assign([
            'groupRes'  => $groupRes,
            'icon_path' => $icon_path,
        ]);
        return view();
    }

    public function edit()
    {
        if (request()->isPost()) {
            $data = input('post.');
            if (empty($data['admin_icon'])) {
                $this->error('请选择头像！');
            }
            //验证
            {
                $validate = validate('admin');
            }

            if (!$validate->scene('edit')->check($data)) {
                $this->error($validate->getError());
            }
            if ($data['password']) {
                $data['password'] = md5($data['password']);
            } else {
                unset($data['password']);
            }
            $save = db('admin')->update($data);
            db('authGroupAccess')->where(array('uid' => $data['id']))->update(['group_id' => $data['groupid']]);
            if ($save !== false) {
                if (session('id') == 1) {
                    $this->success('修改管理员成功！', 'lst');
                }
                $this->success('修改管理员成功！');
            } else {
                $this->error('修改管理员失败！');
            }
            return;
        }
        $admins    = db('admin')->field('id,uname,groupid')->find(input('id'));
        $icon_path = db('admin_icon')->where('img_type', 1)->select();
        //所属用户组
        $groupRes = db('authGroup')->select();
        $this->assign([
            'admins'    => $admins,
            'groupRes'  => $groupRes,
            'icon_path' => $icon_path,
        ]);
        return view();
    }

    public function del($id)
    {
        if ($id == 1) {
            $this->error('超级管理员不允许删除！');
        }
        $del = db('admin')->delete($id);
        if ($del) {
            $this->success('删除管理员成功！', 'lst');
        } else {
            $this->error('删除管理员失败！');
        }
    }

    //ajax修改管理员状态
    public function changestatus()
    {
        $id     = input('id');
        $admins = db('admin')->field('status')->find($id);
        $status = $admins['status'];
        if ($status == 1) {
            db('admin')->where(array('id' => $id))->update(['status' => 0]);
        } else {
            db('admin')->where(array('id' => $id))->update(['status' => 1]);
        }
    }

    //退出登录
    public function logout()
    {
        session(null);
        $this->success('退出成功！', 'Login/index');
    }

}
