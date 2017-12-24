<?php

namespace app\index\controller;

use think\Controller;
use think\Db;

class Newsapi extends Controller{
    /*
    *会展资讯：顶部轮播图和列表
    */
    public function showNews()
    {
        $type = input('type');
        $page = input('page')?input('page'):1;
        $arr = array();
        if($type && is_numeric($type)){
            //轮播图
            $list = model('banner')->showBanner($type);
            $arr[0]['isTopImages']     = true;
            $arr[0]['isTopNews']       = false;
            $arr[0]['isImagesNews']    = false;
            $arr[0]['isNormalNews']    = false;
            $arr[0]['isBigImagesNews'] = false;
            $arr[0]['isVideo']         = false;
            $arr[0]['contents']        = $list;

            $lists = model('News')->showList($type, $page);
            $i=1;
            foreach ($lists as $v) {
                $arr[$i]['isTopImages'] = false;
                if($v['is_top']){
                    $arr[$i]['isTopNews'] = true;
                }else{
                    $arr[$i]['isTopNews'] = false;
                }
                //判断多图
                switch ($v['type']) {
                    case 3:
                        $arr[$i]['imageURL1']       = $v['image1'];
                        $arr[$i]['imageURL2']       = $v['image2'];
                        $arr[$i]['imageURL3']       = $v['image3'];
                        $arr[$i]['isNormalNews']    = false;
                        $arr[$i]['isImagesNews']    = true;
                        $arr[$i]['isBigImagesNews'] = false;
                        $arr[$i]['isVideo']         = false;
                        break;
                    case 4:
                        $arr[$i]['imageURL']        = $v['image1'];
                        $arr[$i]['isNormalNews']    = false;
                        $arr[$i]['isImagesNews']    = false;
                        $arr[$i]['isBigImagesNews'] = true;
                        $arr[$i]['isVideo']         = false;
                        break;
                    case 5:
                        $arr[$i]['imageURL']        = $v['cover'];
                        $arr[$i]['isNormalNews']    = false;
                        $arr[$i]['isImagesNews']    = false;
                        $arr[$i]['isBigImagesNews'] = false;
                        $arr[$i]['isVideo']         = true;
                        break;
                    default:
                        $arr[$i]['imageURL']        = $v['cover'];
                        $arr[$i]['isNormalNews']    = true;
                        $arr[$i]['isImagesNews']    = false;
                        $arr[$i]['isBigImagesNews'] = false;
                        $arr[$i]['isVideo']         = false;
                        break;
                }
                $arr[$i]['title']       = $v['name'];
                $arr[$i]['bottomTitle'] = $v['tab'];
                $arr[$i]['itemId']      = $v['id'];
                $i++;
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
    *会展资讯：资讯详情
    */
    public function newsInfo()
    {
        $itemId = input('itemId');
        $uid    = input('uid');
        if($itemId && is_numeric($itemId) && $uid && is_numeric($uid)){
            //将用户浏览咨询存入资讯历史
            $result = Db::table('pre_min_information_history')
                ->field(['id'])
                ->where('user_id =:uid and news_id = :nid')
                ->bind(['uid'=>[$uid,\PDO::PARAM_INT],'nid'=>[$itemId,\PDO::PARAM_INT]])
                ->find();
            if(!$result){
                Db::table('pre_min_information_history')->insertGetId(array('user_id'=>$uid,'news_id'=>$itemId));
            }
            $info = model('News')->getInfo($itemId);
            if($info){
                return json($info,200);
            }else{
                return json(array('数据获取失败'),400);
            }
        }else{
            return json(array('非法参数'),400);
        }
    }

    /*
    *会展资讯：顶部轮播图详情
    */
    public function bannerInfo()
    {
        $itemId = input('itemId');
        if($itemId && is_numeric($itemId)){
            $info = model('Banner')->bannerInfo($itemId);
            if($info){
                return json($info,200);
            }else{
                return json(array('数据获取失败'),400);
            }
        }else{
            return json(array('非法参数'),400);
        }
    }
}