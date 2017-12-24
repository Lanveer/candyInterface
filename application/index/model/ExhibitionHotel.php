<?php

namespace app\index\model;

use think\Model;
use think\Db;

class ExhibitionHotel extends Model{
    /*
    *查询酒店或者展馆
    */
    public function checkExhibitionHotel($type, $page, $keyword)
    {
        $list = Db::table('pre_min_exhibition_hotel')->field('id,name,logo,is_show,introduce,address,lat,tel,lng,primary_label,two_label,three_label')->where(['type'=>$type, 'status'=>1]);
        if($keyword){
            $list->where('name|primary_label|two_label|three_label', 'like', '%'.$keyword.'%');
        }
        $lists = $list->order('create_time DESC')->page($page,PAGESIZE)->select();
        if($lists){
            return $lists;
        }
        return false;
    }

    public function getCounts($type, $keyword)
    {
        $count = Db::table('pre_min_exhibition_hotel')->field('id,name,logo,introduce,address')->where(['type'=>$type, 'status'=>1]);
        if($keyword){
            $count->where('name|primary_label|two_label|three_label', 'like', '%'.$keyword.'%');
        }
        return $count->count();
    }

    /*
    *修改酒店或者展馆
    */
    public function updInfo($data)
    {


        $info   = Db::table('pre_min_exhibition_hotel')->where(['id'=>$data['id']]);
        $result = $info->update($data);
        return $result;
    }

    /*
    *通过id获得酒店或者展馆
    */
    public function getList($id,$type)
    {
        $list = Db::table('pre_min_exhibition_hotel');
        if($type == 1){
            $list->field('id,name,address,is_show,introduce,tel,logo,lat,lng,primary_label,two_label,three_label,bgimg_id,recommend');
        }
        if($type ==2){
            $list->field('id,name,address,introduce,tel,logo,lat,lng,bgimg_id,recommend');
        }
        $lists = $list->where(['id'=>$id])->find();
        return $lists;
    }

    /*
    *酒店-展馆信息：id name type
    */
    public function getLists()
    {
        $info = Db::table('pre_min_exhibition_hotel')->field('id as exhibition_hotel_id,name,type')->where(['status'=>1])->select();
        return $info;
    }


    /*
    *小程序接口开始
    */
    /*
    *会展展位：酒店(type:1)、展馆(type:2)
    */
    public function showExhibitionHotel($type)
    {
        $list = Db::table('pre_min_exhibition_hotel')->alias('eh');
        if($type==1){
            $list->field('eh.id,b.back_image,b.hotel_num,eh.name,eh.primary_label,eh.two_label,eh.three_label,eh.introduce,eh.address,eh.logo,eh.lat,eh.lng,eh.bgimg_id');
        }
        if($type==2){
            $list->field('eh.id,b.back_image,b.hotel_num,eh.name,eh.introduce,eh.address,eh.logo,eh.lat,eh.lng,eh.bgimg_id');
        }
        $list->join('pre_min_bgimg b','eh.bgimg_id = b.id')->where(['eh.status'=>1,'eh.type'=>$type,'eh.is_show'=>0]);
        $list->order('eh.recommend desc,eh.create_time desc');
        $lists = $list->select();
        return $lists;
    }

    //获取推荐酒店
    public function getRecommend()
    {
        $list = Db::table('pre_min_exhibition_hotel')
            ->field('id,name')
            ->where(['status'=>1, 'recommend'=>1, 'is_show'=>0])
            ->select();
        return $list;
    }

    public function getInfo($hotelid)
    {
        $info = Db::table('pre_min_exhibition_hotel')
            ->field('type,name,introduce,logo,address,lat,lng')
            ->where('id =:id')->bind(['id'=>[$hotelid,\PDO::PARAM_INT]])
            ->select();
        return $info;
    }
}