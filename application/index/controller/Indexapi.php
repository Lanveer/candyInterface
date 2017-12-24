<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;

class Indexapi extends Controller{
    //获取头部menu
    public function menuList()
    {
        $lists = Db::table('pre_min_category')->field('id,name,logo')->where(['status'=>1])->select();

        $arr = array();
        foreach ($lists as $key => $list) {
            $arr[$key]['title']    = $list['name'];
            $arr[$key]['imageURL'] = $list['logo'];
            $arr[$key]['itemId']   = $list['id'];
        }

        if($arr){
            return json($arr,200);
        }else{
            return json('获取数据失败');
        }
    }

    //用户授权后，将用户信息存入数据库
    public function saveUserInfo()
    {
        $nick_name  = input('nick_name');//昵称
        $header_img = input('header');//头像
        $code       = input('code');//微信code

        if(!$code){
            return json('缺少参数');
        }

        $header = array(
            'Content-Type:application/json'
        );
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=wxdeb2af8946db58ef&secret=e7897880446c1b712011f06f5570e1a9&grant_type=authorization_code&js_code='.$code;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
            exit(json_encode(0));
        }
        curl_close($ch);
        $result = json_decode($result, true);

        $user   = Db::table('pre_min_user')->field('id,nick_name,header,fill_in')->where(['openid'=>$result['openid']])->find();
        if($user){
            return json($user,200);
        }else{
            if(!$nick_name || !$header_img){
                return json('缺少参数');
            }
            $userid = Db::table('pre_min_user')->insertGetId(array('nick_name'=>$nick_name,'header'=>$header_img,'openid'=>$result['openid']));
            return json(array('id'=>$userid,'nick_name'=>$nick_name,'header'=>$header_img,'fill_in'=>0),200);
        }
    }

    //保存用户定位信息
    public function  saveDestination()
    {
        $userid = input('uid');
        $lat    = input('lat');//纬度
        $lng    = input('lng');//经度

        if(empty($lat) || empty($lng) || empty($userid)){
            return json('缺少参数');
        }

        if(!is_numeric($userid) || !is_numeric($lat) || !is_numeric($lng)){
            return json('非法参数');
        }

        $result = Db::table('pre_min_user')->where('id =:id')->bind(['id'=>[$userid,\PDO::PARAM_INT]])->update(['lat'=>$lat,'lng'=>$lng]);
        return json(array($result),200);
    }
}
