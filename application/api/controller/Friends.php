<?php
namespace app\api\controller;

use app\api\controller\Common;
use think\Controller;

class Friends extends Common
{

    /**
     * 发布朋友圈
     * @return [string] [NULL]
     */
    public function pub_article($return = '')
    {

        /****** 接受参数  ******/
        $data = $this->params;
        /******  向数据库查找是否存在文章型并返回  ******/
        $res = db('user')->where('id', $data['user_id'])->find();
        /******  处理数据并返回 ******/
        if (!empty($res)) {
            $data['user_name']       = $res['user_nickname'];
            $data['user_img']        = $res['user_icon'];
            $data['user_ip']         = $_SERVER['REMOTE_ADDR'];
            $data['add_time']        = date('Y-m-d H:i:s', time());
            $data['tribune_status']  = "1";
            $data['tribune_title']   = $data['title'];
            $data['tribune_content'] = $data['content'];
            $returnID                = db('tribune')->insertGetId($data);
        } else {
            $this->return_msg(400, '用户ID或者会员名不存在！', $data);
        }
        /****** 处理上传图片，获得路径  ******/
        if (!empty($data['image']) && $returnID) {
            $img_path = $this->upload_img($data['image'], 'image');
            $result   = array();
            foreach ($img_path as $value) {
                $imgdata['add_time']   = date('Y-m-d H:i:s', time());
                $imgdata['tribune_id'] = $returnID;
                $imgdata['image_url']  = $value['image_url'];
                $imgdata['thumb_url']  = $value['thumb_url'];
                $result[]              = $imgdata;
            }
            $return = db('tribune_img')->insertAll($result);
        } else {
            $return = false;
        }

        /****** 将获取信息返回出去  ******/
        if (!$return) {
            $this->return_msg(400, '发表失败！', $data);
        } else {
            $this->return_msg(200, '发表成功！', 'NULL');
        }

    }

    /**
     *获取交流贴总数接口
     */
    public function sum_tribune($count = '')
    {
        /****** 接受参数  ******/
        $data = $this->params;
        /******  获取数据库的总条数  ******/
        $count = db('tribune')->where('tribune_status', 1)->count();
        /****** 将获取信息返回出去  ******/
        if (!$count) {
            $this->return_msg(400, '获取交流贴总数失败！', $data);
        } else {
            $this->return_msg(200, '获取交流贴总数成功！', $count);
        }
    }

    /**
     * 获取交流贴
     * @return [type] [description]
     */
    public function exchange($dataArray = '')
    {
        /****** 接受参数  ******/
        $data = $this->params;

        /******  向数据库匹配交流贴并返回  ******/
        $where['tribune_status'] = '1'; //查询内容为公开状态
        $db_res                  = db('tribune')
            ->field('*')
            ->where($where)
            ->order('tribune_id desc')
            ->limit($data['forum_sum'])
            ->page($data['forum_page'])
            ->select();
        /*****  获取图片信息  *****/
        if ($db_res) {
            $dataArray = array();
            foreach ($db_res as $value) {
                $path_img    = db('tribune_img')->field('id,image_url,thumb_url')->where('tribune_id', $value['tribune_id'])->select();
                $dataArray[] = array('img_path' => $path_img, 'info' => $value);
            }
        }

        /****** 将获取信息返回出去  ******/
        if (!$dataArray) {
            $this->return_msg(400, '获取交友贴失败！', $data);
        } else {
            $this->return_msg(200, '获取交友贴成功！', $dataArray);
        }
    }

    /**
     *获取交流贴评论总数接口
     */
    public function sum_comment($count = '')
    {
        /****** 接受参数  ******/
        $data = $this->params;
        /******  获取数据库的总条数  ******/
        $count = db('tribune_comment')->where('comment_status', 1)->count();
        /****** 将获取信息返回出去  ******/
        if (!$count) {
            $this->return_msg(400, '获取评论总数失败！', $data);
        } else {
            $this->return_msg(200, '获取评论总数成功！', $count);
        }
    }

    /**
     * 获取评论内容
     * @return [array] [评论info]
     */
    public function discuss($db_res = '')
    {
        /****** 接受参数  ******/
        $data = $this->params;
        /******  向数据库匹配交流贴评论内容并返回  ******/
        $where['comment_status'] = '1'; //查询内容为公开状态
        $where['tribune_id']     = $data['tribune_id']; //帖子id
        $db_res                  = db('tribune_comment')
            ->field('*')
            ->where($where)
            ->order('comment_id desc')
            ->limit($data['forum_sum'])
            ->page($data['forum_page'])
            ->select();
        /*****  获取图片信息  *****/
        if ($db_res) {
            $dataArray = array();
            foreach ($db_res as $value) {
                $path_img    = db('comment_img')->field('id,image_url,thumb_url')->where('comment_id', $value['comment_id'])->select();
                $dataArray[] = array('img_path' => $path_img, 'info' => $value);
            }
        }
        /****** 将获取信息返回出去  ******/
        if (!$db_res) {
            $this->return_msg(400, '获取评论失败！', $data);
        } else {
            $this->return_msg(200, '获取评论成功！', $dataArray);
        }
    }

    /**
     * 用户评论交流贴
     */
    public function add_comment($returnID = '')
    {
        /****** 接受参数  ******/
        $data = $this->params;
        /******  向数据库查找是否存在文章型并返回  ******/
        $res = db('tribune')->where('tribune_id', $data['tribune_id'])->column('tribune_id , comm_sum');
        /******  处理数据 ******/
        if ($res) {
            $user_icon               = db('user')->where('id', $data['user_id'])->find();
            $data['user_ip']         = $_SERVER['REMOTE_ADDR'];
            $data['user_headimg']    = $user_icon['user_icon'];
            $data['user_name']       = $user_icon['user_name'];
            $data['add_time']        = date('Y-m-d H:i:s', time());
            $data['comment_status']  = "1";
            $data['comment_content'] = $data['content'];

        } else {
            $this->return_msg(400, '文章ID不对！', $data);
        }
        /****** 插入数据库或更新数据库   ********/
        $returnID = db('tribune_comment')->insertGetId($data);
        if ($returnID) {
            $sum['comm_sum'] = $res[$data['tribune_id']] + 1;
            db('tribune')->where('tribune_id', $data['tribune_id'])->update($sum);
        }
        /****** 处理上传图片，获得路径  ******/
        if (!empty($data['image']) && $returnID) {
            $img_path = $this->upload_img($data['image'], 'image');
            $result   = array();
            foreach ($img_path as $value) {
                $imgdata['add_time']   = $data['add_time'];
                $imgdata['comment_id'] = $returnID;
                $imgdata['image_url']  = $value['image_url'];
                $imgdata['thumb_url']  = $value['thumb_url'];
                $result[]              = $imgdata;
            }
            $return = db('comment_img')->insertAll($result);
        } else {
            $return = false;
        }
        /****** 将获取信息返回出去  ******/
        if (!$returnID) {
            $this->return_msg(400, '评论失败！', $data);
        } else {
            $this->return_msg(200, '评论成功！', $returnID);
        }

    }

    /**
     * 点赞
     * @return [array] [改变数值]
     */
    public function lick($return = '')
    {

        /****** 接受参数  ******/
        $data = $this->params;
        /******  向数据库查找是否存在文章型并返回  ******/
        $res = db('tribune')->where('tribune_id', $data['tribune_id'])->column('tribune_id , tribune_lick');
        /******  处理数据并返回 ******/
        if ($res) {
            switch ($data['tribune_lick']) {
                case '1':
                    $lick['tribune_lick'] = $res[$data['tribune_id']] + 1;
                    $return               = db('tribune')->where('tribune_id', $data['tribune_id'])->update($lick);
                    break;
                case '0':
                    $lick['tribune_lick'] = $res[$data['tribune_id']] - 1;
                    $return               = db('tribune')->where('tribune_id', $data['tribune_id'])->update($lick);
                    break;
                default:
                    $return = null;
                    break;
            }

        } else {
            $this->return_msg(400, '文章ID不存在！', $data);
        }
        /****** 将获取信息返回出去  ******/
        if (!$return) {
            $this->return_msg(400, '点赞不是有效数值！', 'NULL');
        } else {
            $this->return_msg(200, '更新成功！', $lick);
        }

    }

    /**
     * 删除发布和评论内容
     * @return [json] [返回结果]
     */
    public function del_content($return = '')
    {
        /****** 接受参数  ******/
        $data = $this->params;
        /****** 检测用户是否存在  ********/
        $user_id = db('user')->where('id', $data['user_id'])->find();

        if ($user_id) {
            $updata['user_ip'] = $_SERVER['REMOTE_ADDR'];
            switch ($data['type']) {
                case 'publish':
                    $updata['tribune_status'] = '0';
                    $return                   = db('tribune')->where('tribune_id', $data['deleteID'])->update($updata);
                    break;
                case 'comment':
                    $updata['comment_status'] = '0';
                    $return                   = db('tribune_comment')->where('comment_id', $data['deleteID'])->update($updata);
                    break;
                default:
                    $return = null;
                    break;
            }
        } else {
            $this->return_msg(400, '该用户不存在！', 'NULL');
        }

        /****** 将获取信息返回出去  ******/
        if (!$return) {
            $this->return_msg(400, '删除失败，请检查参数是否合法！', 'NULL');
        } else {
            $this->return_msg(200, '删除成功！', 'NULL');
        }
    }

}
