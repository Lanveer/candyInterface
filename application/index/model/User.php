<?php

namespace app\index\model;

use think\Model;
use think\Db;

class User extends Model{
    /*
    *用户信息
    */
    public function checkUserInfo($page='')
    {
        $list =  Db::table('pre_min_user')->where('status','1')->field('id,nick_name,header,name,tel,province,city,customer_number,want_know')->order('create_time DESC')
        ;
        if($page){
            $list->page($page,PAGESIZE);
        }
        $lists = $list->select();
        return $lists;
    }

    /*
    *用户信息总条数
    */
    public function getConts()
    {
        $counts = Db::table('pre_min_user')->field('id')->where(['status'=>1])->count();
        return $counts;
    }

    /*
    *删除用户信息
    */
    public function del($id)
    {
            $res = Db::table('pre_min_user')->where('id = :id')->bind(['id'=>$id])->update(['status'=>0]);
            return $res;
    }

}