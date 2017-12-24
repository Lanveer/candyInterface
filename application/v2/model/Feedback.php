<?php

namespace app\v2\model;

use think\Model;

class Feedback extends Model
{
    protected $type = [
        'create_time' => 'datetime',
    ];

    protected $field = [
        'id',
        'user_id',
        'user_name',
        'tel',
        'create_time',
        'content',
        'status',
    ];

    /**
     * 筛选
     */
    protected function scopeFilter($query, $request)
    {
        
    }
}
