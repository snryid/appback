<?php
namespace app\admin\model;

use app\admin\model\Video as Video;
use think\Model;

class Video extends Model
{

    protected static function init()
    {
        Video::event('before_insert', function ($video) {
            if ($_FILES['video']) {
                $file = request()->file('video');
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'video');
                if ($info) {
                    $path               = '/uploads' . DS . 'video' . DS . $info->getSaveName();
                    $video_path         = str_replace('\\', '/', $path);
                    $video['video_url'] = $video_path;
                }
            }
        });

        Video::event('before_update', function ($video) {
            if ($_FILES['video']) {
                $vid        = Video::find($video->id);
                $video_path = $_SERVER['DOCUMENT_ROOT'] . $vid['video_url'];
                if (file_exists($video_path)) {
                    @unlink($video_path);
                }
                $file = request()->file('video');
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'video');
                if ($info) {
                    $path               = '/uploads' . DS . 'video' . DS . $info->getSaveName();
                    $video_path         = str_replace('\\', '/', $path);
                    $video['video_url'] = $video_path;
                }

            }
        });

        Video::event('before_delete', function ($video) {

            $vid        = Video::find($video->id);
            $video_path = $_SERVER['DOCUMENT_ROOT'] . $vid['video_url'];
            if (file_exists($video_path)) {
                @unlink($video_path);
            }
        });

    }

    public function videotree()
    {
        $videotree = $this->order('sort desc')->select();
        return $this->sort($videotree);
    }

    public function sort($data, $vid = 0, $level = 0)
    {
        static $arr = array();
        foreach ($data as $k => $v) {
            if ($v['vid'] == $vid) {
                $v['level'] = $level;
                $arr[]      = $v;
                $this->sort($data, $v['type_id'], $level + 1);
            }
        }
        return $arr;
    }

}
