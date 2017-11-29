<?php
namespace app\admin\controller;

use app\admin\controller\Common;
use app\admin\model\Image as ImageModel;
use think\Db;
use think\File;

class Image extends Common
{
    public function index()
    {

        $image      = Db::table('app_image')->alias('i')->order('id desc')->join('app_image_type t', 'i.type_id=t.type_id', 'left')->paginate(10);
        $image_type = db('image_type')->where('parent_id', 0)->select();
        $this->assign(array(
            'image'      => $image,
            'image_type' => $image_type,
        ));

        return view();
    }

    public function add()
    {

        if (request()->isPost()) {
            $data             = input('post.');
            $data['add_time'] = date('Y-m-d H:i:s', time());
            $validate         = \think\Loader::validate('Image');
            if (!$validate->scene('add')->check($data)) {
                $this->error($validate->getError());
            }
            if ($_FILES['image']) {
                $file = request()->file('image');
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'images');
                if ($info) {
                    $path          = '/uploads' . DS . 'images/' . $info->getSaveName();
                    $data['thumb'] = str_replace('\\', '/', $path);
                    //插入数据库
                    $abc = db('image')->insert($data);
                    if ($abc) {
                        $this->success('添加图片成功', url('index'));
                    } else {
                        $this->error('添加图片失败！');
                    }
                } else {
                    // 上传失败获取错误信息
                    echo $file->getError();
                }
            }
            return;
        }

        $res = db('image_type')->select();
        $this->assign('res', $res);
        return view();
    }

    public function edit()
    {
        if (request()->isPost()) {
            $data              = input('post.');
            $data['edit_time'] = date('Y-m-d H:i:s', time());
            $validate          = \think\Loader::validate('Image');
            if (!$validate->scene('edit')->check($data)) {
                $this->error($validate->getError());
            }

            $res = db('image')->update($data);
            if ($res) {
                $this->success('修改图片成功！', url('index'));
            } else {
                $this->error('修改图片失败！');
            }
            return;
        }

        $data = db('image')->where(array('id' => input('id')))->find();
        $res  = db('image_type')->select();
        $this->assign(array(
            'img' => $data,
            'res' => $res,
        ));
        return view();
    }

    public function del()
    {
        if (ImageModel::destroy(input('id'))) {
            $this->success('删除图片成功！', url('index'));
        } else {
            $this->error('删除图片失败！');
        }
    }

    public function class_list()
    {

        $image_type = db('image_type')->order('type_id desc')->paginate(10);
        $this->assign('image_type', $image_type);
        return view();
    }

    public function class_add()
    {

        if (request()->isPost()) {
            $data               = input('post.');
            $data['add_time']   = date('Y-m-d H-i-s', time());
            $where['type_name'] = $data['type_name'];
            if (!empty($data)) {
                $res = db('image_type')->where($where)->find();
                if (!$res) {
                    $ret = db('image_type')->insert($data);
                    if ($ret) {
                        $this->success('添加分类成功！', url('class_list'));
                    } else {
                        $this->error('添加分类失败！');
                    }
                } else {
                    $this->error('该分类已存在！');
                }

            }

        }

        $image_type = db('image_type')->select();
        $this->assign('image_type', $image_type);
        return view();
    }

    public function class_edit()
    {

        if (request()->isPost()) {
            $data             = input('post.');
            $data['add_time'] = date('Y-m-d H-i-s', time());
            if (!empty($data)) {
                $ret = db('image_type')->where('type_name', $data['type_name'])->update($data);
                if ($ret) {
                    $this->success('修改分类成功！', url('class_list'));
                } else {
                    $this->error('修改分类失败！');
                }
            }
        }

        $res = db('image_type')->where('type_id', input('type_id'))->find();
        $this->assign('res', $res);
        return view();
    }
    public function class_del()
    {
        if (!empty(input('type_id'))) {
            $ret = db('image_type')->where('type_id', input('type_id'))->delete();
            if ($ret) {
                $this->success('删除成功！', url('class_list'));
            } else {
                $this->error('删除失败！');
            }
        }

    }

}
