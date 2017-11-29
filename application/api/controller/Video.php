<?php
namespace app\api\controller;

use think\Db;

class Video extends Common
{

    /**
     * 视频类型接口
     */
    public function video_type()
    {
        /****** 接受参数  ******/
        $data = $this->params;
        /******  向数据库查找视频类型并返回  ******/
        $result = db('video_type')->select();
        /****** 将获取信息返回出去  ******/
        if (!$result) {
            $this->return_msg(400, '获取视频类型失败！', $data);
        } else {
            $this->return_msg(200, '获取视频类型成功！', $result);
        }

    }

    /**
     *获取视频总数接口
     */
    public function sum_video()
    {
        /****** 接受参数  ******/
        $data = $this->params;
        /******  获取数据库的总条数  ******/
        $count = db('video')->where('vid', $data['type_id'])->count();
        /****** 将获取信息返回出去  ******/
        if (!$count) {
            $this->return_msg(400, '获取类型视频总数失败！', $data);
        } else {
            $this->return_msg(200, '获取类型视频总数成功！', $count);
        }
    }
    /**
     *视频接口
     */
    public function port_video()
    {
        /****** 接受参数  ******/
        $data = $this->params;
        /******  向数据库匹配视频类型并返回  ******/
        $db_res = db('video')
            ->field('*')
            ->where('vid', $data['type_id'])
            ->order('id desc')
            ->limit($data['video_sum'])
            ->page($data['video_page'])
            ->select();

        /****** 将获取信息返回出去  ******/
        if (!$db_res) {
            $this->return_msg(400, '获取视频失败！', $data);
        } else {
            $this->return_msg(200, '获取视频成功！', $db_res);
        }
    }

}
