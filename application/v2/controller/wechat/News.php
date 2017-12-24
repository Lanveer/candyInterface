<?php

namespace app\v2\controller\wechat;

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
    public function index()
    {
        $page = $this->request->param('page/d', 1);
        $limit = $this->request->param('limit/d', 10);

        // 限制limit最大为100
        $limit = $limit < 100 ? $limit : 100;
        $list = NewsV2::scope('filter', $this->request)
                ->field('id,name,category,type,tab,editor,create_time,is_top,status,covers')
                ->limit($limit)
                ->page($page);

        // 按照置顶方式返回数据
        $is_top = $this->request->param('is_top/d', 0);
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
        $total = NewsV2::scope('filter', $this->request)->count();
        $totalPages = ceil($total / $limit);

        return $this->ajaxResponse([
            'content' => $list,
            'totalElements' => $total,
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ]);
    }

    /**
     * 显示指定的资源
     *
     * @return \think\Response
     */
    public function read()
    {
        $id = $this->request->param('id/d');
        if (empty($id)) {
            return $this->ajaxResponse([], '参数不完整', 400);
        }

        $data = NewsV2::where(['id' => $id])
                ->find();
        if (empty($data)) {
            return $this->ajaxResponse([], '不存在此资源', 300);
        }

        return $this->ajaxResponse($data);
    }
}
