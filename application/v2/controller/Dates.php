<?php

namespace app\v2\controller;

use think\Request;
use app\v2\model\Date;

class Dates extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $page = $request->param('page/d', 1);
        $limit = $request->param('limit/d', 50);

        $list = Date::limit($limit)
                ->page($page)
                ->select();

        if (empty($list)) {
            return $this->ajaxResponse([], '列表数据为空', 300);
        }

        return $this->ajaxResponse($list);
    }
}
