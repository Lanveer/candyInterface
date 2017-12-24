<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\controller\Rest;

class Banner extends Rest{

    public function rest(){
        header("Access-Control-Allow-Origin:*");
        switch ($this->method){
           case 'get':     //查询
               $this->read();
               break;
           case 'post':     //新增修改
                $this->update();
               break;
           case 'delete':  //删除
               $this->delete($id);
               break;
        }
    }

    /*
    *查询轮播列表
    */
    public function read()
    {
        header("Access-Control-Allow-Origin:*");
        $page = input('page')?input('page'):1;
        if($id=input('id')){//查询单条数据
            if(!is_numeric($id)){
                return json(array('errCode'=>400,'msg'=>'非法参数','content'=>[]));
            }
            $list = model('Banner')->checkBanner('',$id);
            if($list){
                return json(array('errCode'=>0,'msg'=>'OK','content'=>$list[0]));
            }else{
                return json(array('errCode'=>400,'msg'=>'获取信息失败','content'=>[]));
            }
        }else{//查询多条
            if(!is_numeric($page)){
                return json(array('errCode'=>400,'msg'=>'非法参数','content'=>[]));
            }
            //获得总条数
            $counts = model('Banner')->getCounts();
            $pages = ceil($counts/PAGESIZE);
            if($page<0){
                $page = 1;
            }
            if($page>$pages){
                $page = $pages;
            }
            $list = model('Banner')->checkBanner($page);
            $arr = array('content'=>$list,'totalElements'=>$counts,'totalPages'=>$pages,'number'=>$page);
            if($list){
                return json(array('errCode'=>0,'msg'=>'OK','content'=>$arr));
            }else{
                return json(array('errCode'=>200,'msg'=>'列表为空','content'=>['totalElements'=>$counts]));
            }
        }
    }

    /*
    *新增、修改：1.显示数据  2.修改数据
    */
    public function update()
    {
        header("Access-Control-Allow-Origin:*");
        $id=input('id');
        if($id){//修改操作
            if(!is_numeric($id)){
                return json(array('errCode'=>400,'msg'=>'非法参数','content'=>[]));
            }
            $data = json_decode(file_get_contents("php://input"), true);
            if(empty($data)){
                return json(array('errCode'=>400,'msg'=>'非法参数','content'=>''));
            }
            $res = model('Banner')->upd($id,$data);
            if($res === false){
                return json(array('errCode'=>400,'msg'=>'修改失败','content'=>[]));
            }elseif($res === 0){
                return json(array('errCode'=>200,'msg'=>'您没有做任何修改','content'=>[]));
            }else{
                return json(array('errCode'=>0,'msg'=>'OK','content'=>[]));
            }
        }else{//添加操作
            $data = json_decode(file_get_contents("php://input"), true);
            if(empty($data)){
                return json(array('errCode'=>400,'msg'=>'非法参数','content'=>''));
            }
            $res = model('Banner')->add($data);
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
            return json(array('errCode'=>400,'msg'=>'非法参数','content'=>[]));
        }
        $res = model('Banner')->del($id);
        if($res ==1){
            return json(array('errCode'=>0,'msg'=>'OK','content'=>[]));
        }
        return json(array('errCode'=>400,'msg'=>'删除失败','content'=>[]));
    }
}
