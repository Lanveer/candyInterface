<?php

namespace app\index\model;

use think\Model;
use think\Db;
use think\Request;

class Booth extends Model{
    /*
    *查询展位
    */
    public function checkBooth($page, $keyword)
    {
        $list = Db::table('pre_min_booth')->alias('b')
        ->field('b.id,b.company,b.contact,b.tel,b.floor,.b.exhibition_code,b.address,b.start_time,b.end_time,b.img,b.primary_label,b.two_label,b.three_label,eh.name as location')
        ->join('pre_min_exhibition_hotel eh','eh.id=b.exhibition_hotel_id')
        ->where(['b.status'=>1]);
        if($keyword){
            $list->where('b.company|eh.name|b.exhibition_code','like','%'.$keyword.'%');
        }
        $lists = $list->order('b.create_time DESC')->page($page,PAGESIZE)->select();
        return  $lists;
    }
    /*
    *查询展位总条数
    */
    public function getCounts($keyword)
    {
        $count = Db::table('pre_min_booth')->where(['status'=>1]);
        if($keyword){
            $count->where('company|exhibition_code','like','%'.$keyword.'%');
        }
        return $count->count();
    }

    /*
    *根据id获得展位
    */
    public function getOne($id)
    {
        $res = Db::table('pre_min_booth')->field('id,company,contact,address,img,tel,exhibition_hotel_id,exhibition_code,product,floor,is_hot,start_time,end_time,img,primary_label,two_label,three_label')->where(['id'=>$id,'status'=>1])->find();
        return $res;
    }

    //关键字搜索
    public function getBooth($keyword, $page=1)
    {
        $list = Db::table('pre_min_booth')->alias('b')
            ->field('b.id,b.company,eh.name as hotel,eh.type,b.contact,f.detail,b.tel,exhibition_code,b.primary_label,b.two_label,b.three_label,b.img as imageUrl')
            ->join('pre_min_exhibition_hotel eh','b.exhibition_hotel_id = eh.id')
            ->join('pre_min_floor f','b.floor = f.id')
            ->where(['b.status'=>1]);
        if($keyword){
            switch ($keyword) {
                case '热门':
                    $list->where('b.is_hot = 1');
                    break;
                case '全部':
                    break;
                default:
                    //可搜索酒店标签，酒店名，几号馆或者几层楼
                    $list->where('b.primary_label|b.two_label|b.three_label|b.company|eh.name','like',"%$keyword%");
                    break;
            }

        }
        $lists = $list->order('b.create_time DESC')->page($page,PAGESIZE)->select();

        return  $lists;
    }

    //获取酒店或展馆(楼层/几号馆)对应展位数量
    public function getBoothNum($hotelid=0, $floor=0, $keyword='')
    {
        $num = Db::table('pre_min_booth')->where(['status'=>1]);
        if($hotelid){
            $num->where('exhibition_hotel_id =:id and status =1')->bind(['id'=>[$hotelid,\PDO::PARAM_INT]]);
        }
        if($keyword){
            $num->where('primary_label|two_label|three_label|company','like',"%$keyword%");
        }
        if($floor){
            $num->where('floor =:floor')->bind(['floor'=>[$floor,\PDO::PARAM_INT]]);
        }
        return $num->count();
    }

    //获取展位列表
    public function getBoothList($hotelid, $page, $floor)
    {
        $list = Db::table('pre_min_booth')->alias('b')
            ->field('b.id,b.company,eh.type,eh.name as hotel,b.contact,b.tel,exhibition_code,b.primary_label,b.two_label,b.three_label,f.detail,b.img as imageUrl')
            ->join('pre_min_exhibition_hotel eh','b.exhibition_hotel_id = eh.id')
            ->join('pre_min_floor f','b.floor = f.id')
            ->where(['b.status'=>1])
            ->where('exhibition_hotel_id =:id')->bind(['id'=>[$hotelid,\PDO::PARAM_INT]])
            ->where('floor =:floor')->bind(['floor'=>[$floor,\PDO::PARAM_INT]])
            ->order('b.create_time DESC')
            ->page($page, PAGESIZE);
        return  $list->select();
    }
}