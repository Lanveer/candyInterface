<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

class Exhibitionhotelapi extends Controller{
    /*
    *根据经纬度计算距离
    */
    private function getDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6367000; //approximate radius of earth in meters

        $lat1 = ($lat1 * pi() ) / 180;
        $lng1 = ($lng1 * pi() ) / 180;

        $lat2 = ($lat2 * pi() ) / 180;
        $lng2 = ($lng2 * pi() ) / 180;

        $calcLongitude = $lng2 - $lng1;
        $calcLatitude = $lat2 - $lat1;
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;

        return round($calculatedDistance/1000,2);
    }

    /*
    *会展展位：酒店
    */
    public function showHotel()
    {
        $type = input('type')?input('type'):1;//酒店、展馆
        $lat  = input('lat');//纬度
        $lng  = input('lng');//经度
        if(empty($lat) || empty($lng) || empty($type)){
            return json('缺少参数');
        }
        if(($type !=1 && $type !=2)|| !is_numeric($lat) || !is_numeric($lng)){
            return json('非法参数');
        }
        //酒店信息
        $list = model('ExhibitionHotel')->showExhibitionHotel($type);

        $hotelData = array();
        foreach ($list as $va) {
            //计算距离
            $distance = self::getDistance($lat,$lng,$va['lat'],$va['lng']);
            if($va['hotel_num']==1){
                $hotelData[$va['bgimg_id']]['id'] = $va['id'];
                $hotelData[$va['bgimg_id']]['isSinglePic'] = true;
                $hotelData[$va['bgimg_id']]['backImageURL'] = $va['back_image'];
                $hotelData[$va['bgimg_id']]['imageURL'] = $va['logo'];
                $hotelData[$va['bgimg_id']]['introduce'] = $va['introduce'];
                $hotelData[$va['bgimg_id']]['distance'] = $distance;//酒店距当前定位的距离
                //酒店标签
                // $hotelData[$va['bgimg_id']]['tag'] = $va["primary_label"].','.$va["two_label"].','.$va["three_label"];
                $hotelData[$va['bgimg_id']]['title'] = $va['name'];
                $hotelData[$va['bgimg_id']]['location'] = $va['address'];
                $hotelData[$va['bgimg_id']]['latitude'] = $va['lat'];
                $hotelData[$va['bgimg_id']]['longitude'] = $va['lng'];
            }else{
                $hotelData[$va['bgimg_id']]['isSinglePic'] = false;
                $hotelData[$va['bgimg_id']]['backImageURL'] = $va['back_image'];
                //酒店标签
                // $hotelData[$va['bgimg_id']]['content']['tag'] = $va["primary_label"].','.$va["two_label"].','.$va["three_label"];
                $hotelData[$va['bgimg_id']]['contents'][$va['id']]['id'] = $va['id'];
                $hotelData[$va['bgimg_id']]['contents'][$va['id']]['imageURL'] = $va['logo'];
                $hotelData[$va['bgimg_id']]['contents'][$va['id']]['title'] = $va['name'];
                $hotelData[$va['bgimg_id']]['contents'][$va['id']]['location'] = $va['address'];
                $hotelData[$va['bgimg_id']]['contents'][$va['id']]['latitude'] = $va['lat'];
                $hotelData[$va['bgimg_id']]['contents'][$va['id']]['longitude'] = $va['lng'];
            }
        }
        $arr = [];
        foreach ($hotelData as $value) {
            $arr[] = $value;
        }
        return json($arr,200);
    }

    /*
    *会展展位：展馆
    */
    public function showExhibition()
    {
        $data = model('ExhibitionHotel')->showExhibitionHotel(2);
        if($data){
            return json($data[0],200);
        }else{
            return json('数据获取失败');
        }
    }

    /*
    *酒店展馆详情页面
    */
    public function exhibitionHotelInfo()
    {
        $hotelid = input('id');
        $lat = input('lat');//纬度
        $lng = input('lng');//经度
        if(is_numeric($hotelid) && $lat && $lng){
            $info = model('ExhibitionHotel')->getInfo($hotelid);
            $info = $info[0];
            $info['boothnum'] = model('Booth')->getBoothNum($hotelid);
            $info['distance'] = self::getDistance($lat,$lng,$info['lat'],$info['lng']);
            return json($info);
        }else{
            return json('非法参数');
        }
    }

    //查询楼层或者几号馆
    public function  getFloor()
    {
        $type = input('cate');
        if(is_numeric($type)){
            $floors = model('Floor')->getFloors($type);
        }else{
            return json(['非法参数']);
        }
        if($floors){
            $arr = [];
            foreach ($floors as $key => $floor){
                if($key === 0){
                    $floor['didSelected'] = true;
                }else{
                    $floor['didSelected'] = false;
                }
                $arr[] = $floor;
            }
        }
        return json($arr,200);
    }

    //查询酒店或展馆展位列表
    public function exhibitionHotelBoothList()
    {
        $hotelid  = input('id');
        $floor = input('floor')?input('floor'):1;

        if(is_numeric($hotelid) && is_numeric($floor)){
            $page = input('page')?input('page'):1;
            //获取总条数
//            $count = model('Booth')->getBoothNum($hotelid, $floor);
//
//            $pages = ceil($count/PAGESIZE);
            //固定页码在有效范围内
            if($page<1){
                $page = 1;
            }
//            if($page>$pages){
//                $page = $pages;
//            }

            $lists = model('Booth')->getBoothList($hotelid, $page, $floor);
            if($lists){
                $arr = array();
                foreach ($lists as $list) {
                    //根据type匹配几层楼还是几号馆
                    if($list['type'] == 1){
                        $list['exhibition_code'] = $list['detail'].'-'.$list['exhibition_code'].'号';
                    }else{
                        $list['exhibition_code'] = $list['detail'].'-'.$list['exhibition_code'].'号';
                    }
                    unset($list['location']);
                    $label = array_merge(explode(',', $list['primary_label']),explode(',', $list['two_label']),explode(',', $list['three_label']));
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
            return json('数据获取失败');
        }
    }
}