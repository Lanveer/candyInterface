<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Loader;
use think\Request;

class Userapi extends Controller{
    //我的信息
    public function userInfo()
    {
        $userid = input('uid');
        if(is_numeric($userid)){
            $user = Db::table('pre_min_user')
                ->field('name,tel,province,city,customer_number,want_know')
                ->where('id =:id and status =1')->bind(['id'=>[$userid,\PDO::PARAM_INT]])->select();
            $user = $user[0];
            $user['want_know'] = explode(',', $user['want_know']);
            return json($user);
        }else{
            return json(array('非法参数'),200);
        }
        return json(array('获取失败'),400);
    }

    //我的信息保存
    public function userInfoSave()
    {
        $userid = input('uid');
        if(!is_numeric($userid)){
            return json(array('非法参数'),400);
        }
        // $hastel = Db::table('pre_min_user')->field('tel')->where('id =:id')->bind(['id'=>[$userid,\PDO::PARAM_INT]])->find();
        // if(!$hastel){
        $tel     = input('tel');
        $smgcode = input('smgcode');
        if(!$tel || !is_numeric($tel) || !$smgcode || !is_numeric($smgcode)){
            return json(array('请核对手机号和验证码正确后再提交'),400);
        }
        $data    = array('phone'=>$tel,'secCode'=>input('smgcode'));
        $url     = 'http://api.jiushang.cn/api/check-captcha';
        $results = self::sendToCurl($tel, $url, $data);
        if($results['msg'] !== '验证成功!'){
            return json(array('验证码错误'),400);
        }
        // }

        $post     = Request::instance()->post();
        $validate = Loader::validate('UserValidate');
        if(!$validate->check($post)){
            return json(array($validate->getError()),400);
        }

        $post['fill_in'] = 1;
        unset($post['smgcode']);
        $result = Db::table('pre_min_user')->where('id =:id')->bind(['id'=>[$userid,\PDO::PARAM_INT]])->update($post);
        if($result === false){
            return json(array('保存失败'),400);
        }else{
            return json(array('保存成功'),200);
        }
    }

    //资讯历史

    /**
     * @return Request
     */
    public function informationHistory()
    {
        $uid = input('uid');
        if(!is_numeric($uid)){
            return json('非法参数');
        }

        $page   = input('page')?input('page'):1;
        $result = Db::table('pre_min_information_history')->alias('ih')
            ->field('n.id,n.name')
            ->join('pre_min_news n','ih.news_id = n.id')
            ->where('ih.user_id =:id')->bind(['id'=>[$uid,\PDO::PARAM_INT]])
            ->order('ih.create_time DESC')->page($page,PAGESIZE)->select();

        if($result){
            return json($result,200);
        }else{
            return json([],200);
        }
    }

    //投诉反馈
    public function feedBack()
    {
        $uid = input('uid');
        if(!is_numeric($uid)){
            return json('非法参数');
        }

        $post = Request::instance()->post();

        $post['user_id'] = $uid;
        $result = Db::table('pre_min_feedback')->insertGetId($post);

        if($result){
            return json(array('投诉反馈成功'),200);
        }else{
            return json(array('投诉反馈失败'),400);
        }
    }

    private function getToken()
    {
        $url    = 'https://auth.jiushang.cn/oauth/token';
        $header = array('Authorization:Basic '.base64_encode('pt-sales-system:123456'));
        $data   = array('grant_type'=>'password','username'=>'th123','password'=>'111111');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);

        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
            exit(json_encode(0));
        }
        curl_close($ch);

        return json_decode($result, true);
    }

    //发送短信
    public function sendMsg()
    {
        $tel  = input('tel');
        $data = array('phone'=>$tel,'smsCode'=>'SMS_53050322');
        $url  = 'http://api.jiushang.cn/api/send-captcha';

        $result = self::sendToCurl($tel, $url, $data);
        if($result['errCode'] === 0){
            return json(array('验证码发送成功'),200);
        }else{
            return json(array('验证码发送失败'),400);
        }
    }

    private function sendToCurl($tel, $url, $data)
    {
        $result = self::getToken();
        $header = array('Content-type: application/json','Authorization:bearer '.$result['access_token']);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $result = curl_exec($ch);
        if($result === FALSE) {
            return json(array('验证码发送失败'),400);
        }
        curl_close($ch);
        return json_decode($result, true);
    }
}