<?php

namespace app\v2\controller\wechat;

use app\v2\model\Feedback as Model;

/**
 * 用户反馈
 */
class Feedback extends Base
{
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

        $result = Model::create($data);
        if (empty($result)) {
            return $this->ajaxResponse([], '创建失败', 500);
        }

        return $this->ajaxResponse($result);
    }
}
