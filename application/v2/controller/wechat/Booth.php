<?php

namespace app\v2\controller\wechat;

use think\Db;
use think\Cache;
use app\v2\model\BoothV2;
use app\v2\model\BoothCollection as Collection;

/**
 * 展位信息
 */
class Booth extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $request = $this->request;

        $page = $request->param('page/d', 1);
        $limit = $request->param('limit/d', 10);

        // 限制limit最大为100
        $limit = $limit < 100 ? $limit : 100;
        $list = BoothV2::scope('filter', $request)
                ->with(['exhibition'])
                ->limit($limit)
                ->order('id desc')
                ->page($page);

        $list = $list->select();
        if (empty($list)) {
            return $this->ajaxResponse([], '列表数据为空', 300);
        }

        for ($i = 0; $i < count($list); $i++) {
            $list[$i] = $list[$i]->toArray();
        }

        // 统计总条数
        $total = BoothV2::scope('filter', $request)->count();
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
    public function read()
    {
        $request = $this->request;
        $id = $request->param('id/d');

        $data = BoothV2::where(['id' => $id])
                ->with(['exhibition'])
                ->find();
        if (empty($data)) {
            return $this->ajaxResponse([], '不存在此资源', 300);
        }

        $data['collected'] = 0;
        $user_id = $request->param('user_id/d');
        if ($user_id) {
            if (!empty(Collection::where(['user_id' => $user_id, 'booth_id' => $id])->find())) {
                $data['collected'] = 1;
            }
        }

        return $this->ajaxResponse($data->toArray());
    }

    /**
     * 获取展位的商品
     */
    public function products()
    {
        $request = $this->request;
        $booth_id = $request->param('booth_id/d');

        $data = BoothV2::where(['id' => $booth_id])->field(['id', 'products'])->find();
        if (empty($data->products)) {
            return $this->ajaxResponse();
        }

        $arr = [];
        for ($i = 0; $i < count($data->products); $i++) {
            $tmp = $this->getProdasInfoById($data->products[$i]);
            if (!empty($tmp)) $arr[] = $tmp;
        }

        return $this->ajaxResponse($arr);
    }

    /**
     * 根据产品ID获取产品的一些信息
     */
    private function getProdasInfoById($id)
    {
        // 看看有缓存没
        $pro = Cache::get('pro_' . $id);
        if ($pro) return json_decode($pro);

        // 获取一个管理员token
        $token = self::getAdminToken();
        if (empty($token)) {
            return null;
        }

        if (is_numeric($id)) {
            $url = config('v2.jsw_product_api_id') . '/?id=' . $id;
        } else {
            $url = config('v2.jsw_product_api') . '/?goods_code=' . $id;
        }

        // 发起请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization:bearer ' . $token]);
        $resp = curl_exec($ch);
        curl_close($ch);

        if (empty($resp)) return null;

        $data = json_decode($resp);
        if (!isset($data->errCode) || $data->errCode != 0) {
            return null;
        }

        $temp = $data->content;
        if (is_numeric($id)) {
            $temp->is_user_upload = 1;
        }

        Cache::set('pro_' . $id, json_encode($temp), 3600 * 24 * 5);

        return $temp;
    }

    /**
     * 获取酒商网管理员Token
     */
    public static function getAdminToken()
    {
        $token = Cache::get('jsw_admin_token');
        if (empty($token)) {
            $ret = Sign::getJSWToken(config('v2.username'), config('v2.password'));
            if (!isset($ret['access_token'])) {
                return null;
            }
            $token = $ret['access_token'];
            Cache::set('jsw_admin_token', $token, 3600 * 24 * 2);
        }
        return $token;
    }
}
