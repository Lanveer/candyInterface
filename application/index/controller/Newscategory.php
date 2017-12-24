<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\controller\Rest;

class Newscategory extends Rest{

    public function rest(){
        header("Access-Control-Allow-Origin:*");
        switch ($this->method){
           case 'get':     //查询
               $this->read();
               break;
           case 'post':    //新增
               $this->add();
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
    *查询新闻分类列表
    */
    public function read()
    {
        header("Access-Control-Allow-Origin:*");

        $page = input('page')?input('page'):1;
        $id   = input('id');
        if($id){
            if(!is_numeric($id)){
                return json(array('errCode'=>400,'msg'=>'非法参数','content'=>[]));
            }
            $list = Db::table('pre_min_news_category')->field('id,category')->where('id =:id and status =1')->bind(['id'=>[$id,\PDO::PARAM_INT]])->find();
            if($list){
                return json(array('errCode'=>0,'msg'=>'OK','content'=>$list));
            }
            return json(array('errCode'=>400,'msg'=>'信息获取失败','content'=>[]));
        }else{
            $model  = model('NewsCategory');
            $counts = $model->getConts();
            $pages = ceil($counts/PAGESIZE);
            //固定页码在有效范围内
            if($page<1){
                $page = 1;
            }
            if($page>$pages){
                $page = $pages;
            }
            $data  = $model->checkNewsCategory($page);
            $arr = array('content'=>$data, 'totalElements'=>$counts, 'totalPages'=>$pages, 'number'=>$page);
            if($data){
                return json(array('errCode'=>0,'msg'=>'OK','content'=>$arr));
            }
            return json(array('errCode'=>200,'msg'=>'列表为空','content'=>['totalElements'=>$counts]));
        }
    }

    /*
    *删除新闻分类
    */
    public function delete($id)
    {
        header("Access-Control-Allow-Origin:*");
        if(!is_numeric($id)){
            return json(array('errCode'=>400,'msg'=>'非法参数','content'=>[]));
        }
        //判断该分类是否被新闻信息使用
        $newsres = Db::table('pre_min_news')->where(['news_category_id'=>$id,'status'=>1])->find();
        //判断该分类是否被轮播图使用
        $bannerres = Db::table('pre_min_banner')->where(['news_category_id'=>$id,'status'=>1])->find();
        if(empty($newsres) && empty($bannerres)){
            $res = model('NewsCategory')->delNewsCategory($id);
            if($res ==1){
                return json(array('errCode'=>0,'msg'=>'OK','content'=>[]));
            }else{
                return json(array('errCode'=>400,'msg'=>'删除失败','content'=>[]));
            }
        }else{
            return json(array('errCode'=>400,'msg'=>'该分类信息正在被使用，无法删除。','content'=>[]));
        }
    }

    /*
    *修改新闻分类
    */
    public function update()
    {
        header("Access-Control-Allow-Origin:*");
        if($id = input('id')){
            if(!is_numeric($id))
            {
                return json(array('errCode'=>400,'msg'=>'非法参数','content'=>[]));
            }
            $data = json_decode(file_get_contents("php://input"), true);
            if(empty($data)){
                return json(array('errCode'=>400,'msg'=>'非法参数','content'=>''));
            }
            $category = $data['category'];
            if(strlen($category)<50){
                $res = model('NewsCategory')->updNewsCategory($id,$category);
                if($res === false){
                    return json(array('errCode'=>400,'msg'=>'修改失败','content'=>[]));
                }elseif($res === 0){
                    return json(array('errCode'=>200,'msg'=>'您没有做任何修改','content'=>[]));
                }else{
                    return json(array('errCode'=>0,'msg'=>'OK','content'=>[]));
                }
            }
            return json(array('errCode'=>400,'msg'=>'修改失败','content'=>[]));
        }else{
            $category = json_decode(file_get_contents("php://input"), true);
            if(empty($category)){
                return json(array('errCode'=>400,'msg'=>'非法参数','content'=>''));
            }
            $category = $category['category'];
            if(strlen($category)<50){
                $res = model('NewsCategory')->addNewsCategory($category);
                if($res){
                    return json(array('errCode'=>0,'msg'=>'OK','content'=>[]));
                }
            }
            return json(array('errCode'=>400,'msg'=>'添加失败','content'=>[]));
        }
    }
}