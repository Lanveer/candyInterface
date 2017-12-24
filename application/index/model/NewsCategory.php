<?php
namespace app\index\model;

use think\Model;
use think\Db;

class NewsCategory extends Model{
    /*
    *新闻分类查询
    */
    public function checkNewsCategory($page='')
    {
        $res = Db::table('pre_min_news_category')
        ->field('id,category')
        ->where(['status'=>1]);
        $res->order('create_time DESC');
        if($page){
            $res->page($page,PAGESIZE);
        }
        $result = $res->select();
        return $result;

    }
    /*
    *新闻分类总数
    */
    public function getConts()
    {
        $counts = Db::table('pre_min_news_category')->field('id')->where(['status'=>1])->count();
        return $counts;
    }

    /*
    *新增分类查询
    */
    public function addNewsCategory($category)
    {
        $res = Db::table('pre_min_news_category')->insert(['category'=> $category]);
        return $res;
    }

    /*
    *删除分类查询
    */
    public function delNewsCategory($id)
    {
        if($id){
            $res = Db::table('pre_min_news_category')->where('id',$id)->update(['status'=>0]);
            return $res;
        }
    }

    /*
    *更新分类查询
    */
    public function updNewsCategory($id,$category)
    {
        if($id !='' &&$category !=''){
            $res = Db::table('pre_min_news_category')->where(['id' => $id])
            ->update(['category' => $category]);
        }
        return $res;
    }
 }