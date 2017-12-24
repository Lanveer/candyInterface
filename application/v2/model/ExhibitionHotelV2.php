<?php

namespace app\v2\model;

use think\Model;

class ExhibitionHotelV2 extends Model
{
    protected $type = [
        'create_time' => 'datetime',
    ];

    protected $field = [
        'id',
        'type',
        'recommend',
        'name',
        'tel',
        'introduce',
        'floors',
        'floors_alias',
        'primary_label',
        'two_label',
        'three_label',
        'address',
        'logo',
        'lat',
        'lng',
        'create_time',
        'status',
        'banner',
    ];

    /**
     * 类型
     */
    public static $types = [
        // 酒店
        1 => ['f' => '楼', 'n' => '酒店'],
        // 展馆
        2 => ['f' => '号馆', 'n' => '展馆'],
    ];

    /**
     * 筛选
     */
    protected function scopeFilter($query, $request)
    {
        // 筛选开始
        $type = $request->param('type/d', 0);
        $hidden = $request->param('hidden/d', 0);
        $recommend = $request->param('recommend/d', 0);

        $filter = [];

        if ($type !== 0) {
            $filter['type'] = $type;
        }
        if ($hidden !== 0) {
            $filter['status'] = 1;
        }
        if ($recommend !== 0) {
            $filter['recommend'] = 1;
        }
        // 筛选结束

        $query->where($filter);

        // 关键字搜索
        $keyword = $request->param('skw/s');
        if (!empty($keyword)) {
            $query->where('name', 'like', '%' . $keyword . '%');
        }
    }

    /**
     * 展位
     */
    public function booth()
    {
        return $this->hasMany('BoothV2', 'exhibition_hotel_id', 'id');
    }

    /**
     * 一级标签的修改器
     */
    protected function setPrimaryLabelAttr($value)
    {
        return json_encode(array_filter((array)$value), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 一级标签的获取器
     */
    public function getPrimaryLabelAttr($value)
    {
        return json_decode($value);
    }

    /**
     * 二级标签的修改器
     */
    protected function setTwoLabelAttr($value)
    {
        return json_encode(array_filter((array)$value), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 二级标签的获取器
     */
    public function getTwoLabelAttr($value)
    {
        return json_decode($value);
    }

    /**
     * 三级标签的修改器
     */
    protected function setThreeLabelAttr($value)
    {
        return json_encode(array_filter((array)$value), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 三级标签的获取器
     */
    public function getThreeLabelAttr($value)
    {
        return json_decode($value);
    }

    /**
     * 楼层的修改器
     */
    protected function setFloorsAttr($value)
    {
        return json_encode(array_filter((array)$value), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 楼层的获取器
     */
    public function getFloorsAttr($value)
    {
        return json_decode($value);
    }

    /**
     * 别名的修改器
     */
    protected function setFloorsAliasAttr($value)
    {
        return json_encode(array_filter((array)$value), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 别名的获取器
     */
    public function getFloorsAliasAttr($value)
    {
        return json_decode($value);
    }
}
