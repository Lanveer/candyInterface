<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Loader;
use think\controller\Rest;

class User extends Rest{
    /*
    *查询用户信息:
    *content 内容列表
    *totalPages    总页数
    *totalElements   总条数
    *last    是否是最后一页
    *number  当前页码
    *numberOfElements    当前是第几条数据
    */
    public function rest()
    {
        header("Access-Control-Allow-Origin:*");
        switch ($this->method){
           case 'get':     //查询
               $this->read();
               break;
           case 'delete':  //删除
               $this->delete($id);
               break;
        }
    }

    public function read()
    {
        header("Access-Control-Allow-Origin:*");

        $export = input('export');
        if($export){
            self::export();
            return json_encode(array('errCode'=>0,'msg'=>'OK','content'=>[]));
        }else{
            $model = model('User');
            $page = input('page')?input('page'):1;
            if(!is_numeric($page)){
                return json_encode(array('errCode'=>0,'msg'=>'非法参数','content'=>[]));
            }
            //获得总条数
            $counts = $model->getConts();
            $pages = ceil($counts/PAGESIZE);
            //固定页码在有效范围内
            if($page<1){
                $page = 1;
            }
            if($page>$pages){
                $page = $pages;
            }
            //获得列表
            $userList = $model->checkUserInfo($page);
            $data = array();
            if($userList){
                foreach ($userList as $vi) {
                    $vi['area'] = $vi['province'].$vi['city'];
                    unset($vi['province']);
                    unset($vi['city']);
                    $vi['more'] = $vi['want_know'];
                    unset($vi['want_know']);
                    $data[] = $vi;
                }
                $arr = array('content'=>$data, 'totalElements'=>$counts, 'totalPages'=>$pages, 'number'=>$page);
                if($data){
                    return json(array('errCode'=>0,'msg'=>'OK','content'=>$arr),200);
                }
            }
            return json(array('errCode'=>400,'msg'=>'列表信息获取失败','content'=>[]));
        }
    }

    /*
    *删除用户信息
    */
    public function delete($id)
    {
        header("Access-Control-Allow-Origin:*");
        $model = model('User');
        if(!is_numeric($id)){
            return json(array('errCode'=>0,'msg'=>'非法参数','content'=>[]));
        }
        $rs=$model->del($id);
        if($rs){
           return json(array('errCode'=>0,'msg'=>'OK','content'=>[]));
       }else{
           return json(array('errCode'=>400,'msg'=>'删除失败','content'=>[]));
       }
    }

    /*
    *导出用户信息为excel表格
    */
    private function export()
    {
        header("Access-Control-Allow-Origin:*");
        //查出数据
        $model =  Model("User");
        $list  = $model->checkUserInfo();
        $data  = array();
        if($list){
            foreach ($list as $vi) {
                $vi['area'] = $vi['province'].$vi['city'];
                unset($vi['province']);
                unset($vi['city']);
                $vi['more'] = $vi['want_know'];
                unset($vi['want_know']);
                $data[] = $vi;
            }
        }
        //生成的Excel文件文件名
        $name='Excelfile'.date('Ymd',time());
        $res=self::setExcel($data,$name);
    }

    /*
    *导出方法,此方法还不完善
    */
   private function setExcel($data, $name)
   {
        $headTitle = "酒商网微信用户";
        $headtitle= "<tr style='height:50px;'></tr>";
        $titlename = "<tr>
               <th style='width:60px;'>用户id</th>
               <th style='width:70px;'>昵称</th>
               <th style='width:150px;'>头像</th>
               <th style='width:70px;'>姓名</th>
               <th style='width:100px;'>电话</th>
               <th style='width:100px;'>地区</th>
               <th style='width:70px;'>用户编码</th>
               <th style='width:200px;'>更多</th>
           </tr>";
        $filename = "用户列表.xls";

        $str = "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\"\r\nxmlns:x=\"urn:schemas-microsoft-com:office:excel\"\r\nxmlns=\"http://www.w3.org/TR/REC-html40\">\r\n<head><meta http-equiv=Content-Type content=\"text/html; charset=utf-8\"></head><body>";
        $str .="<table border='1'><head>".$titlename."</head>";

        foreach ($data as $key=> $rt)
        {
            $str .= "<tr style='text-align:center'>";
            foreach ($rt as $k => $v)
            {
                $str .= "<td>{$v}</td>";
            }
            $str .= "</tr>\n";
        }
        $str .= "</table></body></html>";

        header("Content-Type: application/vnd.ms-excel; name='excel'");
        header("Content-type: application/octet-stream" );
        header("Content-Disposition: attachment; filename=".$filename);
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache" );
        header("Expires: 0" );
        exit($str);
    }
}