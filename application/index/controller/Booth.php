<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Loader;
use think\controller\Rest;

class Booth extends Rest{

    public function rest(){
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
    *显示展位列表
    */
    public function read()
    {
        header("Access-Control-Allow-Origin:*");
        $keyword = input('keyword');
        $exhibitionhotel = input('exhibitionhotel');
        $bath = input('bath');
        if($bath){
            if(is_numeric($bath)){
                $floors = model('Floor')->getFloors($bath);
                $arr = [];
                foreach ($floors as $floor) {
                    $arr[$floor['id']] = $floor['detail'].' '.$floor['tag'];
                }
            }else{
                return json(array('errCode'=>400,'msg'=>'参数非法','content'=>[]));
            }
            return json(array('errCode'=>0,'msg'=>'ok','content'=>$arr));
        }
        if($exhibitionhotel){
            //酒店-展馆列表
            $lists = model('ExhibitionHotel')->getLists();
            return json(array('errCode'=>0,'msg'=>'ok','content'=>$lists));
        }else{
            $id = input('id');
            if($id){
                //查询一条数据
                if(!is_numeric($id)){
                    return json(array('errCode'=>0,'msg'=>'非法参数','content'=>[]));
                }
                $res = model('Booth')->getOne($id);
                if($res){
                    return json(array('errCode'=>0,'msg'=>'OK','content'=>$res));
                }else{
                    return json(array('errCode'=>400,'msg'=>'获取信息失败','content'=>[]));
                }
            }else{
                //查询多条数据
                $page = input('page')?input('page'):1;
                //获取总条数
                $counts = model('Booth')->getCounts($keyword);
                $pages = ceil($counts/PAGESIZE);
                //固定页码在有效范围内
                if($page<1){
                    $page = 1;
                }
                if($page>$pages){
                    $page = $pages;
                }
                $list = model('Booth')->checkBooth($page, $keyword);
                $arr = array('content'=>$list, 'totalElements'=>$counts, 'totalPages'=>$pages, 'number'=>$page);
                if($list){
                    return json(array('errCode'=>0,'msg'=>'OK','content'=>$arr));
                }else{
                    return json(array('errCode'=>400,'msg'=>'信息为空','content'=>['totalElements'=>$counts]));
                }
            }
        }
    }

    /*
    *修改：1.显示数据  2.更新数据
    */
    public function update()
    {
        header("Access-Control-Allow-Origin:*");
        $id = input('id');
        $data = json_decode(file_get_contents("php://input"), true);

        $validate = Loader::validate('BoothValidate');
        if(!$validate->check($data)){
            return json(array('errCode'=>400,'msg'=>$validate->getError(),'content'=>[]));
        }

        if($id){
            //修改操作
            if(!is_numeric($id)){
                return json(array('errCode'=>0,'msg'=>'非法参数','content'=>[]));
            }
            $res = Db::table('pre_min_booth')->where(['id'=>$id])->update($data);
            if($res === false){
                return json(array('errCode'=>400,'msg'=>'修改失败','content'=>[]));
            }elseif($res === 0){
                return json(array('errCode'=>200,'msg'=>'您没有做任何修改','content'=>[]));
            }else{
                return json(array('errCode'=>0,'msg'=>'OK','content'=>[]));
            }
        }else{
            //添加操作
            $result = Db::table('pre_min_booth')->insertGetId($data);
            if($result){
                return json(array('errCode'=>0,'msg'=>'OK','content'=>[]));
            }else{
               return json(array('errCode'=>400,'msg'=>'添加失败','content'=>[]));
            }
        }
    }

    /*
    *删除: 1.更新status
    */
    public function delete($id)
    {
        header("Access-Control-Allow-Origin:*");
        if($id && is_numeric($id)){
            $res = Db::table('pre_min_booth')->where(['id'=>$id])->update(['status'=>0]);
            if($res ==1){
                return json(array('errCode'=>0,'msg'=>'OK','content'=>[]),200);
            }else{
                return json(array('errCode'=>400,'msg'=>'删除失败','content'=>[]));
            }
        }
        return json(array('errCode'=>400,'msg'=>'非法参数','content'=>[]));

    }
}