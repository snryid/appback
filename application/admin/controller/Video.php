<?php
namespace app\admin\controller;

use app\admin\controller\Common;
use app\admin\model\Video as VideoModel;
use think\Db;
use think\File;

header("Content-type: text/html; charset=utf-8");
class Video extends Common
{
    public function index()
    {

        $res    = db('video')->alias('v')->order('id desc')->join('video_type t', 'v.vid=t.type_id', 'left')->paginate(10);
        $record = db('video')->count();
        $this->assign('video', $res);
        $this->assign('record', $record);
        return view();
    }

    //  视频上传

    public function add()
    {
        if (request()->isPost()) {
            $data             = input('post.');
            $data['add_time'] = time();
            $validate         = \think\Loader::validate('Video');
            if (!$validate->scene('add')->check($data)) {
                $this->error($validate->getError());
            }
            if (is_numeric($data['time'])) {
                $datatime = $data['time'];
            } else {
                $datatime = '';
            }
            if ($_FILES['video']) {
                $file = request()->file('video');
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'video');
                if ($info) {
                    $path              = '/uploads' . DS . 'video' . DS . $info->getSaveName();
                    $video_path        = str_replace('\\', '/', $path);
                    $data['video_url'] = $video_path;
                    $thumb             = $info->getFilename();
                    $data['thumb_url'] = $this->getVideoCover($video_path, $datatime, $thumb);
                }
            }
            $result = db('video')->insert($data);
            if ($result) {
                $this->success('添加视频成功', url('index'));
            } else {
                $this->error('添加视频失败！');
            }
        }
        $res_class = db('video_type')->select();
        $this->assign('res_class', $res_class);
        return view();
    }

    //编辑视频
    public function edit()
    {
        if (request()->isPost()) {
            $data              = input('post.');
            $data['edit_time'] = time();
            $validate          = \think\Loader::validate('Video');
            if (!$validate->scene('edit')->check($data)) {
                $this->error($validate->getError());
            }

            $video = new VideoModel;
            $save  = $video->update($data);
            if ($save) {
                $this->success('修改视频成功！', url('index'));
            } else {
                $this->error('修改视频失败！');
            }
            return;
        }
        $vid = db('video')->where(array('id' => input('id')))->find();
        $this->assign(array('vid' => $vid));
        $res = db('video_type')->select();
        $this->assign('video_type', $res);
        return view();
    }

    //删除视频
    public function del()
    {
        if (VideoModel::destroy(input('id'))) {
            $this->success('删除视频成功！', url('index'));
        } else {
            $this->error('删除视频失败！');
        }
    }

    //  视频分类列表
    public function class_list()
    {
        $video_type = db('video_type')->order('type_id desc')->paginate(10);
        $this->assign('video_type', $video_type);
        return view();
    }

    //  增加视频分类
    public function class_add()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $qian = array(" ", "　", "\t", "\n", "\r");
            $hou  = array("", "", "", "", "");
            $data = str_replace($qian, $hou, $data);
            //插入数据库
            if (!$data['class_video']) {
                $this->error('新增分类不能为空！', url('class_add'));
            }
            $sql = db('video_type')->where('class_video', $data['class_video'])->select();

            if (!$sql) {
                $up_class = db('video_type')->insert($data);
                if ($up_class) {
                    $this->success('新增分类成功！', url('class_list'));
                }
            } else {
                $this->error('分类已存在！');
            }
        }
        return view();
    }

    //  视频分类列表
    public function class_edit()
    {
        $res = db('video_type')->select();
        $this->assign('video_type', $res);
        return view();
    }

    //删除分类
    public function class_del()
    {
        $del = db('video_type')->delete(input('id'));
        if ($del) {
            $this->success('删除分类成功！', url('class_list'));
        } else {
            $this->error('删除分类失败！');
        }

    }

    /**
     * [获取视频缩略图]
     * @param  [type] $path [视频路径]
     * @param  [type] $time [视频记录时间]
     * @param  [type] $thumb [缩率图路径]
     * @return [string]       [返回缩率图路径]
     */
    public function getVideoCover($path, $time = '', $thumb)
    {
        $ffmpeg = \FFMpeg\FFMpeg::create();
        if (empty($time)) {
            $time = '1';
        }
        //默认截取video第一秒
        $filepath = ROOT_PATH . 'public' . $path;
        $video    = $ffmpeg->open($filepath);
        $frame    = $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds($time));
        //生成缩略图文件名
        $strlen         = strlen($thumb);
        $videoCover     = substr($thumb, 0, $strlen - 4);
        $pathname       = '/uploads' . DS . 'video' . DS . date('Ymd') . DS . 'thumb_' . $videoCover . '.jpg'; //缩略图命名
        $return_path    = str_replace('\\', '/', $pathname);
        $videoCoverName = ROOT_PATH . 'public' . DS . $return_path;
        $data           = $frame->save($videoCoverName);
        return $return_path;
    }

}
