<?php

namespace app\v2\controller;

use think\Controller;

/**
 * 基础控制器
 */
class Base extends Controller
{
    /**
     * 初始化接口 - 这里来做权限控制
     */
    public function _initialize()
    {
        
    }

    /**
     * 接口响应函数
     *
     * @return \think\Response
     */
    public function ajaxResponse($data = [], $msg = 'ok', $code = 0)
    {
        // 允许跨域访问
        header('Access-Control-Allow-Origin: *');

        $ret = ['code' => $code, 'msg' => $msg, 'data' => $data];

        // return json($ret)->send();
        return $ret;
    }
}
