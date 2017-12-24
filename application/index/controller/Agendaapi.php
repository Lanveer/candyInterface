<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;

class Agendaapi extends Controller{
    /*
    *顶部滑动栏日期数据
    */
    public function getDate()
    {
        $date = Db::table('pre_min_date')->select();
        $arr = array();
        foreach ($date as $vi) {
            $da = date('d')<19?'3月19日':date('n月d日');
            $da = date('d')>28?'3月28日':$da;
            if($da == $vi['title']){
                $vi['didSelected'] = true;
            }else{
                $vi['didSelected'] = false;
            }
            $arr[] = $vi;
        }
        if($arr){
            return json($arr,200);
        }else{
            return json(array('数据获取失败'),400);
        }
    }

    /*
    *日程列表内容数据
    */
    public function agendaList()
    {
        $type = input('cate');
        $hot  = input('hot');
        if(($type && is_numeric($type)) || ($hot && is_numeric($hot))){
            $page = input('page')?input('page'):1;
            $list = model('Agenda')->agendaList($type,$hot,$page);
            $arr  = array();
            foreach ($list as $va) {
                $arr[$va['id']]['imageURL']   = $va['img'];
                $arr[$va['id']]['imageTitle'] = $va['name'];
                $arr[$va['id']]['title']      = $va['theme'];
                $arr[$va['id']]['guest']      = $va['guest'];
                $arr[$va['id']]['time']       = $va['title'].' '.$va['time'];
                $arr[$va['id']]['location']   = $va['address'];
                $arr[$va['id']]['itemId']     = $va['id'];
                $arr[$va['id']]['latitude']   = $va['lat'];
                $arr[$va['id']]['longitude']  = $va['lng'];
            }
            if($arr){
                return json($arr,200);
            }else{
                return json([],200);
            }
        }else{
            return json(array('非法参数'),400);
        }
    }

    /*
    *日程详情内容数据
    */
    public function agendaInfo()
    {
        $id = input('itemId');
        if($id && is_numeric($id)){
            $info = model('Agenda')->agendaInfo($id);
            if($info){
                return json($info,200);
            }else{
                return json(array('获取数据失败'),500);
            }

        }else{
            return json(array('非法参数'),400);
        }
    }
}