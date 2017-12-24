<?php

namespace app\v2\model;

use think\Model;

class UserV2 extends Model
{
    protected $type = [
        'create_time' => 'datetime',
    ];

    protected $field = [
        'id',
        'js_uid',
        'nick_name',
        'header',
        'name',
        'tel',
        'openid',
        'create_time',
        'status',
        'identity',
        'tags',
        'customer_number',
        'qrcode',
        'validated',
        'company_info',
        'card_num',
        'person_card_image_url',
        'company_licence_url',
    ];

    protected $identitys = [
        1 => '参展',
        2 => '办展',
    ];

    /**
     * 筛选
     */
    protected function scopeFilter($query, $request)
    {
        
    }

    /**
     * 展位的酒店/展馆
     */
    public function booth()
    {
        return $this->hasMany('BoothV2', 'tel', 'tel');
    }

    /**
     * 标签的修改器
     */
    protected function setTagsAttr($value)
    {
        return json_encode(array_filter((array)$value), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 标签的获取器
     */
    public function getTagsAttr($value)
    {
        return json_decode($value);
    }
}
