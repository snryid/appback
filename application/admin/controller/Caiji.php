<?php
namespace app\admin\controller;
use QL\QueryList;
class Caiji extends Common
{
	

    public function index()
    {   $baseUrl='1111';
        $rules=[
        // "title"=>array('h1','text'),
        // "url"=>array('h1 a','href'),
        // "desc"=>array('dl dd','text'),
        // "time"=>array('h4 span.view_time','text'),
        // "img"=>array('dl dt a img','src'),
        "content"=>array('b','text','',function($content) use($baseUrl){
            return str_replace('综合电影','484519446', $content);
        }),

        ];
        $hj = QueryList::Query('http://www.dytt8.net/html/gndy/rihan/index.html',$rules,'','UTF-8','gbk');
        print_r($hj->data);
        
    }

    public function lst()
    {   
        $rules=[
        "title"=>array('div.good_right>h1','text'),
        'bianhao'=>array('span.canshu_nub','text'),
        'geshi'=>array('ul.miao_right li:eq(0)','text'),
        'tiji'=>array('ul.miao_right li:eq(1)','text'),
        ];
        $hj = QueryList::Query('http://sucai.redocn.com/zhanban_8242005.html',$rules,'','UTF-8','gbk');
        print_r($hj->data);
        
    }
    
   
}
