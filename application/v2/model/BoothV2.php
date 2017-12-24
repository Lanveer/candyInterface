<?php

namespace app\v2\model;

use think\Model;

class BoothV2 extends Model
{
    protected $type = [
        'create_time' => 'datetime',
    ];

    protected $field = [
        'id',
        'company',
        'introduce',
        'exhibition_hotel_id',
        'floor',
        'exhibition_code',
        'address',
        'contact',
        'tel',
        'start_time',
        'end_time',
        'img',
        'primary_label',
        'two_label',
        'three_label',
        'is_hot',
        'status',
        'create_time',
        'products',
    ];

    /**
     * 筛选
     */
    protected function scopeFilter($query, $request)
    {
        // 筛选开始
        $hot = $request->param('hot/d', 0);
        $floor = $request->param('floor/s', 0);
        $hidden = $request->param('hidden/d', -1);
        $ehId = $request->param('eh_id/d', 0);

        // var_dump($request->param());exit();

        $filter = [];

        if ($hot !== 0) {
            $filter['is_hot'] = 1;
        }
        if ($ehId !== 0) {
            $filter['exhibition_hotel_id'] = $ehId;
        }
        if ($floor !== 0) {
            $filter['floor'] = $floor;
        }
        if ($hidden !== -1) {
            $filter['status'] = $hidden;
        }

        // 筛选结束
        $query->where($filter);

        // 关键字搜索
        $keyword = $request->param('skw/s');
        if (!empty($keyword)) {
            $query->where('two_label|company|primary_label|three_label', 'like', '%' . $keyword . '%');
        }
    }

    /**
     * 展位的酒店/展馆
     */
    public function exhibition()
    {
        return $this->hasOne('ExhibitionHotelV2', 'id', 'exhibition_hotel_id')->field('id,type,name');
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
     * 产品的修改器
     */
    protected function setProductsAttr($value)
    {
        return json_encode(array_filter((array)$value), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 产品的获取器
     */
    public function getProductsAttr($value)
    {
        return json_decode($value);
    }
}
