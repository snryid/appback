<?php
namespace app\admin\controller;

use auth\Auth;
use think\Controller;
use think\Image;
use think\Request;

header("Content-type: text/html; charset=utf-8");
class Common extends Controller
{
    public $config;

    public function _initialize()
    {
        if (!session('uname')) {
            $this->error('请先登录系统！', 'Login/index');
        }
        $request = Request::instance();
        $module  = $request->module();
        $con     = $request->controller();
        $action  = $request->action();
        $name    = $module . '/' . $con . '/' . $action; //组合规则name

        $this->assign([
            'con'  => $con,
            'view' => $action,
            'name' => $name,
        ]);
        $this->getConf();
        $this->admin_icon();

        $auth = new Auth();
        // 菜单
        $group       = $auth->getGroups(session('id'));
        $rules       = explode(',', $group[0]['rules']);
        $menu        = array();
        $map['pid']  = ['=', 0];
        $map['show'] = ['=', 1];
        $map['id']   = ['in', $rules];
        $_map['id']  = ['in', $rules];
        $menu        = db('authRule')->where($map)->select();
        foreach ($menu as $k => $v) {
            $menu[$k]['children'] = db('authRule')->where($_map)->where(array('pid' => $v['id'], 'show' => 1))->select();
            foreach ($menu[$k]['children'] as $k1 => $v1) {
                $menu[$k]['children'][$k1]['children'] = db('authRule')->where($_map)->where(array('pid' => $v1['id'], 'show' => 1))->select();
            }
        }
        $this->assign([
            'menu' => $menu,
        ]);
        // end菜单
        if (session('id') !== 1) {
            $check = $auth->check($name, session('id'));
            if (!$check) {
                $this->error('没有该操作权限！');
            }
        }

    }

    public function getConf()
    {
        $confRes  = array();
        $_confRes = db('conf')->field('ename,value')->select();
        foreach ($_confRes as $v) {
            $confRes[$v['ename']] = $v['value'];
        }
        $this->config = $confRes;
    }

    public function admin_icon()
    {
        $where['id'] = session('id');
        $path        = db('admin')->where($where)->find();
        $this->assign('admin_icon', $path['admin_icon']);
    }

    /**
     * 生成缩略图
     * @param  [type] $path         [打开文件路劲]
     * @param  [type] $thumb_path   [新的路径]
     * @return [string]             [缩略图路径]
     */
    public function image_thumb($path, $type, $thumb_path)
    {
        $image = Image::open(ROOT_PATH . 'public' . $path);
        switch ($type) {
            case 'image':
                $image->thumb(300, 300, Image::THUMB_SCALING)->save(ROOT_PATH . 'public' . $thumb_path);
                break;
        }
        return $thumb_path;
    }

}
