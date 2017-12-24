<?php

namespace app\index\validate;

use think\Validate;

class UserValidate extends Validate
{
    protected $rule = [
        'name|姓名'       =>  'require|max:50',
        'tel|联系电话'    =>  'require|number|length:11',
        'province|省'     =>  'require|max:50',
        'customer_number|客服号' =>  'max:8',
        'city|市'         =>  'require|max:50',
        'want_know|你想了解的展位' => 'require|max:83',
    ];
}