<?php

namespace app\v2\controller\wechat;

use think\Controller;

/**
 * 基础控制器
 */
class Base extends Controller
{
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

    /**
     * 获取一个token
     *
     * @param  int  $uid
     * @return string $token
     */
    public function getToken($uid = 0) {
        $data = base64_encode(json_encode([
            'uid' => $uid,
            'exp' => time() + config('v2.token_time_out'),
        ]));

        return base64_encode($data . ':' . crypt($data, config('v2.salt')));
    }

    /**
     * 根据token获取uid
     *
     * @param  string  $token
     * @return int $uid
     */
    public function getUid($token = '') {
        $data = explode(':', base64_decode($token));
        if ( !isset($data[0]) || !isset($data[1]) || crypt($data[0], config('v2.salt')) != $data[1] ) {
            return false;
        }

        $data = json_decode(base64_decode($data[0]));
        if ($data->exp < time()) {
            return false;
        }
        return $data->uid;
    }
}
