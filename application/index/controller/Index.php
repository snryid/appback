<?php
namespace app\index\controller;

use think\Controller;

header("Content-type: text/html; charset=utf-8");
class Index
{

    public function index()
    {

        return view();
    }

}
