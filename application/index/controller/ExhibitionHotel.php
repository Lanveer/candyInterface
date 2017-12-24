<?php

namespace app\index\controller;

use think\Request;
use think\Db;
use think\Loader;
use think\controller\Rest;

class Exhibitionhotel extends Rest{

    public function rest()
    {
        header("Access-Control-Allow-Origin:*");
        switch ($this->method){
           case 'get':     //查询
               $this->read($type);
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
    *显示酒店或者展馆列表
    */
    public function read($type)
    {
        header("Access-Control-Allow-Origin:*");
        $page = input('page')?input('page'):1;
        $id = input('id');
        $group = input('group');
        if($group){
            $types = Db::table('pre_min_bgimg')->field('id as bgimg_id ,group_name')->where('type =:type and status = 1')->bind(['type'=>[$type,\PDO::PARAM_INT]])->select();
            return json(array('errCode'=>0,'msg'=>'ok','content'=>$types));
        }else{
            if($type !=1 && $type !=2)
            {
                $type = 1;
            }
            if(!$type || !is_numeric($type)){
                return json(array('errCode'=>400,'msg'=>'非法参数','content'=>''));
            }
            if($id){
                //查询一条数据
                if(!is_numeric($id)){
                    return json(array('errCode'=>400,'msg'=>'非法参数','content'=>''));
                }
                $showInfo = model('ExhibitionHotel')->getList($id,$type);
                if($showInfo){
                    return json(array('errCode'=>0,'msg'=>'OK','content'=>$showInfo));
                }else{
                    return json(array('errCode'=>400,'msg'=>'数据异常','content'=>[]));
                }
            }else{
                //查询多条数据
                $keyword = input('keyword');
                //获取总条数
                $counts = model('ExhibitionHotel')->getCounts($type, $keyword);
                $pages = ceil($counts/PAGESIZE);
                //固定页码在有效范围内
                if($page<1){
                    $page = 1;
                }
                if($page>$pages){
                    $page = $pages;
                }
                $list = model('ExhibitionHotel')->checkExhibitionHotel($type, $page, $keyword);

                $arr = array('content'=>$list, 'totalElements'=>$counts, 'totalPages'=>$pages, 'number'=>$page);
                if($list){
                    return json(array('errCode'=>0,'msg'=>'OK','content'=>$arr),200);
                }else{
                    return json(array('errCode'=>400,'msg'=>'列表为空','content'=>['totalElements'=>$counts]));
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
        $id   = input('id');
        $data = json_decode(file_get_contents("php://input"), true);
        $validate = Loader::validate('ExhibitionhotelValidate');
        if(isset($data['type']) && $data['type'] == 1){
            $scene = 'type1';
        }elseif(isset($data['type']) && $data['type'] == 2){
            $scene = 'type2';
        }else{
            return json(array('errCode'=>400,'msg'=>'缺少type参数','content'=>[]));
        }
        if(!$validate->scene($scene)->check($data)){
            return json(array('errCode'=>400,'msg'=>$validate->getError(),'content'=>[]));
        }

        if($id){//修改操作
            if(!is_numeric($id)){
                return json(array('errCode'=>400,'msg'=>'非法参数','content'=>[]));
            }
            $res = Db::table('pre_min_exhibition_hotel')->where('id =:id')->bind(['id'=>[$id,\PDO::PARAM_INT]])->update($data);
            if($res === false){
                return json(array('errCode'=>400,'msg'=>'修改失败','content'=>[]));
            }elseif($res === 0){
                return json(array('errCode'=>200,'msg'=>'您没有做任何修改','content'=>[]));
            }else{
                return json(array('errCode'=>0,'msg'=>'OK','content'=>[]));
            }
        }else{//添加操作
            $result = Db::table('pre_min_exhibition_hotel')->insertGetId($data);
            if($result){
                return json(array('errCode'=>0,'msg'=>'OK','content'=>[]));
            }else{
                return json(array('errCode'=>400,'msg'=>'添加失败','content'=>[]));
            }
        }
    }

    /*
    *删除：1.更新status
    */
    public function delete($id)
    {
        header("Access-Control-Allow-Origin:*");
        if(!is_numeric($id)){
            return json(array('errCode'=>400,'msg'=>'非法参数','content'=>[]));
        }
        $result = Db::table('pre_min_exhibition_hotel')->where(['id'=>$id])->update(['status' => 0]);
        if($result ==1){
            return json(array('errCode'=>0,'msg'=>'OK','content'=>[]));
        }else{
            return json(array('errCode'=>400,'msg'=>'删除失败','content'=>[]));
        }
    }
}