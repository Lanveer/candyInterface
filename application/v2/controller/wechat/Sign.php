<?php

namespace app\v2\controller\wechat;

use think\Request;
use think\Exception;
use app\v2\model\UserV2;

/**
 * 用户
 */
class Sign extends Base
{
    /**
     * 保存新建的资源 - 注册
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function up(Request $request)
    {
        $data = array_filter($request->param());
        if (empty($data)) {
            return $this->ajaxResponse($request->param(), '参数不完整', 400);
        }

        $nick_name = $request->param('nick_name/s');
        $tel = $request->param('tel/s');
        $email = $request->param('email/s');
        $password = $request->param('password/s');
        $header = $request->param('header/s');

        $code = $request->param('code/s');
        if (empty($code)) {
            return $this->ajaxResponse($request->param(), '验证码不能为空', 400);
        }

        // 调用远程服务器接口
        $reqestData = [
            'username' => $tel,
            'phone' => $tel,
            'password' => $password,
            'email' => $email,
            'code' => $code,
        ];

        $resp = $this->ToJiuS($reqestData, config('v2.jsw_reg'));
        if (empty($resp) || !empty($resp->error)) {
            return $this->ajaxResponse($resp, '远程服务器出错', 500);
        }

        if ($resp->errCode != 0) {
            return $this->ajaxResponse([], $resp->msg, $resp->errCode);
        }
        // 调用结束

        // 获取openid
        $opencode = $request->param('opencode/s');
        $wechatret = $this->getOpenID($opencode);
        if (empty($wechatret) || empty($wechatret['openid'])) {
            return $this->ajaxResponse($wechatret, 'OPENID获取失败', 500);
        }
        // 结束

        $data['openid'] = $wechatret['openid'];
        $data['js_uid'] = $resp->content->uid;

        $result = UserV2::create($data);
        if (empty($result)) {
            return $this->ajaxResponse([], '创建失败', 500);
        }

        try {
            $this->_collect([
                'uid' => $resp->content->uid,
                'phone' => $tel,
                'longitude' => $request->param('longitude', 10.0),
                'latitude' => $request->param('latitude', 10.0),
                'from' => $request->param('qrcode', ''),
            ]);
        } catch (Exception $e) {

        }

        return $this->ajaxResponse($result);
    }

    private function _collect($data)
    {
        // 获取一个管理员token
        $token = Booth::getAdminToken();
        if (empty($token)) {
            return false;
        }
        // 发起请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, config('v2.jsw_collect_reg'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization:bearer ' . $token]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $info = json_decode(curl_exec($ch));
        curl_close($ch);

        if (empty($info)) {
            return false;
        }
        return true;
    }

    public function collect()
    {
        try {
            $data = $this->request->param();
            if (empty($data)) {
                return $this->ajaxResponse([], '参数不能为空', 400);
            }
            // 获取一个管理员token
            $token = Booth::getAdminToken();
            if (empty($token)) {
                return $this->ajaxResponse();
            }
            // 发起请求
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, config('v2.jsw_collect'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization:bearer ' . $token]);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $info = json_decode(curl_exec($ch));
            curl_close($ch);
        } catch (Exception $e) {

        }

        return $this->ajaxResponse();
    }

    /**
     * 登录
     *
     * @return \think\Response
     */
    public function in()
    {
        $opencode = $this->request->param('opencode/s');
        if (empty($opencode)) {
            return $this->ajaxResponse([], '参数不完整', 300);
        }

        // 验证openid
        $wechatret = $this->getOpenID($opencode);
        if (empty($wechatret) || empty($wechatret['openid'])) {
            return $this->ajaxResponse($wechatret, 'OPENID获取失败', 500);
        }

        $data = UserV2::where(['openid' => $wechatret['openid']])
                ->with(['booth' => ['exhibition']])
                ->find();

        if (empty($data)) {
            return $this->ajaxResponse([], '不存在此用户', 300);
        }

        $data['token'] = $this->getToken($data['id']);

        return $this->ajaxResponse($data);
    }

    /**
     * 使用手机号和密码进行登录
     */
    public function inCell()
    {
        $opencode = $this->request->param('opencode/s');
        $tel = $this->request->param('tel/s');
        $password = $this->request->param('password/s');

        if (empty($opencode) || empty($tel) || empty($password)) {
            return $this->ajaxResponse([], '参数不完整', 400);
        }

        // 调用远程接口
        // 1.获取token
        $access = self::getJSWToken($tel, $password);
        if (!isset($access['access_token'])) {
            return $this->ajaxResponse([], '账号或密码错误', 400);
        }

        // 2.获取用户基本信息
        $header = ['Authorization:bearer ' . $access['access_token']];
        $ret = $this->ToJiuS([], config('v2.jsw_me'), false, $header);
        if (empty($ret) || !isset($ret->content->uid)) {
            return $this->ajaxResponse([], '远程服务器出错', 500);
        }
        // 调用结束

        $user = UserV2::where(['js_uid' => $ret->content->uid])
                ->with(['booth' => ['exhibition']])
                ->find();

        // 如果为空，相当于注册
        if (empty($user)) {
            $temp = new UserV2();
            $temp->data([
                'nick_name' => $this->request->param('nick_name/s'),
                'header' => $this->request->param('header/s'),
                'js_uid' => $ret->content->uid,
                'tel' => empty($ret->content->member->phone)?$tel:$ret->content->member->phone,
            ]);
            // 先插入记录
            try {
                if (!$temp->save()) {
                    return $this->ajaxResponse([], '保存失败，请联系管理员', 505);
                }
            } catch (Exception $e) {
                return $this->ajaxResponse(['e' => $e->getMessage()], '写入异常，请联系管理员', 505);
            }
            // 再获取
            $user = UserV2::where(['id' => $temp->id])->with(['booth' => ['exhibition']])->find();
        }

        // 获取当前的openid
        $wechatret = $this->getOpenID($opencode);
        if (empty($wechatret) || empty($wechatret['openid'])) {
            return $this->ajaxResponse($wechatret, '登录成功，但OPENID获取失败', 500);
        }

        // 保存openid
        $user->openid = $wechatret['openid'];
        if (!$user->save()) {
            return $this->ajaxResponse($wechatret, '登录成功，但OPENID保存失败', 500);
        }

        $user['token'] = $this->getToken($user['id']);

        return $this->ajaxResponse($user);
    }

    /**
     * 调用远程接口
     */
    private function ToJiuS($data, $url, $isJSon = true, $header = ['Content-Type: application/json'])
    {
        if ($isJSon) {
            $data = json_encode($data);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $resp = curl_exec($ch);
        curl_close($ch);

        return json_decode($resp);
    }

    /**
     * 获取JSWtoken
     */
    public static function getJSWToken($username, $password)
    {
        $header = ['Authorization:Basic ' . base64_encode('pt-sales-system:123456')];
        $data   = [
            'grant_type' => 'password',
            'username' => $username,
            'password' => $password,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, config('v2.jsw_token'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);

        if ($result === FALSE) {
            return null;
        }
        curl_close($ch);
        return json_decode($result, true);
    }

    /**
     * 获取OpenID
     */
    private function getOpenID($code)
    {
        if (empty($code)) return null;

        $appid = config('v2.appid');
        $secret = config('v2.secret');

        $url = 'https://api.weixin.qq.com/sns/jscode2session?grant_type=authorization_code&appid=' . $appid . '&secret=' . $secret . '&js_code=' . $code;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);

        if (empty($result)) {
            return null;
        }

        return json_decode($result, true);
    }
}
