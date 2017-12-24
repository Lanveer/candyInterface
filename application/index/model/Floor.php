<?php

namespace app\index\model;

use think\Model;
use think\Db;

class Floor extends Model{
    //查询楼层和几号馆
    public function getFloors($type)
    {
        $floors = Db::table('pre_min_floor')->field('id,detail,tag')->where('status = 1 and type =:type')->bind(['type'=>[$type,\PDO::PARAM_INT]])->select();
        return $floors;
    }
}