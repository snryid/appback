<?php
namespace app\admin\controller;

class Db extends Common
{
    
    public function bak(){
        $type=input("tp");
        $name=input("name");
        $sql=new \org\Baksql(\think\Config::get("database"));
        switch ($type)
        {
        case "backup": //备份
        return $sql->backup();
        break;
        case "dowonload": //下载
        $sql->downloadFile($name);
        break;
        case "restore": //还原
        return $sql->restore($name);
        break;
        case "del": //删除
        return $sql->delfilename($name);
        break;
        default: //获取备份文件列表
        return $this->fetch("db_bak",["list"=>$sql->get_filelist()]);
         
        }
         
    }

}

