<?php

namespace app\v2\controller\wechat;

use think\Controller;

/**
 * 权限认证的控制器
 * 需要用户ID需要继承此类
 */
class Auth extends Base
{
    public $user_id = null;

    /**
     * 这里来做权限控制
     */
    public function _initialize()
    {
        $token = $this->request->param('token/s');
        if (empty($token)) {
            return json($this->ajaxResponse([], '丢失TOKEN', 400))->send();
        }

        $uid = $this->getUid($token);

        if (!$uid) {
            return json($this->ajaxResponse([], '此认证不合法或已失效', 400))->send;
        }

        $this->user_id = $uid;
    }
}
