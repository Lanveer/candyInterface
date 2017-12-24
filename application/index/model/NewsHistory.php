<?php
namespace app\index\model;

use think\Model;
use think\Db;

class NewsHistory extends Model{
    public function getHistory($uid)
    {
        $list = Db::table('pre_min_news_history')->field('history')
        ->where('user_id =:id')->bind(['id'=>[$uid,\PDO::PARAM_INT]])->select();

        return $list;
    }

    public function saveHistory($uid, $keyword)
    {
        $result = Db::table('pre_min_news_history')->insertGetId(array('user_id'=>$uid, 'history'=>$keyword));
        return $result;
    }
}