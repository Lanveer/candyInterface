<?php
namespace app\index\model;

use think\Model;
use think\Db;

class News extends Model{
    /*
    *查询新闻列表
    */
    public function checkNews($page,$keyword)
    {
        $list = Db::table('pre_min_news')->alias('n')
        ->field('n.id,n.name,n.editor,n.is_top,nc.category,n.time')
        ->join('pre_min_news_category nc','n.news_category_id = nc.id')
        ->where(['n.status'=>1,'nc.status'=>1]);
        if($keyword){
            $list->where('n.name|nc.category|n.editor','like','%'.$keyword.'%');
        }
        $lists = $list->order('n.create_time DESC')->page($page,PAGESIZE)->select();
        return $lists;
    }

    /*
    *获得新闻列表总条数
    */
    public function getCounts($keyword)
    {
        $count = Db::table('pre_min_news')->alias('n')->field('n.id')
        ->join('pre_min_news_category nc','n.news_category_id = nc.id')
        ->where(['n.status'=>1,'nc.status'=>1]);
        if($keyword){
            $count->where('n.name|nc.category|n.editor','like','%'.$keyword.'%');
        }
        $counts = $count->count();
        return $counts;
    }

    /*
    *根据id获得新闻
    */
    public function getOne($id)
    {
        $res = Db::table('pre_min_news')->alias('n')
        ->field('n.id,n.name,nc.id as news_category_id,n.type,n.tab,n.editor,n.cover,n.time,n.content1,n.content2,n.content3,n.image1,n.image2,n.image3,n.is_top')
        ->join('pre_min_news_category nc','n.news_category_id = nc.id')
        ->where(['n.id'=>$id,'n.status'=>1])->find();
        return $res;
    }

    /*
    *会展资讯列表
    */
    public function showList($type, $page)
    {
        $list = Db::table('pre_min_news')->field('id,name,type,tab,is_top,cover,image1,image2,image3')
        ->where('status=1 and news_category_id =:type')->bind(['type'=>[$type,\PDO::PARAM_INT]])->order('is_top DESC')->page($page,PAGESIZE)->select();
        return $list;
    }

    /*
    * 资讯详情
    */
    public function getInfo($id)
    {
        $list = Db::table('pre_min_news')->field('name as title,editor,time,image1,image2,image3,content1,content2,content3,is_top')->where('id=:id and status=1')->bind(['id'=>[$id,\PDO::PARAM_INT]])->find();
        return $list;
    }
}