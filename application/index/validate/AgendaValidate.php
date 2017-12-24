<?php

namespace app\index\validate;

use think\Validate;

class AgendaValidate extends Validate
{
    protected $rule = [
        'date_id|日程时间'  =>  'require|number|max:2',
        'time|会议时间'     =>  'require|max:40',
        'theme|会议主题'    =>  'require|max:50',
        'guest|嘉宾'        =>  'require|max:100',
        'img|封面图片'      =>  'max:150',
        'infoimg|详情图片'  =>  'max:150',
        'auditoria|会议厅'  =>  'max:50',
        'address|会议地址'  =>  'require|max:200',
        'exhibition_hotel_id|展馆酒店id' => 'require|number|max:11',
        'introduce|介绍'    => 'require',
    ];
}