<?php
namespace app\admin\controller;

use app\admin\controller\Common;
use think\Controller;
use think\Db;
use think\File;
use think\Request;

class Friends extends Common
{
    public function index()
    {
        $tribune = db('tribune')->order('tribune_id desc')->paginate(10);
        $this->assign(array(
            'tribune' => $tribune,
        ));
        return view();
    }

    public function add()
    {
        if (request()->isPost()) {
            $data = input('post.');
            if ($data) {
                $data['add_time']  = date('Y-m-d H:i:s', time());
                $data['user_id']   = session('id');
                $data['user_name'] = session('uname');
                $dataID            = db('tribune')->insertGetId($data);
                if ($dataID) {
                    $imgarray = $this->upload_file();
                    $result   = array();
                    foreach ($imgarray as $value) {
                        $imgdata['add_time']   = date('Y-m-d H:i:s', time());
                        $imgdata['tribune_id'] = $dataID;
                        $imgdata['image_url']  = $value['image_url'];
                        $imgdata['thumb_url']  = $value['thumb_url'];
                        $result[]              = $imgdata;
                    }
                }
                $return = db('tribune_img')->insertAll($result);
                if ($return) {
                    $this->success('发表成功', url('index'));
                } else {
                    $this->error('发表失败');
                }
            } else {
                $this->error('上传图片失败');
            }

        }
        return view();
    }
    //上传图片
    public function upload_file()
    {
        $files     = request()->file('image');
        $file_path = array();
        foreach ($files as $file) {
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'friends');
            if ($info) {
                $path  = '/uploads' . DS . 'friends/' . $info->getSaveName();
                $image = str_replace('\\', '/', $path); //替换反斜线

                /***** 生成缩略图   *****/
                $thumb_path = '/uploads' . DS . 'friends' . DS . date("Ymd", time()) . DS . 'thumb_' . $info->getFilename(); //重组文件名，防止覆盖
                $thumb_path = str_replace('\\', '/', $thumb_path);
                if (!empty($path)) {
                    $thumb = $this->image_thumb($path, $thumb_path);
                }
                $file_path[] = array('image_url' => $image, 'thumb_url' => $thumb);
            } else {
                return false;
            }
        }
        return $file_path;
    }

    public function edit()
    {
        if (request()->isPost()) {
            $data             = input('post.');
            $data['add_time'] = date('Y-m-d H:i:s', time());
            $res              = db('tribune')->where(array('tribune_id' => input('tribune_id')))->update($data);
            if ($res) {
                $this->success('修改成功', url('index'));
            } else {
                $this->error('修改失败');
            }
        }

        $tribune     = db('tribune')->where(array('tribune_id' => input('tribune_id')))->find();
        $tribune_img = db('tribune_img')->where(array('tribune_id' => input('tribune_id')))->select();
        $img_path    = array();
        foreach ($tribune_img as $value) {
            $img_path[] = $value['image_url'];
        }
        $this->assign('tribune', $tribune);
        $this->assign('tribune_img', $img_path);
        return view();
    }

    public function trb_del()
    {
        if (input('tribune_id')) {
            $del = db('tribune')->where(array('tribune_id' => input('tribune_id')))->delete();
            if ($del) {
                $this->success('删除成功', url('index'));
            } else {
                $this->error('删除失败');
            }
        }
    }

    public function check()
    {

        $where   = array('tribune_id' => input('tribune_id'));
        $comment = db('tribune_comment')->where($where)->order('comment_id desc')->paginate(10);
        $this->assign(array(
            'comment' => $comment,
            'tribune' => input('tribune_title'),
        ));
        return view();
    }

    public function check_del()
    {
        if (input('comment_id')) {
            $del = db('tribune_comment')->where(array('comment_id' => input('comment_id')))->delete();
            if ($del) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
    }

    public function chenge()
    {
        $data['comment_status'] = input('comment_status');
        if (!empty(input())) {
            $ret = db('tribune_comment')->where('comment_id', input('comment_id'))->update($data);
            if ($ret == 1) {
                $this->success('修改成功！');
            } else {
                $this->error('修改失败！');
            }
        }
    }

    //ajax异步修改模型状态
    public function changestatus()
    {
        if (request()->isAjax()) {
            $modelid = input('modelid');
            $status  = db('tribune_comment')->field('comment_status')->where('comment_id', $modelid)->find();
            $status  = $status['comment_status'];
            if ($status == 1) {
                db('tribune_comment')->where('comment_id', $modelid)->update(['comment_status' => 0]);
                echo 1; //由显示改为隐藏
            } else {
                db('tribune_comment')->where('comment_id', $modelid)->update(['comment_status' => 1]);
                echo 2; //由隐藏改为显示
            }
        } else {
            $this->error("非法操作！");
        }
    }

}
