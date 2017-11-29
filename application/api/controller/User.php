<?php
namespace app\api\controller;

class User extends Common
{
    /**
     * 用户登录
     */
    public function login()
    {

        /********  接受参数    ******/
        $data = $this->params;
        /********   判断session_id是否正确    ******/

        //     $user = db('user')->where(array('user_name'=>$data['user_name']))->find();
        // if($user['session_id'] != $data['session_id']){
        //     session('session_id', null);
        //     $this->return_msg(400 , '您的账号在其他地方登录,您已经被强制下线');
        // }
        /********  判断登录方式并返回数据    ******/
        $user_name_type = $this->check_username($data['user_name']);
        switch ($user_name_type) {
            case 'phone':
                $this->check_exist($data['user_name'], 'phone', 1);
                $db_res = db('user')
                    ->field('*')
                    ->where('user_mobile', $data['user_name'])
                    ->find();
                break;
            case 'email':
                $this->check_exist($data['user_name'], 'email', 1);
                $db_res = db('user')
                    ->field('*')
                    ->where('user_email', $data['user_name'])
                    ->find();
                break;
        }
        if ($db_res['user_pwd'] !== $data['user_pwd']) {
            $this->return_msg(400, '用户密码不一致！', $data);
        } else {
            $data['last_login'] = time();
            $data['last_ip']    = $_SERVER['REMOTE_ADDR'];
            db('user')->where('user_name', $data['user_name'])->update($data); //更新数据库
            unset($db_res['user_pwd']); //密码永不返回
            $this->return_msg(200, '登陆成功！', $db_res);
        }

    }

    /**
     *用户注册
     */
    public function register()
    {
        /****** 接受参数  ******/
        $data = $this->params;
        /****** 检查验证码  ******/
        $this->check_code($data['user_name'], $data['code']);
        die;
        /****** 检查用户名  ******/
        $user_name_type = $this->check_username($data['user_name']);
        switch ($user_name_type) {
            case 'phone':
                $this->check_exist($data['user_name'], 'phone', 0);
                $data['user_mobile'] = $data['user_name'];
                break;
            case 'email':
                $this->check_exist($data['user_name'], 'email', 0);
                $data['user_email'] = $data['user_name'];
                break;
        }
        /****** 将用户信息写入数据库  ******/
        unset($data['user_name']); //因为数据库有user_name字段 今后还有昵称等  所以先清除该字段 防止手机和邮箱写入数据库的user_name字段中
        $data['add_time']  = time(); //注册时间
        $data['last_ip']   = $_SERVER['REMOTE_ADDR']; //获取用户注册IP
        $data['user_name'] = 'Jinri' . $this->user_name(); //生成用户名
        $res               = db('user')->insertGetId($data); //插入数据库返回id
        if (!$res) {
            $this->return_msg(400, '用户注册失败！', $data);
        } else {
            $this->return_msg(200, '用户注册成功！', $res);
        }
    }

    /**
     *用户上传头像
     */
    public function upload_head_img()
    {
        /****** 接受参数  ******/
        $data = $this->params;
        /****** 上传文件，获得路径  ******/
        $head_img_path = $this->upload_file($data['user_icon'], 'head_img');
        /****** 存入数据库  ******/
        $res = db('user')->where('id', $data['id'])->setField('user_icon', $head_img_path);
        if ($res) {
            $this->return_msg(200, '头像上传成功!', $head_img_path);
        } else {
            $this->return_mag(400, '头像上传失败!', $data);
        }
    }

    /**
     *用户修改密码
     */
    public function change_pwd()
    {
        /****** 接受参数  ******/
        $data = $this->params;
        /****** 检查用户名并取出数据库中的密码  ******/
        $user_name_type = $this->check_username($data['user_name']);
        switch ($user_name_type) {
            case 'phone':
                $this->check_exist($data['user_name'], 'phone', 1);
                $where['user_mobile'] = $data['user_name'];
                break;
            case 'email':
                $this->check_exist($data['user_name'], 'email', 1);
                $where['user_email'] = $data['user_name'];
                break;
        }
        /******  判断原始密码是否正确  ******/
        $db_ini_pwd = db('user')->where($where)->value('user_pwd');
        if ($db_ini_pwd !== $data['user_ini_pwd']) {
            $this->return_msg(400, '原密码错误!');
        }
        /****** 把新的密码存入数据库  ******/
        $res = db('user')->where($where)->setField('user_pwd', $data['user_pwd']);
        if ($res !== false) {
            $this->return_msg(200, '密码修改成功!', 'NULL');
        } else {
            $this->return_msg(400, '密码修改失败!', $data);
        }

    }

    /**
     * 用户找回密码
     *
     */
    public function find_pwd()
    {
        /****** 接受参数  ******/
        $data = $this->params;
        /****** 检测验证码  ******/
        $this->check_code($data['user_name'], $data['code']);
        /****** 检测用户名  ******/
        $user_name_type = $this->check_username($data['user_name']);
        switch ($user_name_type) {
            case 'phone':
                $this->check_exist($data['user_name'], 'phone', 1);
                $where['user_mobile'] = $data['user_name'];
                break;
            case 'email':
                $this->check_exist($data['user_name'], 'email', 1);
                $where['user_email'] = $data['user_name'];
                break;
        }
        /****** 修改数据库  ******/
        $res = db('user')->where($where)->setField('user_pwd', $data['user_pwd']);
        if ($res !== false) {
            $this->return_msg(200, '密码重置成功!', 'NULL');
        } else {
            $this->return_msg(400, '密码重置失败!', $data);
        }
    }

    /**
     *用户绑定邮箱/手机号
     *
     */
    public function bind_username()
    {

        /******** 接受参数  ********/
        $data = $this->params;
        /******** 检测验证码  ********/
        $this->check_code($data['user_name'], $data['code']);
        /******** 判断用户名  ********/
        $user_name_type = $this->check_username($data['user_name']);
        switch ($user_name_type) {
            case 'phone':
                $type_text                  = '手机号';
                $updata_data['user_mobile'] = $data['user_name'];
                break;
            case 'email':
                $type_text                 = '邮箱';
                $updata_data['user_email'] = $data['user_name'];
                break;
        }
        /******** 更新数据库  ********/
        $res = db('user')->where('id', $data['id'])->update($updata_data);
        if ($res !== false) {
            $this->return_msg(200, $type_text . '绑定成功!', 'NULL');
        } else {
            $this->return_msg(400, $type_text . '绑定失败!', $data);
        }
    }

    /**
     *用户修改昵称
     */
    public function set_nickname()
    {
        /******** 接受参数  ********/
        $data = $this->params;
        /******** 检测昵称  ********/
        $res = db('user')->where('user_nickname', $data['user_nickname'])->find();
        if ($res) {
            $this->return_msg(400, '改昵称已被占用!');
        }
        /******** 修改数据库并返回  ********/
        $res = db('user')->where('id', $data['id'])->setField('user_nickname', $data['user_nickname']);
        if ($res) {
            $this->return_msg(200, '修改昵称成功!', 'NULL');
        } else {
            $this->return_msg(400, '修改昵称失败!', $data);
        }
    }

    /**
     *第三方登陸
     */
    public function qq_login()
    {
        /******** 接受参数  ********/
        $data = $this->params;
        /******** 检测参数并更新数据库  ********/
        $data['qq_usid'] = $data['usid'];
        $data['last_ip'] = $_SERVER['REMOTE_ADDR'];
        #转换性别
        if ($data['sex'] = '男') {
            $data['sex'] = '1';
        } elseif ($data['sex'] = '女') {
            $data['sex'] = '2';
        } else {
            $data['sex'] = '0';
        }
        /****    查询数据库书否存在     ******/
        $res = db('user')->where('qq_usid', $data['usid'])->find();
        if (!empty($res)) {
            # 向数据库更新
            $data['last_login'] = time();
            $data['user_name']  = $res['user_name'];
            $result             = db('user')->where('qq_usid', $data['usid'])->update($data);
            $returndata         = array('user_id' => $res['id'], 'info' => $data);
        } else {
            # 向数据库插入
            $data['add_time']  = time();
            $data['user_name'] = 'Jinriqq' . $this->user_name();
            $result            = db('user')->insertGetId($data);
            $returndata        = array('user_id' => $result, 'info' => $data);
        }

        /******** 返回结果  ********/
        if ($result) {
            $this->return_msg(200, 'qq登陆成功！', $returndata);
        } else {
            $this->return_msg(400, 'qq登陆失败！', 'NULL');
        }

    }

    /*
     *生成用户名随机数
     */
    public function user_name($sum = '')
    {
        $count = db('user')->order('id DESC')->find();
        $sum   = $count['id'] + 1;
        return $sum;
    }

    /**
     * 检测用户是否收藏
     * @return [int] [返回状态]
     */
    public function check_collect($return = '')
    {
        /******** 接受参数  ********/
        $data = $this->params;
        /******** 检测参数并更新数据库  ********/
        $res = db('user')->where('id', $data['user_id'])->find();
        if ($res) {
            switch ($data['type']) {
                case 'image':
                    $check = stristr($res['img_collect'], $data['ID']);
                    if ($check) {
                        $return = 1;
                    }
                    break;
                case 'video':
                    $check = stristr($res['video_collect'], $data['ID']);
                    if ($check) {
                        $return = 1;
                    }
                    break;
                case 'article':
                    $check = stristr($res['article_collect'], $data['ID']);
                    if ($check) {
                        $return = 1;
                    }
                    break;
                default:
                    $return = 0;
                    break;
            }
        } else {
            $this->return_msg(400, '该用户不存在或者上传参数不合法！', $data);
        }
        if (empty($return)) {
            $this->return_msg(200, '检测记录为空！', $data);
        } else {
            $this->return_msg(200, '检测成功！', $return);
        }
    }

    /**
     * 用户收藏
     * @return [int] [返回状态]
     */
    public function user_collect($save = '')
    {
        /******** 接受参数  ********/
        $data = $this->params;
        /******** 检测参数并更新数据库  ********/
        $res = db('user')->where('id', $data['user_id'])->find();
        if ($res) {
            switch ($data['type']) {
                case 'image':
                    if (empty($res['img_collect'])) {
                        $collect_id = $data['ID'];
                    } else if (stristr($res['img_collect'], $data['ID']) !== false) {
                        $collect_id = $res['img_collect'];
                    } else {
                        $collect_id = $res['img_collect'] . ',' . $data['ID'];
                    }
                    $save = db('user')->where(array('id' => $data['user_id']))->update(['img_collect' => $collect_id]);
                    break;
                case 'video':
                    if (empty($res['video_collect'])) {
                        $collect_id = $data['ID'];
                    } else if (stristr($res['video_collect'], $data['ID']) !== false) {
                        $collect_id = $res['video_collect'];
                    } else {
                        $collect_id = $res['video_collect'] . ',' . $data['ID'];
                    }
                    $save = db('user')->where(array('id' => $data['user_id']))->update(['video_collect' => $collect_id]);
                    break;
                case 'article':
                    if (empty($res['article_collect'])) {
                        $collect_id = $data['ID'];
                    } else if (stristr($res['article_collect'], $data['ID']) !== false) {
                        $collect_id = $res['article_collect'];
                    } else {
                        $collect_id = $res['article_collect'] . ',' . $data['ID'];
                    }
                    $save = db('user')->where(array('id' => $data['user_id']))->update(['article_collect' => $collect_id]);
                    break;

                default:
                    $save = '';
                    break;
            }
        } else {
            $this->return_msg(400, '该用户不存在或者上传参数不合法！', $data);
        }
        /******** 更新成功并返回参数  ********/
        if (empty($save)) {
            $this->return_msg(400, '添加失败或者已收藏！', array('data' => $data, 'info' => $res));
        } else {
            $this->return_msg(200, '添加收藏成功！', $save);
        }
    }

    /**
     * 用户取消收藏
     * @return [int] [返回状态]
     */
    public function user_deselect($save = '')
    {
        /******** 接受参数  ********/
        $data = $this->params;
        /******** 检测参数并更新数据库  ********/
        $res = db('user')->where('id', $data['user_id'])->find();
        if ($res) {
            switch ($data['type']) {
                case 'image':
                    if (empty($res['img_collect'])) {
                        $collect_id = '';
                    } else if (stristr($res['img_collect'], $data['ID']) !== false) {
                        $collect_id = $this->replace_str($res['img_collect'], $data['ID']);
                    } else {
                        $collect_id = $res['img_collect'];
                    }
                    $save = db('user')->where(array('id' => $data['user_id']))->update(['img_collect' => $collect_id]);
                    break;
                case 'video':
                    if (empty($res['video_collect'])) {
                        $collect_id = '';
                    } else if (stristr($res['video_collect'], $data['ID']) !== false) {
                        $collect_id = $this->replace_str($res['video_collect'], $data['ID']);
                    } else {
                        $collect_id = $res['video_collect'];
                    }
                    $save = db('user')->where(array('id' => $data['user_id']))->update(['video_collect' => $collect_id]);
                    break;
                case 'article':
                    if (empty($res['article_collect'])) {
                        $collect_id = '';
                    } else if (stristr($res['article_collect'], $data['ID']) !== false) {
                        $collect_id = $this->replace_str($res['article_collect'], $data['ID']);
                    } else {
                        $collect_id = $res['article_collect'];
                    }
                    $save = db('user')->where(array('id' => $data['user_id']))->update(['article_collect' => $collect_id]);
                    break;

                default:
                    $save = '';
                    break;
            }
        } else {
            $this->return_msg(400, '该用户不存在或者上传参数不合法！', $data);
        } 
        /******** 更新成功并返回参数  ********/
        if (empty($save)) {
            $this->return_msg(400, '取消失败或者已取消收藏！', array('data' => $data, 'info' => $res));
        } else {
            $this->return_msg(200, '取消收藏成功！', $save);
        }
    }

    /**
     * 用户个人中心获取收藏
     * @return [array] [返回数据]
     */
    public function get_collect($return = '')
    {
        /******** 接受参数  ********/
        $data = $this->params;
        /******** 检测参数并更新数据库  ********/
        $res = db('user')->where('id', $data['user_id'])->find();
        if ($res) {
            switch ($data['type']) {
                case 'image':
                    $get_id = explode(',', $res['img_collect']);
                    $return = array();
                    foreach ($get_id as $id) {
                        $return[] = db('image')->alias('i')->field('i.*,t.type_name')->where('i.id', $id)->order('id desc')->join('image_type t', 'i.type_id=t.type_id', 'left')->select();
                    }
                    break;
                case 'video':
                    $get_id = explode(',', $res['video_collect']);
                    $return = array();
                    foreach ($get_id as $id) {
                        $return[] = db('video')->alias('v')->field('v.*,t.class_video')->where('v.id', $id)->order('id desc')->join('video_type t', 'v.vid=t.type_id', 'left')->select();
                    }
                    break;
                case 'article':
                    $get_id = explode(',', $res['article_collect']);
                    $return = array();
                    foreach ($get_id as $id) {
                        $return[] = db('article')->alias('a')->field('a.*,c.catename')->where('a.id', $id)->order('id desc')->join('cate c', 'a.cateid=c.id', 'left')->select();
                    }
                    break;
                default:
                    $return = null;
                    break;
            }

        } else {
            $this->return_msg(400, '该用户不存在或者上传参数不合法！', $data);
        }
        if (empty($return)) {
            $this->return_msg(200, '收藏记录为空！', $data);
        } else {
            /*****   转换三维数组为二维   ****/
            foreach ($return as $key => $value) {
                $new_array[] = $value[0];
            }
            $this->return_msg(200, '获取收藏数据成功！', $new_array);
        }
    }

    /**
     * 用户个人中心获取发表的帖子
     * @return [type] [description]
     */
    public function get_publish($dataArray = '')
    {
        /******** 接受参数  ********/
        $data = $this->params;
        /******  向数据库匹配交流贴并返回  ******/
        $where['user_id'] = $data['user_id']; //查询用户ID下的帖子
        $db_res           = db('tribune')
            ->field('*')
            ->where($where)
            ->order('tribune_id desc')
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
            $this->return_msg(400, '获取失败！', $data);
        } else {
            $this->return_msg(200, '获取成功！', $dataArray);
        }
    }

    /**
     * 替换字符串
     * @return [string] [返回字符串]
     */
    public function replace_str($get_str = '' , $id = ''){
        $str_arr = explode("," , $get_str);
        foreach($str_arr as $k => $v){  
               if($v == $id){  
                   unset($str_arr[$k]);  
               }  
           }  
        $new_arr = implode(",",$str_arr);
        return $new_arr;
    }

}
