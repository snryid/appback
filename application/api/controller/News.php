<?php
namespace app\api\controller;

use app\api\controller\Common;

class News extends Common
{
    /**
     * 获取新闻类型
     * @return [string] [返回参数]
     */
    public function new_type()
    {
        /*******   从数据库调取数据  ***/
        $data = db('cate')
            ->field('catename')
            ->order('id desc')
            ->select();
        /****    返回数据    *****/
        if ($data) {
            $this->return_msg(200, '获取新闻类型成功！', $data);
        } else {
            $this->return_msg(400, '获取新闻类型失败！', 'NULL');
        }
    }

    /**
     * 获取新闻条数
     * @return [string] [返回参数]
     */
    public function sum_news()
    {
        /*******   获取数据  ***/
        $data = db('article')->count();
        /****    返回数据    *****/
        if ($data) {
            $this->return_msg(200, '获取新闻类型成功！', $data);
        } else {
            $this->return_msg(400, '获取新闻类型失败！', 'NULL');
        }
    }

    /**
     * 获取新闻
     * @return [array] [返回参数]
     */
    public function query()
    {

        /****** 接受参数  ******/
        $data = $this->params;

        /****** 获取分类ID查询指定新闻  ******/
        if (!$data) {
            $this->return_msg(400, '上传参数不合法！', $data);
        }
        $db_res = db('article')
            ->alias('a')
            ->field('a.*,c.catename')
            ->order('id desc')
            ->join('cate c', 'c.id=a.cateid', 'left')
            ->limit($data['news_sum'])
            ->page($data['news_page'])
            ->select();

        /****** 返回数据  ******/
        if (!$db_res) {
            $this->return_msg(400, '获取新闻失败！', $data);
        } else {
            $this->return_msg(200, '获取新闻成功！', $db_res);
        }
    }

    /**
     * 获取新闻推荐
     * @return [array] [返回参数]
     */
    public function recommend()
    {
        /***   查询满足条件的参数    *****/
        $where['rec'] = 1;
        $db_res       = db('article')
            ->alias('a')
            ->field('a.*,c.catename')
            ->order('id desc')
            ->where($where)
            ->join('cate c', 'c.id=a.cateid', 'left')
            ->select();
        /****** 返回数据  ******/
        if (!$db_res) {
            $this->return_msg(400, '获取新闻失败！', $data);
        } else {
            $this->return_msg(200, '获取新闻成功！', $db_res);
        }
    }
}
