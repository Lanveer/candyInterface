<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;
Route::rule('/',function(){
    return 'Hello,world!';
});

/**
 * 旧版本的RestFul路由设置
 */

// 老版本模块名
define('OLDM', 'index');
// 老版本路由前缀
define('ROUTE_PIX', 'app/weixin/');


//用户管理
Route::get(ROUTE_PIX . 'user', OLDM . '/User/read');//查询
/*导出访问查询的接口，带上export参数（例：user?export=true）*/
Route::post(ROUTE_PIX . 'user/[:id]', OLDM . '/User/add'); //新增
Route::delete(ROUTE_PIX . 'user/:id', OLDM . '/User/delete'); //删除

//日程管理
Route::get(ROUTE_PIX . 'agenda', OLDM .'/Agenda/read');//查询
Route::post(ROUTE_PIX . 'agenda/[:id]', OLDM .'/Agenda/update'); //新增修改
Route::delete(ROUTE_PIX . 'agenda/:id', OLDM . '/Agenda/delete'); //删除

//酒店、展馆管理
Route::get(ROUTE_PIX . 'exhibitionhotel/:type', OLDM . '/Exhibitionhotel/read');//查询
/*新增和修改时需访问查询接口并带上group参数，如exhibitionhotel/1?group=true*/
Route::post(ROUTE_PIX . 'exhibitionhotel/[:id]', OLDM . '/Exhibitionhotel/update'); //新增修改
Route::delete(ROUTE_PIX . 'exhibitionhotel/:id', OLDM . '/Exhibitionhotel/delete'); //删除

//展位管理
Route::get(ROUTE_PIX . 'booth', OLDM . '/Booth/read');//查询
/*新增和修改时需访问查询接口并带上exhibitionhotel参数，如:booth?exhibitionhotel=true*/
Route::post(ROUTE_PIX . 'booth/[:id]', OLDM . '/Booth/update'); //修改后的数据
Route::delete(ROUTE_PIX . 'booth/:id', OLDM . '/Booth/delete'); //删除

//分类管理111
Route::get(ROUTE_PIX . 'newscategory', OLDM . '/Newscategory/read');//查询
Route::post(ROUTE_PIX . 'newscategory/[:id]', OLDM . '/Newscategory/update'); //新增修改
Route::delete(ROUTE_PIX . 'newscategory/:id', OLDM . '/Newscategory/delete'); //删除

//轮播图管理11
Route::get(ROUTE_PIX . 'banner', OLDM . '/Banner/read');//查询
Route::post(ROUTE_PIX . 'banner/[:id]', OLDM . '/Banner/update'); //新增修改
Route::delete(ROUTE_PIX . 'banner/:id', OLDM . '/Banner/delete'); //删除

//新闻管理11
Route::get(ROUTE_PIX . 'news', OLDM . '/News/read');//查询
Route::post(ROUTE_PIX . 'news/[:id]', OLDM . '/News/update'); //新增修改
Route::delete(ROUTE_PIX . 'news/:id', OLDM . '/News/delete'); //删除

//背景图管理
Route::get(ROUTE_PIX . 'backgroundimg', OLDM . '/Backgroundimg/read');//查询
Route::post(ROUTE_PIX . 'backgroundimg/[:id]', OLDM . '/backgroundimg/update'); //修改后的数据
Route::delete(ROUTE_PIX . 'backgroundimg/:id', OLDM . '/Backgroundimg/delete'); //删除


/**
 * 新版本的RestFul路由设置
 */

// v2版后台管理路由前缀，比如v2
define('V2_ROUTE_PIX', '');


// 新闻
Route::resource(V2_ROUTE_PIX . 'news','v2/news');

// 日程
Route::resource(V2_ROUTE_PIX . 'agenda','v2/agenda');
Route::resource(V2_ROUTE_PIX . 'dates','v2/dates');

// 酒店/展馆
Route::resource(V2_ROUTE_PIX . 'exhibitionHotel','v2/exhibitionHotel');
// 酒店导入接口
Route::rule(V2_ROUTE_PIX . 'exhibitionHotel/putIn','v2/exhibitionHotel/putIn');

// 展位
Route::resource(V2_ROUTE_PIX . 'booth','v2/booth');
// 展位导入接口
Route::rule(V2_ROUTE_PIX . 'booth/putIn','v2/booth/putIn');

// 反馈
Route::resource(V2_ROUTE_PIX . 'feedback','v2/feedback');

// 消息
Route::resource(V2_ROUTE_PIX . 'message','v2/message');

// 用户接口
Route::resource(V2_ROUTE_PIX . 'user','v2/user');
Route::resource(V2_ROUTE_PIX . 'boothCollection','v2/boothCollection');
Route::rule(V2_ROUTE_PIX . 'user/export','v2/user/export');

// 用户行为
Route::resource(V2_ROUTE_PIX . 'actionCount','v2/actionCount');
Route::rule(V2_ROUTE_PIX . 'actionCount/export','v2/actionCount/export');
