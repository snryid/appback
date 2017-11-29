<?php
namespace app\api\controller;

use app\api\common\SmsDemo;
use phpmailer\phpmailer;
use think\Session;

class Code extends Common
{

    public function get_code()
    {

        $username      = $this->params['username'];
        $exist         = $this->params['is_exist'];
        $username_type = $this->check_username($username); //检测用户名的函数
        switch ($username_type) {
            case 'phone':
                $this->get_code_by_username($username, 'phone', $exist);
                break;
            case 'email':
                $this->get_code_by_username($username, 'email', $exist);
                break;
        }

    }

    /**
     *通过手机号码/邮箱获取验证码
     * @param [string] $username [手机号/邮箱]
     * @param [int]   $exist [手机号码/邮箱是否存在于数据库中  1:是  0:否]
     * @return [json]  [api返回的json数据]
     */

    public function get_code_by_username($username, $type, $exist)
    {
        if ($type == 'username') {
            $type_name = '手机';
        } else {
            $type_name = '邮箱';
        }

        /**********  检测手机号码/邮箱是否存在 ***********/
        $this->check_exist($username, $type, $exist);
        /**********  检测验证码请求频率  60秒一次 ***********/
        if (!session('code_time')) {
            if (time() - session('code_time') < 60) {
                $this->return_msg(400, $type_name . '验证码,每60秒钟发送一次！', 'NULL');
            }
        }

        /**********  生成验证码   **********/
        $code = $this->make_code(6);
        /**********  使用session存储验证码，方便比对，MD5加密   **********/

        $md5_code = md5($username . '_' . md5($code));
        Session::set('user_code', $username . '_code' . $md5_code);

        /**********  使用session存储验证码的发送时间  *********/
        Session::set('code_time', time());

        /**********  发送验证码  **********/
        if ($type == 'phone') {
            $this->send_code_to_phone($username, $code);
        } else {
            $this->send_code_to_email($username, $code);
        }

    }

    /**
     *生成验证码
     * @param [int] $num [验证码的位数]
     * @return [int]  [生成的验证码]
     */
    public function make_code($num)
    {
        $max = pow(10, $num) - 1;
        $min = pow(10, $num - 1);
        return rand($min, $max);
    }

    /**
     *向手机发送验证码
     *@param [string]  $email [目标手机号码]
     *@param [int]  $code [验证码]
     *@return [json] [返回json信息]
     */
    public function send_code_to_phone($phone, $code)
    {

        $response = SmsDemo::sendSms(
            "今日电器", // 短信签名
            "SMS_109100025", // 短信模板编号
            $phone, // 短信接收者
            array( // 短信模板中字段的值
                "code" => $code,
            )
        );
        if ($response->Code !== 'OK') {
            $this->return_msg(400, $response->Message);
        } else {
            $this->return_msg(200, '手机验证码已发送,每天发送5次,请在一分钟内验证!', 'NULL');
        }
    }

    /**
     *向邮箱发送验证码
     *@param [string]  $email [目标邮箱]
     *@param [int]  $code [验证码]
     *@return [json] [返回json信息]
     */
    public function send_code_to_email($email, $code)
    {
        $where['cf_type'] = '4';
        $email_db         = db('conf')->where($where)->select();
        $coding           = $email_db[0]['value'] == 'SMTP服务' ? 'SMTP' : 'Mail';
        $email_ssl        = $email_db[1]['value'] == '是' ? 'ssl' : '';
        $email_site       = $email_db[2]['value'];
        $email_port       = $email_db[3]['value'];
        $email_name       = $email_db[4]['value'];
        $email_password   = $email_db[5]['value'];
        $email_revert     = $email_db[6]['value'];
        $email_code       = $email_db[7]['value'] == 'UTF8' ? 'utf8' : '';
        $toemail          = $email;
        $mail             = new PHPMailer();
        $mail->isSMTP();
        $mail->CharSet    = $email_code;
        $mail->Host       = $email_site;
        $mail->SMTPAuth   = true;
        $mail->Username   = $email_name;
        $mail->Password   = $email_password;
        $mail->SMTPSecure = $email_ssl;
        $mail->Port       = $email_port;
        $mail->setFrom($email_name, 'APP接口测试');
        $mail->addAddress($toemail, 'test');
        $mail->addReplyTo($email_revert, 'Reply');
        $mail->Subject = "您有新的验证码！";
        $mail->Body    = "这是一个测试邮件,你的验证码是$code,验证码有效期为1分钟,本邮件请勿回复！";
        if (!$mail->send()) {
            $this->return_msg(400, $mail->ErrorInfo);
        } else {
            $this->return_msg(200, '验证码已发送到邮箱，请注意查收！', 'NULL');
        }
    }

}
