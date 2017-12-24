<?php

namespace app\v2\model;

use think\Model;

class ActionCount extends Model
{
    protected $type = [
        'created_at' => 'datetime',
    ];

    protected $field = [
        'id',
        'type',
        'name',
        'phone',
        'user_type',
        'created_at',
    ];
}
