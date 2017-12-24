<?php

namespace app\index\model;

use think\Model;
use think\Db;
use think\Request;

class Agenda extends Model{
    /*
    *查询日程
    */
    public function checkAgenda($page,$theme,$date,$exhibitionhotel)
    {
        $list = Db::table('pre_min_agenda')->alias('a')
        ->field('a.id,d.title as date,a.theme,a.time,a.guest,a.address,eh.name,a.is_hot')
        ->join('pre_min_exhibition_hotel eh','eh.id=a.exhibition_hotel_id')
        ->join('pre_min_date d','a.date_id=d.pageId')
        ->where(['a.status'=>1]);

        if($theme){
            $list->where('a.theme','like','%'.$theme.'%');
        }
        if($date){
            $list->where('d.title','like','%'.$date.'%');
        }
        if($exhibitionhotel){
            $list->where('eh.name','liek', '%'.$exhibitionhotel.'%');
        }
        $lists = $list->order('a.create_time DESC')->page($page,PAGESIZE)->select();

        return  $lists;
    }

    /*
    *查询日程总条数
    */
    public function getCounts($theme,$dateid,$exhibitionhotel)
    {
        $count = Db::table('pre_min_agenda')->alias('a')->field('a.id')
        ->join('pre_min_date d','a.date_id=d.pageId')
        ->join('pre_min_exhibition_hotel eh','a.exhibition_hotel_id = eh.id')
        ->where(['a.status'=>1]);
        if($theme){
            $count->where('a.theme','like','%'.$theme.'%');
        }
         if($dateid){
            $count->where('d.title','like','%'.$dateid.'%');
        }
        if($exhibitionhotel){
            $count->where('eh.name','like','%'.$exhibitionhotel.'%');
        }
        $counts = $count->count();
        return $counts;
    }

    /*
    *搜索日程
    */
    public function getOne($id)
    {
        $res = Db::table('pre_min_agenda')->field('id,date_id,auditoria,time,theme,guest,img,infoimg,address,exhibition_hotel_id,introduce,is_hot')
        ->where('id =:id')->bind(['id'=>[$id,\PDO::PARAM_INT]])->find();
        return $res;
    }

    /*
    *日程列表内容接口
    */
    public function agendaList($type,$hot,$page)
    {
        $list = Db::table('pre_min_agenda')->alias('a')
        ->field('a.id,a.time,a.theme,a.guest,a.img,eh.lat,eh.lng,a.address,d.title,eh.name')
        ->join('pre_min_date d','a.date_id = d.pageId')
        ->join('pre_min_exhibition_hotel eh','a.exhibition_hotel_id=eh.id')
        ->where(['a.status'=>1]);
        if($type){
            $list->where('a.date_id =:type')->bind(['type'=>[$type,\PDO::PARAM_INT]]);
        }
        if($hot){
            $list->where('a.is_hot =:hot')->bind(['hot'=>[$hot,\PDO::PARAM_INT]]);
        }
        $lists = $list->order('a.create_time DESC')->page($page,PAGESIZE)->select();
        return $lists;
    }

    /*
    *日程详情内容接口
    */
    public function agendaInfo($id)
    {
        $info = Db::table('pre_min_agenda')->field('theme as title,infoimg,introduce')->where('id =:id and status = 1')->bind(['id'=>[$id,\PDO::PARAM_INT]])->find();
        return $info;
    }

}
