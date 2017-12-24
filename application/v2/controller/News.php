<?php

namespace app\v2\controller;

use think\Request;
use app\v2\model\NewsV2;

/**
 * 新闻资讯
 */
class News extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $page = $request->param('page/d', 1);
        $limit = $request->param('limit/d', 10);

        // 限制limit最大为100
        $limit = $limit < 100 ? $limit : 100;
        $list = NewsV2::scope('filter', $request)
                ->field('id,name,category,type,tab,editor,create_time,is_top,status,covers')
                ->limit($limit)
                ->page($page);

        // 按照置顶方式返回数据
        $is_top = $request->param('is_top/d', 0);
        if ($is_top != 0) {
            $list = $list->order('is_top desc,id desc');
        } else {
            $list = $list->order('id desc');
        }

        $list = $list->select();

        if (empty($list)) {
            return $this->ajaxResponse([], '列表数据为空', 300);
        }

        // 统计总条数
        $total = NewsV2::scope('filter', $request)->count();
        $totalPages = ceil($total / $limit);

        return $this->ajaxResponse([
            'content' => $list,
            'totalElements' => $total,
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ]);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = $request->param();
        if (empty($data)) {
            return $this->ajaxResponse([], '缺少参数', 400);
        }

        // 判断类型
        if (!in_array($data['type'], array_keys(NewsV2::$types))) {
            return $this->ajaxResponse([], '类型错误', 400);
        }

        $result = NewsV2::create($data);
        if (empty($result)) {
            return $this->ajaxResponse([], '创建失败', 500);
        }

        return $this->ajaxResponse($result);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $data = NewsV2::where(['id' => $id])
                ->find();
        if (empty($data)) {
            return $this->ajaxResponse([], '不存在此资源', 300);
        }

        return $this->ajaxResponse($data);
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->param();
        if (empty($data)) {
            return $this->ajaxResponse([], '缺少参数', 400);
        }

        // 判断类型
        if ( !empty($data['type']) && !in_array($data['type'], array_keys(NewsV2::$types))) {
            return $this->ajaxResponse([], '类型错误', 400);
        }

        $result = NewsV2::update($data, ['id' => $id]);
        if (empty($result)) {
            return $this->ajaxResponse([], '修改失败', 500);
        }

        return $this->ajaxResponse($result);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $result = NewsV2::destroy($id);
        if (!$result) {
            return $this->ajaxResponse($result, '删除失败', 500);
        }

        return $this->ajaxResponse($result);
    }
}
