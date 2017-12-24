<?php

namespace app\v2\controller\wechat;

require_once VENDOR_PATH . 'autoload.php';

use think\Request;
use think\Cache;
use app\v2\model\UserV2;
use app\v2\model\BoothV2;
use OSS\OssClient;
use OSS\Core\OssException;

/**
 * 用户相关操作
 */
class User extends Auth
{

    /**
     * 获取用户信息
     */
    public function info()
    {
        $data = UserV2::where(['id' => $this->user_id])
                ->with(['booth' => ['exhibition']])
                ->find();

        $data['token'] = $this->getToken($data['id']);

        return $this->ajaxResponse($data);
    }

    /**
     * 办展用户添加一个展位
     */
    public function addbooth()
    {
        $data = $this->request->param();
        if (empty($data)) {
            return $this->ajaxResponse([], '缺少参数', 400);
        }

        $vk = ['company', 'introduce', 'exhibition_hotel_id', 'floor', 'exhibition_code', 'contact', 'address', 'img'];
        foreach ($vk as $value) {
            if (empty($data[$value])) {
                return $this->ajaxResponse([], '参数不能为空', 400);
            }
        }

        $user = UserV2::get($this->user_id);
        if (empty($user)) {
            return $this->ajaxResponse([], '服务器出错', 500);
        }

        if ($user->validated != 1) {
            return $this->ajaxResponse([], '您暂时不能添加', 500);
        }

        $data['tel'] = $user->tel;
        $data['status'] = 0;

        $result = BoothV2::create($data);
        if (empty($result)) {
            return $this->ajaxResponse([], '创建失败', 500);
        }

        return $this->ajaxResponse($result);
    }

    /**
     * 用户的资质认证
     */
    public function aptitudeauth()
    {
        $user = UserV2::get($this->user_id);
        if ($user->validated == 1) {
            return $this->ajaxResponse([], '您已通过认证或无权限认证', 400);
        }

        // 公司名称
        $companyName = $this->request->param('companyName/s');
        // 公司营业执照
        $companyLicenceUrl = $this->request->param('companyLicenceUrl/s');
        // 身份证号码
        // $cardNum = $this->request->param('cardNum/s');
        // 用户手持身份证图片
        $personCardImageUrl = $this->request->param('personCardImageUrl/s');

        // 缺一不可
        if (empty($companyName) ||
            empty($companyLicenceUrl) ||
            empty($personCardImageUrl)) {
            return $this->ajaxResponse([], '参数不能为空', 400);
        }

        // 获取一个管理员token
        $token = Booth::getAdminToken();
        if (empty($token)) {
            return $this->ajaxResponse([], '服务器配置问题，请联系管理员', 505);
        }

        // 发起请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, config('v2.jsw_company_info_detail_api') . '/?name=' . urlencode($companyName));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization:bearer ' . $token]);
        $info = json_decode(curl_exec($ch));
        curl_close($ch);

        if (empty($info)) {
            return $this->ajaxResponse($info, '未找到相应公司信息', 400);
        }

        // 发起入驻请求
        /*
        $data = [
            'companyKeyNo' => $_info->keyNo,
            'cardNum' => $cardNum,
            'businessRegNum' => $_info->no,
            'personCardImageUrl' => $personCardImageUrl,
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, config('v2.jsw_company_enter'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization:bearer ' . $token, 'Content-Type: application/json']);
        $enter = json_decode(curl_exec($ch));
        curl_close($ch);

        if (empty($enter)) {
            return $this->ajaxResponse([], '验证失败', 400);
        }
        if ($enter->errCode != 0) {
            return $this->ajaxResponse([], $enter->msg, $enter->errCode);
        }
        */

        // 通过认证
        $user->validated = 1;
        // 更改用户身份
        $user->identity = 2;
        $user->company_info = json_encode($info);
        // $user->card_num = $cardNum;
        $user->person_card_image_url = $personCardImageUrl;
        $user->company_licence_url = $companyLicenceUrl;

        if ($user->save()) {
            return $this->ajaxResponse([], '恭喜，你已通过认证');
        }

        return $this->ajaxResponse([], '对不起，认证失败，请联系管理员', 500);
    }

    /**
     * 上传产品
     */
    public function addproduct()
    {
        $_data = [
            'cat_id' => 900,
            'goods_type' => 61,
            'spec_type' => 61,
            'is_ad' => 0,
            'role' => 2,
            'source' => 2,
            // 
            'goods_name' => '',
            // 
            'shop_price' => 0,
            // 
            'market_price' => 0,
            'brand_id' => 0,
            // 
            'original_img' => '',
            // 产品属性
            'goods_attrs' => '',
            // 产品描述
            'goods_content_mobile' => '',
        ];

        $data = $this->request->param();
        foreach ($data as $key => $value) {
            $_data[$key] = $value;
        }

        $_data['goods_attrs'] = json_decode($_data['goods_attrs']);

        // 获取一个管理员token
        $token = Booth::getAdminToken();
        if (empty($token)) {
            return $this->ajaxResponse([], '服务器配置问题，请联系管理员', 505);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, config('v2.jsw_product_add'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization:bearer ' . $token, 'Content-Type: application/json']);
        $resp = json_decode(curl_exec($ch));
        curl_close($ch);

        if (empty($resp)) {
            return $this->ajaxResponse($resp, '上传失败', 400);
        }

        if (isset($resp->errCode) && $resp->errCode != 0) {
            return $this->ajaxResponse($resp->content, $resp->msg, $resp->errCode);
        }

        $user = UserV2::where(['id' => $this->user_id])->find();
        // 拿到一个用户的展位
        $booth = BoothV2::where(['tel' => $user->tel])->find();

        $tempPro = (array)$booth->products;
        $tempPro[] = $resp->content->goods_id;

        // 更新用户的多个展位
        $booth = new BoothV2;
        $booth->save([
            'products'  => $tempPro,
        ], ['tel' => $user->tel]);

        return $this->ajaxResponse($resp->content, '上传成功，等待审核', $resp->errCode);
    }


    /**
     * 搜索品牌brandList
    public function brandList()
    {
        // 获取一个管理员token
        $token = Booth::getAdminToken();
        if (empty($token)) {
            return $this->ajaxResponse([], '服务器配置问题，请联系管理员', 505);
        }

        $query = http_build_query($this->request->param());

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, config('v2.jsw_product_brand') . '/?' . $query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization:bearer ' . $token, 'Content-Type: application/json']);
        $resp = json_decode(curl_exec($ch));
        curl_close($ch);

        if (empty($resp)) {
            return $this->ajaxResponse([], '远程服务器错误', 500);
        }

        return $this->ajaxResponse($resp->content, $resp->msg, $resp->errCode);
    }*/

    /**
     * 分类与类型ID
     */
    public function getTypeList()
    {
        $data = [
            ['name' => '白酒', 'cat_id' => 888, 'goods_type' => 61],
            ['name' => '红酒/葡萄酒', 'cat_id' => 903, 'goods_type' => 76],
            ['name' => '洋酒', 'cat_id' => 904, 'goods_type' => 77],
            ['name' => '啤酒', 'cat_id' => 905, 'goods_type' => 78],
            ['name' => '预调酒', 'cat_id' => 906, 'goods_type' => 79],
            ['name' => '保健酒', 'cat_id' => 907, 'goods_type' => 80],
            ['name' => '饮料', 'cat_id' => 908, 'goods_type' => 81],
            ['name' => '食品', 'cat_id' => 909, 'goods_type' => 82],
        ];

        return $this->ajaxResponse($data);
    }

    /**
     * 用户上传图片
     */
    public function upload()
    {
        $file = $this->request->file('file');

        if(empty($file)) {
            return $this->ajaxResponse([], '文件不能为空', 400);
        }

        $info = $file->rule('md5')->move(ROOT_PATH . 'public' . DS . 'tmp' . DS . 'uploads');
        if (empty($info)) {
            return $this->ajaxResponse(['error' => $file->getError()], '文件上传失败', 500); 
        }

        // 如果没有配置阿里云那啥
        if (empty(config('v2.accessKeyId'))) {
            $host = $this->request->domain();
            $path = 'tmp/uploads/' . $info->getSaveName();
            return $this->ajaxResponse(['host' => $host, 'path' => $path, 'url' => $host . '/' . $path]);
        }

        $path = 'wechat/uploads/' . $info->getSaveName();

        // 配置了
        try {
            $ossClient = new OssClient(config('v2.accessKeyId'), config('v2.accessKeySecret'), config('v2.endpoint'));
        } catch (OssException $e) {
            return $this->ajaxResponse([], $e->getMessage(), 600);
        }

        try{
            $ossClient->uploadFile(config('v2.bucket'), $path, $info->getPathName());
        } catch(OssException $e) {
            return $this->ajaxResponse([], $e->getMessage(), 600);
        }

        return $this->ajaxResponse(['host' => config('v2.host'), 'path' => $info->getSaveName(), 'url' => config('v2.host') . '/' . $path]);
    }
}
