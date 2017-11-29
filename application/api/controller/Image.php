<?php
namespace app\api\controller;

class Image extends Common
{

    /**
     *相册接口
     */
    public function image_type()
    {
        /****** 接受参数  ******/
        $data = $this->params;
        /******  向数据库查找视频类型并返回  ******/
        $result = db('image_type')->select();
        /****** 将获取信息返回出去  ******/
        if (!$result) {
            $this->return_msg(400, '获取相册类型失败！', $data);
        } else {
            $this->return_msg(200, '获取相册类型成功！', $result);
        }
    }

    /**
     *获取相册总数接口
     */
    public function image_sum()
    {
        /****** 接受参数  ******/
        $data = $this->params;
        /******  获取数据库的总条数  ******/
        $count = db('image')->where('type_id', $data['type_id'])->count();
        /****** 将获取信息返回出去  ******/
        if (!$count) {
            $this->return_msg(400, '获取类型相册总数失败！', 'NULL');
        } else {
            $this->return_msg(200, '获取类型相册总数成功！', $count);
        }
    }

    /**
     *相册接口
     */
    public function get_image()
    {
        /****** 接受参数  ******/
        $data = $this->params;
        /******  向数据库匹配视频类型并返回  ******/
        $where  = array('type_id' => $data['type_id'], 'res_status' => '1');
        $db_res = db('image')
            ->field('id,title,thumb,image_url,other_url,image_content,add_time,res_status')
            ->where($where)
            ->order('id desc')
            ->limit($data['img_sum'])
            ->page($data['img_page'])
            ->select();

        /****** 将获取信息返回出去  ******/
        if (!$db_res) {
            $this->return_msg(400, '获取图片失败！', $data);
        } else {
            $this->return_msg(200, '获取图片成功！', $db_res);
        }
    }

}
