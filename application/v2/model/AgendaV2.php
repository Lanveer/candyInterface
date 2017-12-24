<?php

namespace app\v2\model;

use think\Model;

class AgendaV2 extends Model
{
    protected $type = [
        'create_time' => 'datetime',
    ];

    protected $field = [
        'id',
        'page_id',
        'is_hot',
        'time',
        'theme',
        'guest',
        'cover',
        'auditoria',
        'address',
        'exhibition_hotel_id',
        'introduce',
        'create_time',
        'status',
    ];

    /**
     * 筛选
     */
    protected function scopeFilter($query, $request)
    {
        // 筛选开始
        $hot = $request->param('hot/d', 0);
        $pageId = $request->param('page_id/d', 0);
        $hidden = $request->param('hidden/d', 0);
        $ehId = $request->param('eh_id/d', 0);

        $filter = [];

        if ($hot != 0) {
            $filter['is_hot'] = 1;
        }
        if ($pageId != 0 && $hot != 1) {
            $filter['page_id'] = $pageId;
        }
        if ($ehId != 0) {
            $filter['exhibition_hotel_id'] = $ehId;
        }
        if ($hidden != 0) {
            $filter['status'] = 1;
        }
        // 筛选结束

        $query->where($filter);

        // 关键字搜索
        $keyword = $request->param('skw/s');
        if (!empty($keyword)) {
            $query->where('theme', 'like', '%' . $keyword . '%');
        }
    }

    /**
     * 日程的酒店/展馆
     */
    public function exhibition()
    {
        return $this->hasOne('ExhibitionHotelV2', 'id', 'exhibition_hotel_id')->field('id,type,name');
    }

    /**
     * 日程的时间
     */
    public function date()
    {
        return $this->hasOne('Date', 'pageId', 'page_id');
    }
}
