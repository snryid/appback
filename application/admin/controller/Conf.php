<?php
namespace app\admin\controller;

use phpmailer\phpmailer;

class Conf extends Common
{
    public function conflst()
    {
        if (request()->isPost()) {
            $data     = input('post.');
            $enameArr = db('conf')->column('ename');
            $confArr  = array();
            //附件类型数据处理
            $imgcolumn = db('conf')->where('dt_type', 6)->column('ename');
            foreach ($data as $k => $v) {
                $confArr[] = $k;
                if (is_array($v)) {
                    $v = implode(',', $v);
                }
                $v = trim($v, " ");
                db('conf')->where('ename', $k)->update(['value' => $v]);
            }

            foreach ($enameArr as $k => $v) {
                if (!in_array($v, $confArr) && !in_array($v, $imgcolumn)) {
                    db('conf')->where('ename', $v)->update(['value' => '']);
                }
            }

            foreach ($imgcolumn as $k => $v) {
                if ($_FILES[$v]['tmp_name'] != '') {
                    $file   = request()->file($v);
                    $info   = $file->move(ROOT_PATH . 'public/static/admin/uploads/conf');
                    $imgSrc = $info->getSaveName();
                    $imgSrc = str_replace('\\', '/', $imgSrc);
                    if ($imgSrc != '') {
                        db('conf')->where('ename', $v)->update(['value' => $imgSrc]);
                    }
                }
            }
            $this->success('修改配置成功！');
            return;
        }
        $confRes = db('conf')->select();
        $this->assign('confRes', $confRes);
        return view();
    }

    public function lst()
    {
        $confRes = db('conf')->field('id,cname,ename,value,values')->paginate(15);
        $this->assign('confRes', $confRes);
        return view();
    }

    public function add()
    {
        if (request()->isPost()) {
            $data     = input('post.');
            $validate = validate('conf');
            if (!$validate->scene('add')->check($data)) {
                $this->error($validate->getError());
            }
            $add = db('conf')->insert($data);
            if ($add) {
                $this->success('添加配置项成功！', url('lst'));
            } else {
                $this->error('添加配置项失败！');
            }
        }
        return view();
    }

    public function edit($id)
    {
        if (request()->isPost()) {
            $data     = input('post.');
            $validate = validate('conf');
            if (!$validate->scene('edit')->check($data)) {
                $this->error($validate->getError());
            }
            $save = db('conf')->update($data);
            if ($save) {
                $this->success('修改配置成功！', url('lst'));
            } else {
                $this->error('修改配置失败！');
            }
        }
        $confs = db('conf')->find($id);
        $this->assign('confs', $confs);
        return view();
    }

    public function del($id)
    {
        $del = db('conf')->delete($id);
        if ($del) {
            $this->success('删除配置项成功！', url('lst'));
        } else {
            $this->error('删除配置失败！');
        }
    }

    public function set_chcek_email()
    {
        if (request()->isAjax()) {
            $email            = input('emailname');
            $where['cf_type'] = '4';
            $email_db         = db('conf')->where($where)->select();
            if (empty($email_db[2]['value'])) {
                $return_data['msg'] = 0;
            }
            $coding         = $email_db[0]['value'] == 'SMTP服务' ? 'SMTP' : 'Mail';
            $email_ssl      = $email_db[1]['value'] == '是' ? 'ssl' : '';
            $email_site     = $email_db[2]['value'];
            $email_port     = $email_db[3]['value'];
            $email_name     = $email_db[4]['value'];
            $email_password = $email_db[5]['value'];
            $email_revert   = $email_db[6]['value'];
            $email_code     = $email_db[7]['value'] == 'UTF8' ? 'utf8' : '';

            $toemail = $email;
            $mail    = new PHPMailer();
            $mail->isSMTP();
            $mail->CharSet    = $email_code;
            $mail->Host       = $email_site;
            $mail->SMTPAuth   = true;
            $mail->Username   = $email_name;
            $mail->Password   = $email_password;
            $mail->SMTPSecure = $email_ssl;
            $mail->Port       = $email_port;
            $mail->setFrom($email_name, 'JINRI后台邮件测试');
            $mail->addAddress($toemail, 'JINRIAPP');
            $mail->addReplyTo($email_revert, 'Reply');
            $mail->Subject = "APP后台邮件服务测试";
            $mail->Body    = "这是一个测试邮件,收到此邮件表示邮件服务安装成功,本邮件请勿回复！";
            if (!$mail->send()) {
                $return_data['msg']  = 1;
                $return_data['data'] = $mail->ErrorInfo;
            } else {
                $return_data['msg']     = 2;
                $return_data['toemail'] = $toemail;
            }
            /******* 返回信息并终止脚本   ********/
            echo json_encode($return_data);die;
        }
    }

}
