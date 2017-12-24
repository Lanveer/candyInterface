<?php

namespace app\v2\controller;

use think\Request;
use app\v2\model\ActionCount as Count;

/**
 * 用户行为
 */
class ActionCount extends Base
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
        $list = Count::limit($limit)
                ->order('id desc')
                ->page($page);

        $list = $list->select();
        if (empty($list)) {
            return $this->ajaxResponse([], '列表数据为空', 300);
        }

        // 统计总条数
        $total = Count::count();
        $totalPages = ceil($total / $limit);

        return $this->ajaxResponse([
            'content' => $list,
            'totalElements' => $total,
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ]);
    }

    /**
     * 导出全部资源
     *
     * @return \think\Response
     */
    public function export(Request $request)
    {
        $keys = [
            'id' => '编号',
            'type' => '类型',
            'name' => '名称',
            'phone' => '联系方式',
            'user_type' => '用户类型',
            'created_at' => '创建时间',
        ];

        // 查询出用户数据
        $file = Excel::getExcelByData(Count::all(), $keys, '用户行为数据导出_' . date('Y_m_d', time()));

        if (empty($file)) {
            return $this->ajaxResponse([], '导出失败', 500);
        }

        return $this->ajaxResponse(['path' => $file, 'host' => $request->domain()]);
    }
}
