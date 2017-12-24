<?php

namespace app\v2\controller;

use think\Request;
use app\v2\model\UserV2;
use app\v2\model\BoothV2;
use app\v2\model\Message as Model;

/**
 * 用户消息
 */
class Message extends Base
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
        $list = Model::scope('filter', $request)
                ->limit($limit)
                ->order('id desc')
                ->page($page);

        $list = $list->select();
        if (empty($list)) {
            return $this->ajaxResponse([], '列表数据为空', 300);
        }

        // 统计总条数
        $total = Model::scope('filter', $request)->count();
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
        $message = $request->param('message/s');
        if (empty($message)) {
            return $this->ajaxResponse([], '缺少参数', 400);
        }

        $eh_id = $request->param('eh_id/d');
        $floor = $request->param('floor/d');

        $eh = BoothV2::where('exhibition_hotel_id', $eh_id);
        if (!empty($floor)) {
            $eh = $eh->where('floor', $floor);
        }
        // 先找出对应的手机号码
        $eh = $eh->field('tel')->select();
        if (empty($eh)) {
            return $this->ajaxResponse([], '此酒店下没有展位信息', 300);
        }

        $tels = [];
        foreach ($eh as $key => $value) {
            $tels[] = $value->tel;
        }

        // 再找出用户
        $u = UserV2::where('tel', 'IN', $tels)->field('id')->select();
        if (empty($u)) {
            return $this->ajaxResponse([], '没有找到对应用户', 300);
        }

        for ($i=0; $i < count($u); $i++) { 
            Model::create(['user_id' => $u[$i]->id, 'content' => $message]);
        }

        return $this->ajaxResponse(['count' => count($u)]);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $data = Model::where(['id' => $id])
                ->find();
        if (empty($data)) {
            return $this->ajaxResponse([], '不存在此资源', 300);
        }

        // 更新为阅读状态
        $data->is_read = 1;
        $data->save();

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

        $result = Model::update($data, ['id' => $id]);
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
        $result = Model::destroy($id);
        if (!$result) {
            return $this->ajaxResponse($result, '删除失败', 500);
        }

        return $this->ajaxResponse($result);
    }
}
