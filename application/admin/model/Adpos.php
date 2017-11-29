<?php
namespace app\admin\model;
use think\Model;
class Adpos extends Model
{
	protected static function init()
    {

        //删除广告位前置钩子
        Adpos::beforeDelete(function ($adpos) {
           $adposid=input('id');
           Ad::destroy(['adpos_id' => $adposid]);
        });


    }




    

   
}
