<?php
namespace app\index\model;

use think\Model;
use think\Db;
use think\Request;

class Banner extends Model{
    /*
    *查询轮播
    */
    public function checkBanner($page,$id=''){
        $list = Db::table('pre_min_banner')->alias('b')
        ->field('b.id,b.banner,b.content,nc.id as news_category_id,nc.category,b.editor,b.time')
        ->join('pre_min_news_category nc','b.news_category_id=nc.id')
        ->where(['b.status'=>1,'nc.status'=>1]);
        if($id){
            $list->where(['b.id'=>$id]);
        }
        if($page){
            $list->order('b.create_time DESC')->page($page,PAGESIZE);
        }
        $lists = $list->select();
        if($lists){
            return $lists;
        }else{
            return false;
        }
    }
    /*
    *获得总条数
    */
    public function getCounts()
    {
        $count = Db::table('pre_min_banner')->field('id')->where(['status'=>1])->count();
        return $count;
    }

    /*
    *添加
    */
    public function add($data)
    {
        $addbanner = [
            'news_category_id' => $data['news_category_id'],
            'banner' => $data['banner'],
            'content' => $data['content'],
            'editor' => $data['editor'],
            'time'  => $data['time']
        ];
        $res = Db::table('pre_min_banner')->insertGetId($addbanner);
        return $res;
    }

    /*
    *修改
    */
    public function upd($id,$data)
    {
        $updbanner = [
            'news_category_id' => $data['news_category_id'],
            'banner' => $data['banner'],
            'content'  => $data['content'],
            'editor' => $data['editor'],
            'time'  => $data['time']
        ];
        $res = Db::table('pre_min_banner')->where(['id'=>$id])->update($updbanner);
        return $res;
        /*if($id>0){
            $updinfo = array();
            $images = $data['image'];
            $info = $data['info'];
            foreach ($info as $key => $vo) {
                $res = Db::table('pre_min_banner_info')->where(['id'=>$id])->update(['banner_id'=>$id,'img' => $images[$key],'info' => $vo]);
            }
            if($res){
                return $res;
            }
        }*/
    }



    /*
    *删除
    */
    public function del($id)
    {
        if($id != ""){
        $res = Db::table('pre_min_banner')->where('id = :id')->bind(['id'=>[$id,\PDO::PARAM_INT]])->update(['status'=>0]);
        return $res;
        }
    }

    /*
    *轮播图API
    */
    public function showBanner($type)
    {
        $list = Db::table('pre_min_banner')->field('id as itemId,banner as imageURL')->where('news_category_id=:type and status = 1')->bind(['type'=>[$type,\PDO::PARAM_INT]])->order('create_time DESC')->limit(3)->select();
        return $list;
    }

    /*
    *轮播详情
    */
    public function bannerInfo($id)
    {
        return $list = Db::table('pre_min_banner')->field('editor,time,banner as image1,content as content1')->where('id=:id and status =1')->bind(['id'=>[$id,\PDO::PARAM_INT]])->find();
    }
}
