<?php
namespace app\admin\controller;

use app\common\model\CloudServerBannerModel;
use app\admin\validate\CloudServerBannerValidate;

/**
 * @title 模板控制器-云服务器轮播图
 * @desc 模板控制器-云服务器轮播图
 * @use app\admin\controller\CloudServerBannerController
 */
class CloudServerBannerController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new CloudServerBannerValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 云服务器轮播图列表
     * @desc 云服务器轮播图列表
     * @author theworld
     * @version v1
     * @url /admin/v1/cloud_server_banner
     * @method GET
     * @return array list - desc:轮播图列表
     * @return int list[].id - desc:轮播图ID
     * @return string list[].img - desc:图片
     * @return string list[].url - desc:跳转链接
     * @return int list[].start_time - desc:展示开始时间
     * @return int list[].end_time - desc:展示结束时间
     * @return int list[].show - desc:是否展示 0否 1是
     * @return string list[].notes - desc:备注
     */
    public function list()
    {
        // 实例化模型类
        $CloudServerBannerModel = new CloudServerBannerModel();

        // 云服务器轮播图列表
        $data = $CloudServerBannerModel->bannerList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 添加云服务器轮播图
     * @desc 添加云服务器轮播图
     * @author theworld
     * @version v1
     * @url /admin/v1/cloud_server_banner
     * @method POST
     * @param string img - desc:图片 validate:required
     * @param string url - desc:跳转链接 validate:required
     * @param int start_time - desc:展示开始时间 validate:required
     * @param int end_time - desc:展示结束时间 validate:required
     * @param int show - desc:是否展示 0否 1是 validate:required
     * @param string notes - desc:备注 validate:optional
     */
    public function create()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $CloudServerBannerModel = new CloudServerBannerModel();
        
        // 添加云服务器轮播图
        $result = $CloudServerBannerModel->createBanner($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 修改云服务器轮播图
     * @desc 修改云服务器轮播图
     * @author theworld
     * @version v1
     * @url /admin/v1/cloud_server_banner/:id
     * @method PUT
     * @param int id - desc:轮播图ID validate:required
     * @param string img - desc:图片 validate:required
     * @param string url - desc:跳转链接 validate:required
     * @param int start_time - desc:展示开始时间 validate:required
     * @param int end_time - desc:展示结束时间 validate:required
     * @param int show - desc:是否展示 0否 1是 validate:required
     * @param string notes - desc:备注 validate:optional
     */
    public function update()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $CloudServerBannerModel = new CloudServerBannerModel();
        
        // 修改云服务器轮播图
        $result = $CloudServerBannerModel->updateBanner($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除云服务器轮播图
     * @desc 删除云服务器轮播图
     * @author theworld
     * @version v1
     * @url /admin/v1/cloud_server_banner/:id
     * @method DELETE
     * @param int id - desc:轮播图ID validate:required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $CloudServerBannerModel = new CloudServerBannerModel();
        
        // 删除云服务器轮播图
        $result = $CloudServerBannerModel->deleteBanner($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 展示云服务器轮播图
     * @desc 展示云服务器轮播图
     * @author theworld
     * @version v1
     * @url /admin/v1/cloud_server_banner/:id/show
     * @method PUT
     * @param int id - desc:轮播图ID validate:required
     * @param int show - desc:是否展示 0否 1是 validate:required
     */
    public function show()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('show')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $CloudServerBannerModel = new CloudServerBannerModel();
        
        // 展示云服务器轮播图
        $result = $CloudServerBannerModel->showBanner($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 云服务器轮播图排序
     * @desc 云服务器轮播图排序
     * @author theworld
     * @version v1
     * @url /admin/v1/cloud_server_banner/order
     * @method PUT
     * @param array id - desc:轮播图ID数组 validate:required
     */
    public function order()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $CloudServerBannerModel = new CloudServerBannerModel();
        
        // 云服务器轮播图排序
        $result = $CloudServerBannerModel->orderBanner($param);

        return json($result);
    }
}