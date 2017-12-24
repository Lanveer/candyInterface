<?php

namespace app\v2\controller;

use think\Request;
use app\v2\model\BoothCollection as Collection;

/**
 * 展位收藏
 */
class BoothCollection extends Base
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
        $list = Collection::scope('filter', $request)
                ->with(['booth' => ['exhibition']])
                ->limit($limit)
                ->order('id desc')
                ->page($page);

        $list = $list->select();
        if (empty($list)) {
            return $this->ajaxResponse([], '列表数据为空', 300);
        }

        // 统计总条数
        $total = Collection::scope('filter', $request)->count();
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

        $result = Collection::create($data);
        if (empty($result)) {
            return $this->ajaxResponse([], '创建失败', 500);
        }

        return $this->ajaxResponse($result);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete(Request $request, $id)
    {
        $data = $request->param();
        $result = Collection::where(['user_id' => $data['user_id'], 'booth_id' => $data['booth_id']])->delete();
        if (!$result) {
            return $this->ajaxResponse($result, '删除失败', 500);
        }

        return $this->ajaxResponse($result);
    }
}
