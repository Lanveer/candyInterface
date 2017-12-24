<?php

namespace app\v2\controller\wechat;

use app\v2\model\UserV2;
use app\v2\model\BoothV2;
use app\v2\model\Message as Model;

/**
 * 用户消息
 */
class Message extends Auth
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
        $list = Model::where(['user_id' => $this->user_id])
                ->limit($limit)
                ->order('id desc')
                ->page($page);

        $list = $list->select();
        if (empty($list)) {
            return $this->ajaxResponse([], '列表数据为空', 300);
        }

        // 统计总条数
        $total = Model::where(['user_id' => $this->user_id])->count();
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

        $data = Model::where(['id' => $id, 'user_id' => $this->user_id])
                ->find();
        if (empty($data)) {
            return $this->ajaxResponse([], '不存在此资源', 300);
        }

        // 更新为阅读状态
        $data->is_read = 1;
        $data->save();

        return $this->ajaxResponse($data);
    }
}
