<?php

namespace app\index\validate;

use think\Validate;

class BoothValidate extends Validate
{
    protected $rule = [
        'company|企业'  =>  'require|max:50',
        'floor|楼层或者馆号'   =>  'require|number|max:3',
        'exhibition_code|展位号'  =>  'require|max:20',
        'contact|联系人'    =>  'require|max:25',
        'tel|电话'    =>  'require|max:25',
        'address|地址'        =>  'require|max:100',
        'start_time|开始时间'   =>  'max:30',
        'end_time|结束时间'  =>  'max:30',
        'product|介绍'  =>  'max:200',
        'img|图片' =>  'require|max:150',
        'primary_label|一级标签' => 'require|max:50',
        'two_label|二级标签' => 'max:100',
        'three_label|三级标签' => 'max:80',
        'exhibition_hotel_id|展馆酒店号' => 'require|number|max:11',
        'is_hot|是否热门' =>  'number|length:1'
    ];
}