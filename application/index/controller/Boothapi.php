<?php

namespace app\index\controller;

use think\Controller;
use think\Db;

class Boothapi extends Controller{
    /*
    *搜索时弹出的历史和推荐
    */
    public function showTipword()
    {
        $uid = input('uid');
        $arr = array();
        if(!$uid){
            return json('缺少参数');
        }
        //获取历史搜索词
        $historys = model('NewsHistory')->getHistory($uid);

        if($historys){
            foreach ($historys as $history) {
                $arr['history'][] = $history['history'];
            }
        }else{
            $arr['history'] = [];
        }
        //获取推荐酒店展馆
        $recommends = model('ExhibitionHotel')->getRecommend();
        if($recommends){
            foreach ($recommends as $recommend) {
                $arr['recommend'][] = $recommend;
            }
        }else{
            $arr['recommend'] = [];
        }

        return json($arr,200);
    }

    /*
    *搜索展位
    */
    public function searchBooth()
    {
        $uid      = input('uid');
        $keyword  = input('keyword');
        if(!is_numeric($uid)){
            return json('非法参数');
        }
        if(!$keyword){
            return json('请输入搜索关键字');
        }
        //保存搜索关键字
        model('NewsHistory')->saveHistory($uid, $keyword);

        $page  = input('page')?input('page'):1;
        //获取总条数
//        $count = model('Booth')->getBoothNum('', '', $keyword);
//        $pages = ceil($count/PAGESIZE);

        //固定页码在有效范围内
        if($page<1){
            $page = 1;
        }
//        if($page>$pages){
//            $page = $pages;
//        }

        $lists = model('Booth')->getBooth($keyword, $page);

        if($lists){
            $arr = array();
            foreach ($lists as $list) {
                if($list['type'] == 1){
                    $list['exhibition_code'] = $list['detail'].'-'.$list['exhibition_code'].'号';
                }else{
                    $list['exhibition_code'] = $list['detail'].'-'.$list['exhibition_code'].'号';
                }

                unset($list['floor']);
                $label =array_merge(explode(',', $list['primary_label']),explode(',', $list['two_label']),explode(',', $list['three_label']));
                $tag = [];
                foreach ($label as $item){
                    if(!$item){
                        continue;
                    }
                    $temp['title'] = $item;
                    $tag[] = $temp;
                }
                $list['tag'] = $tag;
                unset($list['primary_label']);
                unset($list['two_label']);
                unset($list['three_label']);
                $arr[] = $list;
            }
            return json($arr,200);
        }else{
            return json([],200);
        }
        return json(['数据获取失败'],400);
    }
}