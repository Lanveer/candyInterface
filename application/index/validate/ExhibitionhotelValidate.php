<?php

namespace app\index\validate;

use think\Validate;

class ExhibitionhotelValidate extends Validate
{
    protected $rule = [
        'type|类型'  =>  'require|number|max:1',
        'name|名称'   =>  'require|max:50',
        'introduce|介绍'  =>  'require|max:600',
        'tel|电话'    =>  'max:50',
        'address|地址'    =>  'require|max:100',
        'logo|图片'        =>  'require|max:150',
        'lng|经度'   =>  'max:30',
        'lat|纬度'  =>  'max:30',
        'primary_label|一级标签'  =>  'max:40',
        'two_label|二级标签' =>  'max:40',
        'three_label|三级标签' => 'max:40',
        'bgimg_id|组' => 'require|number|max:11'
    ];

    protected $scene = [
        'type1'  =>  ['type','name','introduce','tel','address','logo','lng','lat','primary_label','two_label','three_label','bgimg_id'],
        'type2'  =>  ['type','name','introduce','tel','address','logo','lng','lat','bgimg_id'],
    ];
}