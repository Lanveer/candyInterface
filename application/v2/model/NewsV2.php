<?php

namespace app\v2\model;

use think\Model;

class NewsV2 extends Model
{
    protected $type = [
        'create_time' => 'datetime',
    ];

    protected $field = [
        'id',
        'name',
        'category',
        'type',
        'tab',
        'editor',
        'create_time',
        'content',
        'is_top',
        'status',
        'covers',
    ];

    /**
     * 新闻类型
     */
    public static $types = [
        // 没有封面
        1 => '文字',
        // 有一张封面
        2 => '单图',
        // 有三张封面
        3 => '多图',
        // 有一张大的封面
        4 => '大图',
        // 有一个封面，封面地址是视频的地址
        5 => '视频',
        // 有一张封面
        6 => '轮播图',
    ];

    /**
     * 新闻类别
     */
    public static $categorys = [
        // 展会资讯
        1 => '展会资讯',
        // 展会指南
        2 => '展会指南',
        // 首页推荐
        3 => '首页推荐',
    ];

    /**
     * 筛选
     */
    protected function scopeFilter($query, $request)
    {
        // 筛选开始
        $category = $request->param('category/d', 0);
        $type = $request->param('type/d', 0);
        $hidden = $request->param('hidden/d', 0);

        $filter = [];

        // 为0就不获取为3的
        if ($category != 0) {
            $filter['category'] = $category;
        } else {
            $query->where('category', '<>', 3);
        }
        if ($type != 0 && $category != 3) {
            $filter['type'] = $type;
        }
        if ($hidden != 0) {
            $filter['status'] = 1;
        }
        // 筛选结束

        $query->where($filter);

        // 关键字搜索
        $keyword = $request->param('skw/s');
        if (!empty($keyword)) {
            $query->where('name', 'like', '%' . $keyword . '%');
        }
    }

    /**
     * 封面的修改器
     */
    protected function setCoversAttr($value)
    {
        return json_encode(array_filter((array)$value), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 封面的获取器
     */
    public function getCoversAttr($value)
    {
        return json_decode($value);
    }
}
