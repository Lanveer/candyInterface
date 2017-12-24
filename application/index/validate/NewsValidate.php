<?php

namespace app\index\validate;

use think\Validate;

class NewsValidate extends Validate
{
    protected $rule = [
        'type|分类'             => 'require|number|max:2',
        'name|标题'             => 'require|max:150',
        'news_category_id|类型' => 'require|max:2',
        'tab|标签'              => 'require|max:50',
        'editor|作者'           => 'max:50',
        'cover|封面'            => 'max:150',
        'image1|图片1'          => 'max:150',
        'image2|图片2'          => 'max:150',
        'image3|图片3'          => 'max:150',
        'content1|内容1'        => 'require'
    ];
}