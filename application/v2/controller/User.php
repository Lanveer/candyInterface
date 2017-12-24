<?php

namespace app\v2\controller;

use think\Request;
use app\v2\model\UserV2;

/**
 * 用户
 */
class User extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $page = $request->param('page/d', 1);
        $limit = $request->param('limit/d', 10);

        // 限制limit最大为100
        $limit = $limit < 100 ? $limit : 100;
        $list = UserV2::scope('filter', $request)
                ->limit($limit)
                ->order('id desc')
                ->page($page);

        $list = $list->select();
        if (empty($list)) {
            return $this->ajaxResponse([], '列表数据为空', 300);
        }

        // 统计总条数
        $total = UserV2::scope('filter', $request)->count();
        $totalPages = ceil($total / $limit);

        return $this->ajaxResponse([
            'content' => $list,
            'totalElements' => $total,
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ]);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        if (is_numeric($id) && strlen($id) < 32) {
            $data = UserV2::where(['id' => $id]);
        } else {
            $data = UserV2::where(['openid' => $id]);
        }

        $data = $data->with(['booth' => ['exhibition']])->find();
        if (empty($data)) {
            return $this->ajaxResponse([], '不存在此资源', 300);
        }

        return $this->ajaxResponse($data);
    }

    /**
     * 认证资质通过
     */
    public function validated()
    {
        $uid = $this->request->param('uid/d');
        if (empty($uid)) {
            return $this->ajaxResponse([], '参数不正确', 400);
        }

        $user = UserV2::where(['js_uid' => $uid])->find();
        if (empty($user)) {
            return $this->ajaxResponse([], '不存在此资源', 404);
        }

        $user->validated = 1;
        if ($user->save()) {
            return $this->ajaxResponse();
        }

        return $this->ajaxResponse([], '服务器出错', 500);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        // TODO::为啥要删除
    }

    /**
     * 导出全部资源
     *
     * @return \think\Response
     */
    public function export(Request $request)
    {
        $keys = [
            'id' => '编号',
            'js_uid' => '酒商网ID',
            'nick_name' => '昵称',
            'header' => '头像',
            'name' => '真实姓名',
            'tel' => '电话号码',
            'openid' => 'OPENID',
            'identity' => '身份',
            'tags' => '标签',
            'customer_number' => '客户邀请码',
            'qrcode' => '二维码邀请',
            'create_time' => '创建时间',
        ];
        // 查询出用户数据
        $data = UserV2::all();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i] = $data[$i]->toArray();
            if (empty($data[$i]['tags'])) {
                $data[$i]['tags'] = '';
            } else {
                $data[$i]['tags'] = implode(',', $data[$i]['tags']);
            }
        }
        $file = Excel::getExcelByData($data, $keys, '用户数据导出_' . date('Y_m_d', time()));

        if (empty($file)) {
            return $this->ajaxResponse([], '导出失败', 500);
        }

        return $this->ajaxResponse(['path' => $file, 'host' => $request->domain()]);
    }

    /**
     * 推广统计数据
     */
    public function countData()
    {
        $keys = [
            'qrcode' => '手机号',
            'customer_number' => '客服码',
            'num' => '推广人数',
            'access' => '通过人数',
        ];

        // 统计时间段的
        $ret = $this->request->param('ret');

        $q = \think\Db::name('user_v2')
            ->where(['validated' => 1])
            ->field('COUNT(*)')
            ->where(function($q){
                $q->where('`qrcode` = `a`.`qrcode`')->where('`customer_number` = `a`.`customer_number`');
            });

        if (!empty($ret)) {
            $q = $q->whereTime('create_time', $ret);
        }

        $q = $q->buildSql();
        $count = \think\Db::name('user_v2')
            ->alias('a')
            ->field("qrcode,customer_number,COUNT(*) as num, $q as access")
            ->group('qrcode,customer_number');

        if (!empty($ret)) {
            $count = $count->whereTime('create_time', $ret);
        }

        $count = $count->select();

        // echo $count;exit();
        // var_dump($count);exit();

        $file = Excel::getExcelByData($count, $keys, '推广数据统计导出_' . $ret . '_' . date('Y_m_d', time()));

        if (!$file) {
            return $this->ajaxResponse([], '提取失败', 500);
        }

        return $this->ajaxResponse(['path' => $file, 'host' => $this->request->domain()]);
    }

    /**
     * 特殊统计
     * @return [type] [description]
     */
    public function countDataS()
    {
        $keys = [
            'qrcode' => '手机号',
            'customer_number' => '客服码',
            'num' => '推广人数',
            'access' => '通过人数',
        ];

        $js_uids = [13241, 13229, 10705, 508, 13231, 514, 13116, 952, 12565, 708, 13238, 988, 12977, 915, 464, 792, 13236, 13211, 970, 13276, 13277, 867, 794, 13225, 800, 12572, 13221, 801, 13280, 13281, 916, 13283, 13286, 13330, 13341, 13340, 677, 13249, 13311, 600, 7252, 13382, 13398, 13395, 13407, 13375, 13267, 13435, 13432, 13508, 13463, 13538, 13579, 13670, 13681, 13702, 11987, 13730, 13731, 13746, 13760, 13806, 13824, 13795, 13905, 13927, 13933, 593, 904, 13953, 12973, 12762, 12978, 13997, 13021, 14029, 14058, 14064, 14127, 14153, 14143, 14162, 12761, 14179, 14219, 14287, 430, 14399, 14400, 14402, 14417, 14442, 14445, 13246, 14534, 14587, 14589, 14593, 965, 969, 14509, 447, 14597, 14000, 11103, 14070, 13232, 39, 14626, 14650, 14668, 14672, 14154, 14666, 848, 14748, 13233, 14711, 14817, 14814, 14914, 14972, 14933, 14956, 14967, 15049, 15031, 15055, 15116, 13003, 15141, 15186, 14512, 15220, 15304, 15271, 14843, 526, 15558, 15539, 15583, 15635, 15664, 15657, 15663, 643, 15683, 947, 13138, 15738, 15724, 15761, 15741, 15798, 15861, 664, 899, 16014, 16007, 16011, 16020, 16215, 992, 16332, 12612, 163, 797, 616, 160, 407, 817, 775, 222, 16442, 13268, 968, 13700, 12380, 13015, 13184, 13279, 16066, 15765, 16237, 13115, 393, 789, 14991, 14691, 12760, 9123, 15075, 11949, 622, 15709, 597, 16490, 16526, 16503, 16501, 16498, 13131316595, 16586, 16444, 12197, 16549, 16558, 16618, 16619, 13743, 13228, 16673, 13741, 16651666710, 13618, 16574, 16737, 13132, 523, 16747, 3556, 16161616758, 16718, 16816, 16795, 16842, 946, 16864, 16909, 16897, 16922, 16915, 17000, 16987, 17020, 554, 17109, 17106, 17196, 17236, 17294, 961, 17313, 17310, 17171717347, 17334, 174217425, 17523, 17504, 17462, 17465, 13223, 17558, 17563, 17589, 17570, 579, 17624, 549, 17600, 17687, 17728, 559, 15559, 17649, 12759, 17789, 17791777830, 17832, 17798, 17883, 9156, 17867, 17066, 10980, 11111, 17907, 12308, 179581795, 16953, 17993, 14051, 13118, 159, 945, 18062, 17320, 14973, 17672, 16165, 883, 13239, 17848, 17345, 18249, 18229, 18255, 4, 18277, 18284, 11, 18223, 6156156, 165, 18375, 18367, 12756, 891, 948, 13226, 13622, 145, 18270, 18269, 412, 14595, 12943, 532, 939, 642, 936, 18410];

        // 统计时间段的
        $ret = $this->request->param('ret');

        // $q = \think\Db::name('user_v2')
        //     ->where(['validated' => 1])
        //     ->field('COUNT(*)')
        //     ->where(function($q){
        //         $q->where('`qrcode` = `a`.`qrcode`')->where('`customer_number` = `a`.`customer_number`');
        //     });

        // if (!empty($ret)) {
        //     $q = $q->whereTime('create_time', $ret);
        // }

        // $q = $q->buildSql();
        $count = \think\Db::name('user_v2')
            ->alias('a')
            ->where('js_uid', 'IN', $js_uids)
            ->field("qrcode,customer_number,COUNT(*) as num")
            ->group('qrcode,customer_number');

        if (!empty($ret)) {
            $count = $count->whereTime('create_time', $ret);
        }

        $count = $count->select(false);

        // echo $count;exit();
        var_dump($count);exit();

        $file = Excel::getExcelByData($count, $keys, '推广数据统计导出_' . $ret . '_' . date('Y_m_d', time()));

        if (!$file) {
            return $this->ajaxResponse([], '提取失败', 500);
        }

        return $this->ajaxResponse(['path' => $file, 'host' => $this->request->domain()]);
    }
}
