<?php
namespace app\admin\model;

use app\admin\model\Admin as Admin;
use think\Model;

class User extends Model
{

    public function adduser($data)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }
        if ($data['password']) {
            $data['password'] = md5($data['password']);
        }
        $userData             = array();
        $userData['name']     = $data['name'];
        $userData['password'] = $data['password'];
        if ($this->save($userData)) {
            $groupAccess['uid']      = $this->id;
            $groupAccess['group_id'] = $data['group_id'];
            db('auth_group_access')->insert($groupAccess);
            return true;
        } else {
            return false;
        }

    }

    public function getuser()
    {
        return $this::paginate(5, false, [
            'type'     => 'boot',
            'var_page' => 'page',
        ]);
    }

    public function saveuser($data, $users)
    {
        if (!$data['name']) {
            return 2; //管理员用户名为空
        }
        if (!$data['password']) {
            $data['password'] = $users['password'];
        } else {
            $data['password'] = md5($data['password']);
        }
        db('auth_group_access')->where(array('uid' => $data['id']))->update(['group_id' => $data['group_id']]);
        return $this::update(['name' => $data['name'], 'password' => $data['password']], ['id' => $data['id']]);

    }

    public function deluser($id)
    {
        if ($this::destroy($id)) {
            return 1;
        } else {
            return 2;
        }
    }

    public function login($data)
    {
        $user = Admin::getByName($data['name']);
        if ($user) {
            if ($user['password'] == md5($data['password'])) {
                session('id', $user['id']);
                session('name', $user['name']);
                return 2; //登录密码正确的情况
            } else {
                return 3; //登录密码错误
            }
        } else {
            return 1; //用户不存在的情况
        }

    }

}
