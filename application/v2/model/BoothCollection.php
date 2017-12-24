<?php

namespace app\v2\model;

use think\Model;

class BoothCollection extends Model
{
    protected $type = [
        'created_at' => 'datetime',
    ];

    protected $field = [
        'id',
        'user_id',
        'booth_id',
        'created_at',
    ];

    /**
     * 筛选
     */
    protected function scopeFilter($query, $request)
    {
        // 筛选开始
        $user_id = $request->param('user_id/d', 0);

        $filter = [];

        if ($user_id != 0) {
            $filter['user_id'] = $user_id;
        }
        // 筛选结束
        $query->where($filter);
    }

    /**
     * 展位
     */
    public function booth()
    {
        return $this->hasOne('BoothV2', 'id', 'booth_id');
    }
}
