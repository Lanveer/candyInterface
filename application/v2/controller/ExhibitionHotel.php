<?php

namespace app\v2\controller;

use think\Db;
use think\Request;
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
    public function index(Request $request)
    {
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
            $list = $list->withCount('booth');
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
     * Excel资源导入
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function putIn(Request $request)
    {
        $file = $request->file('excel');
        if(empty($file)) {
            return $this->ajaxResponse([], '文件不能为空', 400);
        }

        // 没啥用，直接丢缓存目录
        $info = $file->rule('md5')->move(RUNTIME_PATH . 'excel');
        if (empty($info)) {
            return $this->ajaxResponse(['error' => $file->getError()], '文件上传失败', 500); 
        }

        $pathName = $info->getPathName();

        // 定义一个title与Excel文件的数据字典对应
        $title = [
            'name',
            'type',
            'floors',
            'floors_alias',
            'recommend',
            'tel',
            'introduce',
            'primary_label',
            'two_label',
            'three_label',
            'address',
            'logo',
            'banner',
            'lat',
            'lng',
        ];

        // 通过Excel文件获取数据
        $data = Excel::getDataByExcelFile($pathName, $title);
        if (empty($data)) {
            return $this->ajaxResponse([], '提取的数据为空', 400);
        }

        // 启动事务
        Db::startTrans();
        try{
            // 使用索引是为了节约内存空间，防止导入大数据时崩溃
            for ($i=0; $i < count($data); $i++) {
                // 这里只是处理一下数据，分隔逗号
                $data[$i]['primary_label'] = explode(',', $data[$i]['primary_label']);
                $data[$i]['two_label'] = explode(',', $data[$i]['two_label']);
                $data[$i]['three_label'] = explode(',', $data[$i]['three_label']);
                $data[$i]['floors'] = explode(',', $data[$i]['floors']);
                $data[$i]['floors_alias'] = explode(',', $data[$i]['floors_alias']);

                // 校验分馆号与别名
                if (!empty($data[$i]['floors']) && !empty($data[$i]['floors_alias'])) {
                    if (count($data[$i]['floors']) != count($data[$i]['floors_alias'])) {
                        throw new \Exception('分馆与分馆别名不对应');
                    }
                }
                ExhibitionHotelV2::create($data[$i]);
            }

            // 不要忘了提交
            Db::commit();    
        } catch (\Exception $e) {
            Db::rollback();

            return $this->ajaxResponse(['error' => $e->getMessage(), 'error_data' => $data[$i]], '在导入数据时出现错误', 500);
        }

        return $this->ajaxResponse(['count' => count($data)], '导入成功');
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = $request->param();
        if (empty($data)) {
            return $this->ajaxResponse([], '缺少参数', 400);
        }

        // 判断类型
        if (!empty($data['type']) && !in_array($data['type'], array_keys(ExhibitionHotelV2::$types))) {
            return $this->ajaxResponse([], '类型错误', 400);
        }

        if (!empty($data['floors'])) {
            $data['floors'] = explode(',', $data['floors']);
        }
        if (!empty($data['floors_alias'])) {
            $data['floors_alias'] = explode(',', $data['floors_alias']);
        }

        if (!empty($data['floors']) && !empty($data['floors_alias'])) {
            if (count($data['floors']) != count($data['floors_alias'])) {
                return $this->ajaxResponse([], '创建失败：分馆与分馆别名不对应', 400);
            }
        }

        $result = ExhibitionHotelV2::create($data);
        if (empty($result)) {
            return $this->ajaxResponse([], '创建失败', 500);
        }

        return $this->ajaxResponse($result);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read(Request $request, $id)
    {
        $data = ExhibitionHotelV2::where(['id' => $id]);

        $booth_count = $request->param('booth_count/d', 0);
        if ($booth_count != 0) {
            $data = $data->withCount('booth');
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
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->param();
        if (empty($data)) {
            return $this->ajaxResponse([], '缺少参数', 400);
        }

        // 判断类型
        if (!empty($data['type']) && !in_array($data['type'], array_keys(ExhibitionHotelV2::$types))) {
            return $this->ajaxResponse([], '类型错误', 400);
        }

        if (!empty($data['floors'])) {
            $data['floors'] = explode(',', $data['floors']);
        }
        if (!empty($data['floors_alias'])) {
            $data['floors_alias'] = explode(',', $data['floors_alias']);
        }

        if (!empty($data['floors']) && !empty($data['floors_alias'])) {
            if (count($data['floors']) != count($data['floors_alias'])) {
                return $this->ajaxResponse([], '创建失败：分馆与分馆别名不对应', 400);
            }
        }

        $result = ExhibitionHotelV2::update($data, ['id' => $id]);
        if (empty($result)) {
            return $this->ajaxResponse([], '修改失败', 500);
        }

        return $this->ajaxResponse($result);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $result = ExhibitionHotelV2::destroy($id);
        if (!$result) {
            return $this->ajaxResponse($result, '删除失败', 500);
        }

        return $this->ajaxResponse($result);
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
