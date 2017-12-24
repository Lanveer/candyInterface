<?php

namespace app\v2\controller\wechat;

use app\v2\model\Date;
use app\v2\model\AgendaV2;

/**
 * 日程
 */
class Agenda extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $request = $this->request;

        $page = $request->param('page/d', 1);
        $limit = $request->param('limit/d', 10);

        // 限制limit最大为100
        $limit = $limit < 100 ? $limit : 100;
        $list = AgendaV2::scope('filter', $request)
                ->field('id,page_id,is_hot,time,theme,guest,cover,auditoria,address,exhibition_hotel_id')
                ->with(['exhibition', 'date'])
                ->limit($limit)
                ->order('id desc')
                ->page($page);

        $list = $list->select();
        if (empty($list)) {
            return $this->ajaxResponse([], '列表数据为空', 300);
        }

        // 统计总条数
        $total = AgendaV2::scope('filter', $request)->count();
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
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $id = $this->request->param('id/d');

        $data = AgendaV2::where(['id' => $id])
                ->find();
        if (empty($data)) {
            return $this->ajaxResponse([], '不存在此资源', 300);
        }

        return $this->ajaxResponse($data);
    }

    /**
     * 显示日程标题
     *
     * @return \think\Response
     */
    public function getDate()
    {
        $request = $this->request;

        $page = $request->param('page/d', 1);
        $limit = $request->param('limit/d', 50);

        $list = Date::limit($limit)
                ->page($page)
                ->select();

        if (empty($list)) {
            return $this->ajaxResponse([], '列表数据为空', 300);
        }

        for ($i=0; $i < count($list); $i++) { 
            $list[$i]['didSelected'] = false;
        }
        if (isset($list[0])) {
            $list[0]['didSelected'] = true;
        }

        return $this->ajaxResponse($list);
    }
}
