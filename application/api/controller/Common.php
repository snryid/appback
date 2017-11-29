<?php
namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Image;
use think\Request;
use think\Session;
use think\Validate;

header("Content-type: text/plain");
class Common extends Controller
{
    protected $request; //用来处理参数
    protected $validata; //用来验证数据/参数
    protected $params; //过滤后符合要求的参数
    protected $rules = array(
        'User'    => array(
            'login'           => array(
                'user_name' => 'require',
                'user_pwd'  => 'require|length:32',
                // 'session_id' => 'require|length:32', 防止其他地方登陆
            ),
            'register'        => array(
                'user_name' => 'require',
                'user_pwd'  => 'require|length:32',
                'code'      => 'require|number|length:6',
            ),
            'upload_head_img' => array(
                'id'        => 'require|number',
                'user_icon' => 'require|image|fileExt:jpg,png,jpeg,bmp|fileSize:2097152',
            ),
            'change_pwd'      => array(
                'user_name'    => 'require',
                'user_ini_pwd' => 'require|length:32',
                'user_pwd'     => 'require|length:32',
            ),
            'find_pwd'        => array(
                'user_name' => 'require',
                'user_pwd'  => 'require|length:32',
                'code'      => 'require|number|length:6',
            ),
            'bind_username'   => array(
                'id'        => 'require|number',
                'user_name' => 'require',
                'code'      => 'require|number|length:6',
            ),
            'set_nickname'    => array(
                'id'            => 'require|number',
                'user_nickname' => 'require|chsDash',
            ),
            'qq_login'        => array(
                'usid'          => 'require|length:32',
                'user_nickname' => 'require',
                'sex'           => 'require',
                'user_icon'     => 'require',
            ),
            'user_collect'    => array(
                'user_id' => 'require',
                'type'    => 'require|alpha',
                'ID'      => 'require|number',
            ),
            'user_deselect'    => array(
                'user_id' => 'require',
                'type'    => 'require|alpha',
                'ID'      => 'require|number',
            ),
            'check_collect'   => array(
                'user_id' => 'require',
                'type'    => 'require|alpha',
                'ID'      => 'require|number',
            ),
            'get_collect'     => array(
                'user_id' => 'require',
                'type'    => 'require|alpha',
            ),
            'get_publish'     => array(
                'user_id' => 'require|number',
            ),
        ),
        'Code'    => array(
            'get_code' => array(
                'username' => 'require',
                'is_exist' => 'require|number|length:1',
            ),
        ),
        'Video'   => array(
            'video_type' => array(),
            'sum_video'  => array(
                'type_id' => 'require|number',
            ),
            'port_video' => array(
                'type_id'    => 'require|number',
                'video_page' => 'require|number',
                'video_sum'  => 'require|number',
            ),
        ),
        'Image'   => array(
            'image_type' => array(),
            'image_sum'  => array(
                'type_id' => 'require|number',
            ),
            'get_image'  => array(
                'type_id'  => 'require|number',
                'img_page' => 'require|number',
                'img_sum'  => 'require|number',
            ),
        ),
        'News'    => array(
            'new_type'  => array(),
            'sum_news'  => array(),
            'query'     => array(
                'news_sum'  => 'require',
                'news_page' => 'require',
            ),
            'recommend' => array(
            ),
        ),
        'Friends' => array(
            'sum_tribune' => array(),
            'exchange'    => array(
                'forum_sum'  => 'require|number',
                'forum_page' => 'require|number',
            ),
            'sum_comment' => array(),
            'discuss'     => array(
                'tribune_id' => 'require|number',
                'forum_sum'  => 'require|number',
                'forum_page' => 'require|number',
            ),
            'add_comment' => array(
                'tribune_id' => 'require|number',
                'user_id'    => 'require|number',
                'content'    => 'require',
            ),
            'pub_article' => array(
                'user_id' => 'require|number',
                'title'   => 'require',
                'content' => 'require',
            ),
            'lick'        => array(
                'tribune_id'   => 'require|number',
                'user_id'      => 'require|number',
                'tribune_lick' => 'require|number|length:1',
            ),
            'del_content' => array(
                'user_id'  => 'require|number',
                'deleteID' => 'require|number',
                'type'     => 'require|alpha',
            ),
        ),
    );
    protected function _initialize()
    {
        parent::_initialize();
        $this->request = Request::instance();
        // $this->check_time($this->request->only(['time'])); //检测时间戳
        // $this->check_token($this->request->param()); //检测token
        $this->params = $this->check_params($this->request->param(true)); //检测参数
    }

    /**
     *验证请求是否超时
     * @param [array]  $arr [包含时间戳的参数组]
     * @return [json]  ['检测结果']
     */
    public function check_time($arr)
    {

        if (!isset($arr['time']) || intval($arr['time']) <= 1) {
            $this->return_msg(400, '时间戳不存在!');
        }
        if (time() - intval($arr['time']) > 60) {
            $this->return_msg(400, '请求超时!');
        }
    }

    /**
     *检查客服端类型
     * @return [string]  ['检测结果']
     */
    public function check_clients()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
            return 'systerm is IOS';
        } else if (strpos($_SERVER['HTTP_USER_AGENT'], 'Android')) {
            return 'systerm is Android';
        } else {
            return 'systerm is other';
        }
    }

    /**
     *api 数据返回
     * @param [int]  $code [结果码  200:正常/4**数据问题/5**服务器问题]
     * @param [string]  $msg [接口要返回的数据信息]
     * @param [array]  $data [接口要返回的的数据]
     * @return [string]  ['最终的json数据']
     */
    public function return_msg($code, $msg = '', $data = [])
    {
        /******* 组合数据   ********/
        $return_data['code'] = $code;
        $return_data['msg']  = $msg;
        $return_data['data'] = $data;

        /******* 返回信息并终止脚本   ********/
        echo json_encode($return_data);die;
    }

    /**
     *验证token (防止篡改数据)
     * @param [array]  $data [全部请求参数]
     * @return [json]  [token验证结果]
     */
    public function check_token($arr)
    {
        /********* api传过来的token  *********/
        if (!isset($arr['time']) || empty($arr['token'])) {
            $this->return_msg(400, 'token不能为空!');
        }
        $app_token = $arr['token']; //app传过来的token
        /********  服务器端生成token    ******/
        unset($arr['token']);
        $service_token = md5($arr['time']);
        $service_token = md5('beck_' . $service_token . '_beck'); //服务器即时生成的token,以申请时的时间戳MD5加密

        /********  对比token 返回结果    ******/
        if ($app_token !== $service_token) {
            $this->return_msg(400, 'token值不正确!');
        }

    }

    /**
     *验证参数  参数过滤
     * @param [array]  $arr [除time和token外的所有参数 ]
     * @return [return]  [合格的参数组]
     */

    public function check_params($arr)
    {
        /********  获取参数验证规则    ******/
        $rule = $this->rules[$this->request->controller()][$this->request->action()]; //0928修改
        /********  验证参数并返回错误    *******/
        $this->validata = new Validate($rule);
        if (!$this->validata->check($arr)) {
            $this->return_msg(400, $this->validata->getError());
        }
        /********  如果正常,通过验证    *******/
        return $arr;
    }

    /**
     *检测用户并返回用户类别
     * @param [string]  $username [用户名,可能是邮箱,也可能是手机 ]
     * @return [string]  [检测结果]
     *
     */

    public function check_username($username)
    {
        /******** 判断是否为邮箱 *********/
        $is_email = Validate::is($username, 'email') ? 1 : 0;
        /******** 判断是否为手机 *********/
        $is_phone = preg_match('/^1[345789]\d{9}$/', $username) ? 4 : 2;
        /******** 判断结果 *********/
        $flag = $is_email + $is_phone;
        switch ($flag) {
            /******** not phone not email ********/
            case 2:
                $this->return_msg(400, '邮箱或手机号不正确!');
                break;
            /******** not phone is email *********/
            case 3:
                return 'email';
                break;
            /******** is phone not email *********/
            case 4:
                return 'phone';
                break;

        }
    }

    /**
     *检测手机号码/邮箱是否存在
     * @param [string] $value [手机/邮箱]
     * @param [string] $type [类型]
     * @param [int] $exist [是否存在数据库]
     * @return [json]  [检测结果]
     */
    public function check_exist($value, $type, $exist)
    {
        $type_num  = $type == 'phone' ? 2 : 4;
        $flag      = $type_num + $exist;
        $phone_res = db('user')->where('user_mobile', $value)->find();
        $email_res = db('user')->where('user_email', $value)->find();
        switch ($flag) {
            /***********    2+0 phone need exist  *********/
            case 2:
                if ($phone_res) {
                    $this->return_msg(400, '此手机号已被占用!');
                }
                break;
            /***********    2+1 phone need exist  *********/
            case 3:
                if (!$phone_res) {
                    $this->return_msg(400, '此手机号不存在!');
                }
                break;
            /***********    4+0 email need exist  *********/
            case 4:
                if ($email_res) {
                    $this->return_msg(400, '此邮箱已被占用!');
                }
                break;
            /***********    4+1 email need exist  *********/
            case 5:
                if (!$email_res) {
                    $this->return_msg(400, '此邮箱不存在!');
                }
                break;
        }
    }

    /**
     *检测验证码
     * @param [string] $user_name     [用户名]
     * @param [int]     $code         [验证码]
     * @return [json]  [api返回json数据]
     */
    public function check_code($user_name, $code)
    {

        /**********   检测生成验证码时间是否为空   **********/
        if (!session('code_time')) {
            $this->return_msg(400, '请获取验证码!', 'NULL');
        }
        /**********   检测是否超时   **********/
        $last_time = session('code_time');
        $time      = time();
        if ($time - $last_time > 60) {
            $this->return_msg(400, '验证超时，请在一分钟内验证!');
        }
        session('code_time', null);
        /**********   检测验证码是否正确   **********/
        $md5_code = md5($user_name . '_' . md5($code));
        $md5_code = $user_name . '_code' . $md5_code;
        if (session('user_code') !== $md5_code) {
            $this->return_msg(400, '验证码不正确!');
        }

        /**********   不管正确与否，每个验证码只验证一次   **********/
        session('user_code', null);

    }

    /**
     *用户上传头像文件
     * @param  [string]     $file     [上传文件]
     * @param  [string]     $type     [文件类型]
     * @return [string]                [返回图片路劲]
     */
    public function upload_file($file, $type = '')
    {

        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'usericon');
        if ($info) {

            $path = '/uploads' . DS . 'usericon/' . $info->getSaveName();

            /************* 裁剪图片 *************/
            if (!empty($type)) {
                $this->image_edit($path, $type);
            }
            return str_replace('\\', '/', $path); //替换反斜线
        } else {
            $this->return_msg(400, $file->getError());
        }
    }
    /**
     *裁剪图片
     */
    public function image_edit($path, $type)
    {
        $image = Image::open(ROOT_PATH . 'public' . $path);
        switch ($type) {
            case 'head_img':
                $image->thumb(200, 200, Image::THUMB_CENTER)->save(ROOT_PATH . 'public' . $path);
                break;

        }
    }

    /**
     * 朋友圈上传图片
     * @param  [string] $files [上传文件]
     * @param  [string] $type  [文件类型]
     * @return [string]        [返回图片路劲]
     */
    public function upload_img($files, $type = '')
    {
        if (is_array($files)) {
            $image_path = array();
            foreach ($files as $file) {
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'friends');
                if ($info) {
                    $path  = '/uploads' . DS . 'friends/' . $info->getSaveName();
                    $image = str_replace('\\', '/', $path); //替换反斜线

                    /***** 生成缩略图   *****/
                    $thumb_path = '/uploads' . DS . 'friends' . DS . date("Ymd", time()) . DS . 'thumb_' . $info->getFilename(); //重组文件名，防止覆盖
                    $thumb_path = str_replace('\\', '/', $thumb_path);
                    if (!empty($type)) {
                        $thumb = $this->image_thumb($path, $type, $thumb_path);
                    }
                    $image_path[] = array('image_url' => $image, 'thumb_url' => $thumb);
                } else {
                    return false;
                }
            }
        } else {
            $info = $files->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'friends');
            if ($info) {
                $path                    = '/uploads' . DS . 'friends' . DS . $info->getSaveName();
                $image_path['image_url'] = str_replace('\\', '/', $path); //替换反斜线

                /***** 生成缩略图   *****/
                $thumb_path = '/uploads' . DS . 'friends' . DS . date("Ymd", time()) . DS . 'thumb_' . $info->getFilename(); //重组文件名，防止覆盖
                $thumb_path = str_replace('\\', '/', $thumb_path);
                if (!empty($type)) {
                    $image_path['thumb_url'] = $this->image_thumb($path, $type, $thumb_path);
                }
            }
        }
        return $image_path;
    }

    /**
     *生成缩略图
     */
    public function image_thumb($path, $type, $thumb_path)
    {
        $image = Image::open(ROOT_PATH . 'public' . $path);
        switch ($type) {
            case 'image':
                $image->thumb(200, 200, Image::THUMB_SCALING)->save(ROOT_PATH . 'public' . $thumb_path);
                break;
        }
        return $thumb_path;
    }

}
