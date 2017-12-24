<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Loader;
use think\controller\Rest;

class News extends Rest{

    public function rest(){
        header("Access-Control-Allow-Origin:*");
        switch ($this->method){
           case 'get':     //查询
               $this->read();
               break;
           case 'post':     //增加修改
                $this->update();
               break;
           case 'delete':  //删除
               $this->delete($id);
               break;
        }
    }

    /*
    *显示新闻列表、搜索
    */
    public function read()
    {
        header("Access-Control-Allow-Origin:*");

        $id = input('id');
        if($id){
            //显示数据
            if(!is_numeric($id)){
                return json(array('errCode'=>400,'msg'=>'非法参数','content'=>''));
            }
            $list = model('News')->getOne($id);
            if($list){
                return json(array('errCode'=>0,'msg'=>'OK','content'=>$list),200);
            }
            return json(array('errCode'=>400,'msg'=>'获取信息失败','content'=>[]));
        }else{
            $keyword = input('keyword');
            $page = input('page')?input('page'):1;
            if(!is_numeric($page)){
                return json(array('errCode'=>400,'msg'=>'非法参数','content'=>''));
            }
            //获取总条数
            $counts = model('News')->getCounts($keyword);
            $pages = ceil($counts/PAGESIZE);
            //固定页码在有效范围内
            if($page<1){
                $page = 1;
            }
            if($page>$pages){
                $page = $pages;
            }
            $list = model('News')->checkNews($page,$keyword);
            $arr = array('content'=>$list, 'totalElements'=>$counts, 'totalPages'=>$pages, 'number'=>$page);
            if($list){
                return json(array('errCode'=>0,'msg'=>'OK','content'=>$arr));
            }else{
                return json(array('errCode'=>200,'msg'=>'列表为空','content'=>[]));
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
            return json(array('errCode'=>400,'msg'=>'非法参数','content'=>''));
        }
        $res = Db::table('pre_min_news')->where(['id'=>$id])->update(['status'=>0]);
        if($res == 1){
           return json(array('errCode'=>0,'msg'=>'OK','content'=>''),200);
        }
        return json(array('errCode'=>400,'msg'=>'删除失败','content'=>''));
    }

    /*
    *修改：1显示数据，2.修改数据
    */
    public function update()
    {
        header("Access-Control-Allow-Origin:*");

        $id   = input('id');
        //获取数据
        $data = json_decode(file_get_contents("php://input"), true);

        $validate = Loader::validate('NewsValidate');
        if(!$validate->check($data)){
            return json(array('errCode'=>400,'msg'=>$validate->getError(),'content'=>[]));
        }

        if($id){//修改操做
            if(!$id && !is_numeric($id))
            {
                return json(array('errCode'=>400,'msg'=>'非法参数','content'=>[]));
            }
            $res = Db::table('pre_min_news')->where('id = :id')->bind(['id'=>[$id,\PDO::PARAM_INT]])->update($data);
            if($res === false){
                return json(array('errCode'=>400,'msg'=>'修改失败','content'=>[]));
            }elseif($res === 0){
                return json(array('errCode'=>200,'msg'=>'您没有做任何修改','content'=>[]));
            }else{
                return json(array('errCode'=>0,'msg'=>'OK','content'=>[]));
            }
        }else{//新增操作
            $res = Db::table('pre_min_news')->insertGetId($data);
            if($res){
                return json(array('errCode'=>0,'msg'=>'OK','content'=>''),200);
            }
            return json(array('errCode'=>400,'msg'=>'添加失败','content'=>''));
        }
    }
}
