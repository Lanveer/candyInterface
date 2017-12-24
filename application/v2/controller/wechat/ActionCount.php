<?php

namespace app\v2\controller\wechat;

use app\v2\model\ActionCount as Count;

/**
 * 用户行为
 */
class ActionCount extends Auth
{
    /**
     * 保存新建的资源
     *
     * @return \think\Response
     */
    public function save()
    {
        $request = $this->request;

        $data = $request->param();
        if (empty($data)) {
            return $this->ajaxResponse([], '缺少参数', 400);
        }

        $result = Count::create($data);
        if (empty($result)) {
            return $this->ajaxResponse([], '创建失败', 500);
        }

        return $this->ajaxResponse($result);
    }
}
