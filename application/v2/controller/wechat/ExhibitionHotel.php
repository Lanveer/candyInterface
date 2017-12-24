<?php

namespace app\v2\controller\wechat;

use think\Db;
use app\v2\model\ExhibitionHotelV2;

/**
 * 酒店展馆
 */
class ExhibitionHotel extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $request = $this->request;

        $page = $request->param('page/d', 1);

        $list = ExhibitionHotelV2::scope('filter', $request)
                ->order('id desc')
                ->page($page);

        // 是否限制数量
        $is_limit = $request->param('is_limit/d', 1);
        $limit = $request->param('limit/d', 10);
        if ($is_limit) {
            $list = $list->limit($limit);
        }

        // 是否限制返回列
        $field = $request->param('field/s');
        if (!empty($field)) {
            $field = explode(',', $field);
            $field[] = 'id';
            $list = $list->field($field);
        }

        // 是否返回展位数量
        $booth_count = $request->param('booth_count/d', 0);
        if ($booth_count != 0) {
            $list = $list->withCount(['booth' => function($q) {
                $q->where(['status' => 1]);
            }]);
        }
    
        // 查询数据
        $list = $list->select();
        if (empty($list)) {
            return $this->ajaxResponse([], '列表数据为空', 300);
        }

        // 是否返回距离
        $is_back_distance = $request->param('back_distance/d', 0);
        if ($is_back_distance != 0) {
            $lat = $request->param('lat/f');
            $lng = $request->param('lng/f');

            if (empty($lat) || empty($lng)) {
                return $this->ajaxResponse([], '你期望返回距离，但是你没有设置你的经纬度。', 400);
            }

            // 计算距离
            for ($i = 0; $i < count($list); $i++) { 
                $list[$i]['distance'] = $this->getDistance($list[$i]['lat'], $list[$i]['lng'], $lat, $lng);
            }
        }

        // 统计总条数
        $total = ExhibitionHotelV2::scope('filter', $request)->count();
        $totalPages = ceil($total / $limit);

        return $this->ajaxResponse([
            'content' => $list,
            'totalElements' => $total,
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ]);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read()
    {
        $request = $this->request;
        $id = $request->param('id/d');

        $data = ExhibitionHotelV2::where(['id' => $id]);

        $booth_count = $request->param('booth_count/d', 0);
        if ($booth_count != 0) {
            $data = $data->withCount(['booth' => function($q) {
                $q->where(['status' => 1]);
            }]);
        }

        $data = $data->find();
        if (empty($data)) {
            return $this->ajaxResponse([], '不存在此资源', 300);
        }

        // 是否返回距离
        $is_back_distance = $request->param('back_distance/d', 0);
        if ($is_back_distance != 0) {
            $lat = $request->param('lat/f');
            $lng = $request->param('lng/f');

            if (empty($lat) || empty($lng)) {
                return $this->ajaxResponse([], '你期望返回距离，但是你没有设置你的经纬度。', 400);
            }
            $data['distance'] = $this->getDistance($lat, $lng, $data['lat'], $data['lng']);
        }

        // 是否格式化楼层或展馆
        $format_floors = $request->param('format_floors/d', 0);
        if ($format_floors != 0) {
            $format = [];
            $type_text = ExhibitionHotelV2::$types[$data['type']]['f'];
            for ($i = 0; $i < count($data['floors']); $i++) { 
                $format[] = [
                    'detail' => $data['floors'][$i] . $type_text,
                    'didSelected' => false,
                    'id' => $data['floors'][$i],
                ];
            }
            if (!empty($format)) {
                $format[0]['didSelected'] = true;
            }
            $data['format_floors'] = $format;
        }

        return $this->ajaxResponse($data);
    }

    /**
     * 根据经纬度计算距离
     */
    public static function getDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6367000;

        $lat1 = ($lat1 * pi()) / 180;
        $lng1 = ($lng1 * pi()) / 180;

        $lat2 = ($lat2 * pi()) / 180;
        $lng2 = ($lng2 * pi()) / 180;

        $calcLongitude = $lng2 - $lng1;
        $calcLatitude = $lat2 - $lat1;
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;

        return round($calculatedDistance/1000,2);
    }
}
