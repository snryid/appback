<?php
namespace app\admin\controller;

use app\admin\controller\Common;
use think\Db;
use think\File;

class Image extends Common
{
    public function index()
    {
        if (request()->isPost()) {
            $data  = input('post.');
            $image = db('image')->alias('i')->where('i.type_id', $data['type_id'])->order('id desc')->join('image_type t', 'i.type_id=t.type_id', 'left')->paginate(10);
        } else {
            $image = db('image')->alias('i')->order('id desc')->join('image_type t', 'i.type_id=t.type_id', 'left')->paginate(10);
        }
        $image_type = db('image_type')->select();
        $this->assign(array(
            'image'      => $image,
            'image_type' => $image_type,
        ));
        return view();
    }

    /**
     * 相册上传图片分存
     * @return [type] [description]
     */
    public function add()
    {
        if (request()->isPost()) {
            $data              = input('post.');
            $data['add_time']  = date('Y-m-d H:i:s', time());
            $file_path         = $this->update_file('image'); //上传图片
            $data['image_url'] = $file_path['image_url'];
            $data['thumb']     = $file_path['thumb'];
            //插入数据库
            $abc = db('image')->insert($data);
            if ($abc) {
                $this->success('添加图片成功', url('index'));
            } else {
                $this->error('添加图片失败！');
            }
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
        if (input('id')) {
            $data    = input('id');
            $dlefile = db('image')->where('id', $data)->find();
            if ($dlefile['thumb']) {
                $file_path = ROOT_PATH . 'public' . $dlefile['thumb'];
                $file_path = str_replace('\\', '/', $file_path);

                /*** 判断文件是否存在   ****/
                if (file_exists($file_path)) {
                    $return = @unlink($file_path); //删除文件
                }
                clearstatcache(); //清除文件返回信息缓存
            }
            if ($return) {
                $res = db('image')->where('id', $data)->delete();
            }
            if ($res) {
                $this->success('删除图片成功！', url('index'));
            } else {
                $this->error('删除图片失败！');
            }
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

    /**
     * 上传文件
     */
    public function update_file($type = '')
    {

        if ($_FILES['image']) {
            $file = request()->file('image');
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'images');
            if ($info) {
                $path                    = '/uploads' . DS . 'images' . DS . $info->getSaveName();
                $image_path['image_url'] = str_replace('\\', '/', $path); //替换反斜线

                /***** 生成缩略图   *****/
                $thumb_path = '/uploads' . DS . 'images' . DS . date("Ymd", time()) . DS . 'thumb_' . $info->getFilename(); //重组文件名，防止覆盖
                $thumb_path = str_replace('\\', '/', $thumb_path);
                if (!empty($type)) {
                    $image_path['thumb'] = $this->image_thumb($path, $type, $thumb_path);
                }
            }
        }

        return $image_path;
    }

}
