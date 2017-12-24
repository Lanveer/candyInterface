<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\controller\Rest;

class Backgroundimg extends Rest{
    public function rest()
    {
        header("Access-Control-Allow-Origin:*");
        switch ($this->method){
           case 'get':     //查询
               $this->read();
               break;
           case 'post':     //修改
                $this->update();
               break;
           case 'delete':  //删除
               $this->delete($id);
               break;
        }
    }

    /*
    *查询背景图
     */
    public function read()
    {
        header('Access-Control-Allow-Origin:*');
        $page = input('page')?input('page'):1;
        $id = input('id');
        if($id){

            //查询一条数据
            if(!is_numeric($id)){
                return json(array('errCode'=>400,'msg'=>'非法参数','content'=>[]));
            }
            $showInfo = Db::table('pre_min_bgimg')->field('id,type,back_image,group_name,hotel_num')->where('id=:id and status=1')->bind(['id'=>[$id,\PDO::PARAM_INT]])->find();
            if($showInfo){
                return json(array('errCode'=>0,'msg'=>'OK','content'=>$showInfo));
            }else{
                return json(array('errCode'=>400,'msg'=>'数据异常','content'=>[]));
            }
        }else{
            //查询多条数据
            if(!is_numeric($page)){
                return json(array('errCode'=>400,'msg'=>'非法参数','content'=>[]));
            }
            //获得总条数
            $counts = Db::table('pre_min_bgimg')->field('id')->where(['status'=>1])->count();
            $pages = ceil($counts/PAGESIZE);
            if($page<0){
                $page = 1;
            }
            if($page>$pages){
                $page = $pages;
            }
            $list = Db::table('pre_min_bgimg')->field('id,type,back_image,group_name')->where(['status'=>1])->order('create_time DESC')->page($page,PAGESIZE)->select();
            $arr = array('content'=>$list,'totalElements'=>$counts,'totalPages'=>$pages,'number'=>$page);
            if($list){
                return json(array('errCode'=>0,'msg'=>'OK','content'=>$arr));
            }else{
                return json(array('errCode'=>400,'msg'=>'信息为空','content'=>['totalElements'=>$counts]));
            }
        }
    }


    /*
    *新增修改
    */
    public function update()
    {
        header('Access-Control-Allow-Origin:*');
        $id = input('id');
        $data = json_decode(file_get_contents("php://input"),true);
        if($id){
            //修改操作
            if(!is_numeric($id)){
                return json(array('errCode'=>400,'msg'=>'非法参数','content'=>[]));
            }
            $res = Db::table('pre_min_bgimg')->where('id=:id')->bind(['id'=>[$id,\PDO::PARAM_INT]])->update($data);
            if($res === false){
                return json(array('errCode'=>400,'msg'=>'修改失败','content'=>[]));
            }elseif($res === 0){
                return json(array('errCode'=>200,'msg'=>'您没有做任何修改','content'=>[]));
            }else{
                return json(array('errCode'=>0,'msg'=>'OK','content'=>[]));
            }
        }else{
            //添加操作
            $res = Db::table('pre_min_bgimg')->insertGetId($data);

            if($res){
                return json(array('errCode'=>0,'msg'=>'OK','content'=>[]));
            }else{
                return json(array('errCode'=>400,'msg'=>'添加失败','content'=>[]));
            }
        }
    }


    /*
    *删除：1.修改status为0
    */
    public function delete($id)
    {
        header("Access-Control-Allow-Origin:*");

        if(!is_numeric($id)){
            return json(array('errCode'=>400,'msg'=>'非法参数','content'=>''));
        }
        //判断是否被酒店或者展馆使用
        $info = Db::table('pre_min_exhibition_hotel')->where('bgimg_id=:id and status=1')->bind(['id'=>[$id,\PDO::PARAM_INT]])->find();
        if($info){
            return json(array('errCode'=>400,'msg'=>'该背景图正在被使用','content'=>[]));
        }else{
            $res = Db::table('pre_min_bgimg')->where('id=:id')->bind(['id'=>[$id,\PDO::PARAM_INT]])->update(['status'=>0]);
            if($res ==1){
                return json(array('errCode'=>0,'msg'=>'OK','content'=>[]));
            }else{
                return json(array('errCode'=>400,'msg'=>'删除失败','content'=>[]));
            }
        }
    }

}