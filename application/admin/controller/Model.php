<?php
namespace app\admin\controller;

use think\Db;

class Model extends Common
{
    public function lst()
    {
        $modelRes = db('model')->order('id desc')->paginate(10);
        $prefix   = config("database.prefix");
        $this->assign(array(
            'modelRes' => $modelRes,
            'prefix'   => $prefix,
        ));
        return view();
    }

    public function add()
    {
        if (request()->isPost()) {
            $data     = input('post.');
            $validate = validate('model');
            if (!$validate->scene('add')->check($data)) {
                $this->error($validate->getError());
            }
            $add = db('model')->insert($data);
            if ($add) {
                $tableName = $data['table_name'];
                $tableName = config("database.prefix") . $tableName;
                $sql       = "create table {$tableName} (aid int unsigned not null) engine=MYISAM default charset=utf8";
                Db::execute($sql);
                $this->success('添加模型成功！', url('lst'));
            } else {
                $this->error('添加模型失败！');
            }
            return;
        }
        return view();

    }

    public function edit()
    {
        if (request()->isPost()) {
            $data         = input('post.');
            $oldTableName = db('model')->field('table_name')->find($data['id']);
            $oldTableName = $oldTableName['table_name'];
            $validate     = validate('model');
            if (!$validate->scene('edit')->check($data)) {
                $this->error($validate->getError());
            }
            $save = db('model')->update($data);
            if ($oldTableName != $data['table_name']) {
                $prefix       = config("database.prefix");
                $oldTableName = $prefix . $oldTableName;
                $tableName    = $prefix . $data['table_name'];
                $sql          = " alter table {$oldTableName} rename {$tableName} ";
                Db::execute($sql);
            }
            if ($save !== false) {
                $this->success('修改模型成功！', url('lst'));
            } else {
                $this->error('修改模型失败！');
            }
            return;
        }
        $models = db('model')->find(input('model_id'));
        $this->assign('models', $models);
        return view();
    }

    //ajax删除模型
    public function ajaxdel()
    {
        $modelId   = input('id');
        $tableName = input('table_name');
        $del       = db('model')->delete($modelId);
        $sql       = "drop table {$tableName}";
        Db::execute($sql);
        if ($del) {
            echo 1; //删除记录和附加表成功
        } else {
            echo 2; //删除失败
        }

    }

    //ajax异步修改模型状态
    public function changestatus()
    {
        if (request()->isAjax()) {
            $modelid = input('modelid');
            $status  = db('model')->field('status')->where('id', $modelid)->find();
            $status  = $status['status'];
            if ($status == 1) {
                db('model')->where('id', $modelid)->update(['status' => 0]);
                echo 1; //由显示改为隐藏
            } else {
                db('model')->where('id', $modelid)->update(['status' => 1]);
                echo 2; //由隐藏改为显示
            }
        } else {
            $this->error("非法操作！");
        }
    }
}
