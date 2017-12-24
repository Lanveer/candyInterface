<?php

namespace app\v2\controller\wechat;

use app\v2\model\BoothCollection as Collection;

/**
 * 展位收藏
 */
class BoothCollection extends Auth
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $page = $this->request->param('page/d', 1);
        $limit = $this->request->param('limit/d', 10);

        // 限制limit最大为100
        $limit = $limit < 100 ? $limit : 100;
        $list = Collection::where(['user_id' => $this->user_id])
                ->with(['booth' => ['exhibition']])
                ->limit($limit)
                ->order('id desc')
                ->page($page);

        $list = $list->select();
        if (empty($list)) {
            return $this->ajaxResponse([], '列表数据为空', 300);
        }

        // 统计总条数
        $total = Collection::where(['user_id' => $this->user_id])->count();
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
     * @return \think\Response
     */
    public function save()
    {
        $data = $this->request->param();
        if (empty($data)) {
            return $this->ajaxResponse([], '缺少参数', 400);
        }

        $data['user_id'] = $this->user_id;
        $result = Collection::create($data);
        if (empty($result)) {
            return $this->ajaxResponse([], '创建失败', 500);
        }

        return $this->ajaxResponse($result);
    }

    /**
     * 删除指定资源
     *
     * @return \think\Response
     */
    public function delete()
    {
        $booth_id = $this->request->param('booth_id/d');
        $result = Collection::where(['user_id' => $this->user_id, 'booth_id' => $booth_id])->delete();
        if (!$result) {
            return $this->ajaxResponse($result, '删除失败', 500);
        }

        return $this->ajaxResponse($result);
    }
}
