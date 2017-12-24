<?php

namespace app\index\controller;

use think\Db;
use think\Loader;
use think\Request;
use think\controller\Rest;

class Agenda extends Rest
{
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
    *查询日程列表
    */
    public function read()
    {
        header("Access-Control-Allow-Origin:*");

        $datelist = input('datelist');//新增或修改时获取日期列表
        if($datelist){
            return json(array('errCode'=>0,'msg'=>'OK','content'=>self::getDate()));
        }else{
            $id = input('id');
            if($id){
                //查询一条数据
                if(!is_numeric($id)){
                    return json(array('errCode'=>400,'msg'=>'非法参数','content'=>[]));
                }
                $agenda = model('Agenda')->getOne($id);
                if($agenda){
                    return json(array('errCode'=>0,'msg'=>'OK','content'=>$agenda));
                }
                return json(array('errCode'=>400,'msg'=>'获取信息失败','content'=>[]));
            }else{
                $theme   = input('theme');
                $dateid  = input('dateid');
                $exhibitionhotel = input('hotel');
                $page = input('page')?input('page'):1;

                //获取总条数
                $counts = model('Agenda')->getCounts($theme,$dateid,$exhibitionhotel);
                $pages = ceil($counts/PAGESIZE);
                //固定页码在有效范围内
                if($page<1){
                    $page = 1;
                }
                if($page>$pages){
                    $page = $pages;
                }
                $list = model('Agenda')->checkAgenda($page,$theme,$dateid,$exhibitionhotel);
                $arr = array('content'=>$list, 'totalElements'=>$counts, 'totalPages'=>$pages, 'number'=>$page);
                if($list){
                    return json(array('errCode'=>0,'msg'=>'OK','content'=>$arr));
                }else{
                    return json(array('errCode'=>200,'msg'=>'列表信息为空','content'=>[]));
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

        $validate = Loader::validate('AgendaValidate');
        if(!$validate->check($data)){
            return json(array('errCode'=>400,'msg'=>$validate->getError(),'content'=>[]));
        }

        if($id){//修改操作
            if(!is_numeric($id)){
                return json(array('errCode'=>400,'msg'=>'非法参数','content'=>''));
            }

            $result = Db::table('pre_min_agenda')->where('id =:id')->bind(['id'=>[$id,\PDO::PARAM_INT]])->update($data);
            if($result === false){
                return json(array('errCode'=>400,'msg'=>'修改失败','content'=>[]));
            }elseif($result === 0){
                return json(array('errCode'=>200,'msg'=>'您没有做任何修改','content'=>[]));
            }else{
                return json(array('errCode'=>0,'msg'=>'OK','content'=>[]));
            }
        }else{//添加操作
            $result = Db::table('pre_min_agenda')->insertGetId($data);
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
            $res = Db::table('pre_min_agenda')->where(['id'=>$id])->update(['status'=>0]);
            if($res ==1){
                return json(array('errCode'=>0,'msg'=>'OK','content'=>''),200);
            }else{
                return json(array('errCode'=>400,'msg'=>'删除失败','content'=>''));
            }
        }
        return json(array('errCode'=>400,'msg'=>'非法参数','content'=>''));
    }

    /*
    *获得日期
    */
    private function getDate()
    {
        header("Access-Control-Allow-Origin:*");
        return $date = Db::table('pre_min_date')->select();
    }
}