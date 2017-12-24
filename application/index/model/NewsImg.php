<?php
namespace app\index\model;

use think\Model;
use think\Db;

class NewsImg extends Model{
    /*
    *获取资讯图片
    */
    public function getImgs($id)
    {
        $list = Db::table('pre_min_news_img')->field('img')
        ->where('status=1 and news_id =:id')->bind(['id'=>[$id,\PDO::PARAM_INT]])->select();

        return $list;
    }
}