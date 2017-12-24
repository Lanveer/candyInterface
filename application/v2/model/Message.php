<?php

namespace app\v2\model;

use think\Model;

class Message extends Model
{
    protected $type = [
        'created_at' => 'datetime',
    ];

    protected $field = [
        'id',
        'user_id',
        'label',
        'content',
        'is_read',
        'created_at',
    ];

    /**
     * 筛选
     */
    protected function scopeFilter($query, $request)
    {
        // 筛选开始
        $label = $request->param('label/s');
        $read = $request->param('read/d', -1);
        $filter = [];

        if (!empty($label)) {
            $filter['label'] = $label;
        }
        if ($read != -1) {
            $filter['is_read'] = $read;
        }
        // 筛选结束

        $query->where($filter);

        // 获取某个用户的
        $user_id = $request->param('user_id/d', 0);
        if ($user_id != 0) {
            $query->where(function($q)use($user_id) {
                $q->where('user_id', $user_id)->whereor('user_id', 0);
            });
        }
    }
}
