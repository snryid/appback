<?php
namespace app\admin\controller;

use app\admin\controller\Common;
use app\admin\model\User as UserModel;
use think\Request;

class User extends Common
{

    public function index()
    {

        $users = db('user')->order('id desc')->paginate(15);
        $this->assign('users', $users);
        return view();
    }

    public function add()
    {
        $request = Request::instance();
        $ip      = $request->ip();
        if (request()->isPost()) {
            $data = input('post.');
            if ($data['user_name'] == '') {
                $this->error('会员名不能为空！');
            }
            if (!($data['user_passa'] == $data['user_passb'])) {
                $this->error('密码不一致！');
            }

            $datainfo['birthday']      = $data['birthday_year'] . '-' . $data['birthday_month'] . '-' . $data['birthday_day'];
            $datainfo['user_name']     = $data['user_name'];
            $datainfo['user_nickname'] = $data['user_nickname'];
            $datainfo['user_pwd']      = md5($data['user_passa']);
            $datainfo['sex']           = $data['sex'];
            $datainfo['user_mobile']   = $data['user_mobile'];
            $datainfo['user_email']    = $data['user_email'];
            $datainfo['checked']       = $data['checked'];
            $datainfo['add_time']      = time();
            $datainfo['last_ip']       = $ip;

            if ($datainfo['user_name'] != '') {

                $comparison = db('user')->where('user_name', $datainfo['user_name'])->find();

                if ($datainfo['user_name'] !== $comparison['user_name']) {

                    $inse = db('user')->insert($datainfo);
                    if ($inse) {
                        $this->success('添加会员成功！', url('index'));
                    } else {
                        $this->error('添加会员失败！');
                    }
                } else {
                    $this->error('该会员已存在！');
                }
            }

        }
        return view();
    }

    public function edit()
    {

        $request = Request::instance();
        $ip      = $request->ip();
        if (request()->isPost()) {
            $data = input('post.');
            if (!($data['user_passa'] == $data['user_passb'])) {
                $this->error('密码不一致！');
            }
            if ($data['user_name'] == '') {
                $this->error('会员名不能为空！');
            }
            $datainfo['birthday']      = $data['birthday_year'] . '-' . $data['birthday_month'] . '-' . $data['birthday_day'];
            $datainfo['user_name']     = $data['user_name'];
            $datainfo['user_nickname'] = $data['user_nickname'];
            $datainfo['user_pwd']      = md5($data['user_passa']);
            $datainfo['sex']           = $data['sex'];
            $datainfo['user_mobile']   = $data['user_mobile'];
            $datainfo['user_email']    = $data['user_email'];
            $datainfo['checked']       = $data['checked'];
            $datainfo['edit_time']     = time();
            $datainfo['last_ip']       = $ip;

            if ($datainfo['user_name'] != '') {

                $inse = db('user')->where('id', $data['id'])->update($datainfo);
                if ($inse) {
                    $this->success('修改成功！', url('index'));
                } else {
                    $this->error('修改失败！');
                }

            }

        }

        $user = db('user')->where(array('id' => input('id')))->find();
        if ($user['edit_time'] != 0) {
            $edit_time = date('Y-m-d H:i:s', $user['edit_time']);
        } else {
            $edit_time = '0';
        }
        $shengri = explode('-', $user['birthday']);
        $this->assign(array(
            'user'      => $user,
            'add_time'  => date('Y-m-d H:i:s', $user['add_time']),
            'edit_time' => $edit_time,
            'year'      => $shengri['0'],
            'day'       => $shengri['1'],
            'month'     => $shengri['2'],
        ));
        return view();
    }

    public function del($id)
    {
        $user   = new UserModel();
        $delnum = $user->deluser($id);
        if ($delnum == '1') {
            $this->success('删除会员员成功！', url('index'));
        } else {
            $this->error('删除管会员失败！');
        }
    }

}
