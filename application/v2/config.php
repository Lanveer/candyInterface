<?php
// 相关配置

return [
    'v2' => [
        // 微信开放平台
        'appid' => 'wxdeb2af8946db58ef',
        'secret' => 'e7897880446c1b712011f06f5570e1a9',

        //////////////////
        ///酒商网相关接口///
        /////////////////

        // 糖酒会Auth
        'username' => 'th123',
        'password' => '111111',
        // 获取Token
        'jsw_token' => 'https://auth.jiushang.cn/oauth/token',
        // 用户信息
        'jsw_me' => 'https://auth.jiushang.cn/me',
        // 注册
        'jsw_reg' => 'http://api.jiushang.cn/api/register',
        // 收集经纬度
        'jsw_collect' => '',
        // 注册成功收集经纬度
        'jsw_collect_reg' => 'http://120.27.197.23:37777/api/user-location',
        // 产品详情接口
        'jsw_product_api' => 'http://jswapi.jiushang.cn/public/lib/info',
        // 产品详情ID
        'jsw_product_api_id' => 'http://120.27.197.23:37777/api/goods/goods',
        // 产品发布接口
        'jsw_product_add' => 'http://adshop.jiushang.cn/api/goods/goods',
        // 产品搜索
        'jsw_product_search' => 'http://jswapi.jiushang.cn/public/local/libgoods',
        // 产品品牌搜索接口
        'jsw_product_brand' => 'http://adshop.jiushang.cn/api/goods/brandList',
        // 通过企业名称查询详情
        'jsw_company_info_detail_api' => 'http://120.27.197.23:37777/api/company/detail',
        // 企业入驻接口
        'jsw_company_enter' => 'http://120.27.197.23:37777/api/company/join',

        //////////////////
        ///阿里云OSS配置///
        ////////////////

        // 如不配置，文件存在本地服务器
        'accessKeyId' => '',
        'accessKeySecret' => '',
        'endpoint' => '',
        'bucket' => '',
        'host' => 'http://jswpic.oss-cn-hangzhou.aliyuncs.com',

        // 微信用户token盐值，上线后不可更改！！
        'salt' => 'DelnKE*&H())M<Indwp>P)(^^%$DFFeqwGH',
        // 微信用户token过期时间
        'token_time_out' => 2592000,
    ],

    // 默认输出类型
    'default_return_type'    => 'json',
];
