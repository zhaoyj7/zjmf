<?php
namespace app\admin\controller;

use think\response\Json;

use app\common\model\MfCloudDataCenterMapGroupModel;
use app\admin\validate\MfCloudDataCenterMapGroupValidate;

/**
 * @title 魔方云区域组模型控制器
 * @desc  魔方云区域组模型控制器
 * @use app\admin\controller\MfCloudDataCenterMapGroupController
 */
class MfCloudDataCenterMapGroupController extends AdminBaseController
{
    /**
     * 时间 2024-11-17
     * @title 魔方云区域组列表
     * @desc 魔方云区域组列表
     * @url /admin/v1/mf_cloud_data_center_map_group
     * @method GET
     * @author hh
     * @version v1
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string name - desc:搜索名称 validate:optional
     * @param string keywords - desc:搜索描述/商品名称/区域名称 validate:optional
     * @param string type - desc:类型 local=本地 upstream=代理 validate:optional
     * @return array list - desc:区域组列表
     * @return int list[].id - desc:区域组ID
     * @return string list[].name - desc:区域组名称
     * @return string list[].description - desc:区域组描述
     * @return array list[].product - desc:商品列表
     * @return int list[].product[].id - desc:商品ID
     * @return string list[].product[].name - desc:商品名称
     * @return array list[].product[].data_center - desc:数据中心列表
     * @return int list[].product[].data_center[].id - desc:数据中心ID
     * @return string list[].product[].data_center[].name - desc:数据中心名称
     * @return int count - desc:总数
     */
    public function groupList(): Json
    {
        $param = $this->request->param();
        
        // 实例化模型类
        $MfCloudDataCenterMapGroupModel = new MfCloudDataCenterMapGroupModel();

        $data = $MfCloudDataCenterMapGroupModel->groupList($param);

        $result = [
            'status' => 200, 
            'msg' => lang('success_message'), 
            'data' => $data,
        ];

        return json($result);
    }

    /**
     * 时间 2024-11-17
     * @title 创建魔方云区域组
     * @desc 创建魔方云区域组
     * @url /admin/v1/mf_cloud_data_center_map_group
     * @method POST
     * @author hh
     * @version v1
     * @param string name - desc:区域组名称 validate:required
     * @param string description - desc:区域组描述 validate:optional
     * @param array data_center - desc:数据中心配置 validate:required
     * @param int data_center[].product_id - desc:商品ID validate:required
     * @param array data_center[].data_center_id - desc:数据中心ID数组 validate:required
     * @return int id - desc:区域组ID
     */
    public function groupCreate(): Json
    {
        $param = $this->request->param();
        
        $MfCloudDataCenterMapGroupValidate = new MfCloudDataCenterMapGroupValidate();
        // 参数验证
        if (!$MfCloudDataCenterMapGroupValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($MfCloudDataCenterMapGroupValidate->getError())]);
        }

        // 实例化模型类
        $MfCloudDataCenterMapGroupModel = new MfCloudDataCenterMapGroupModel();

        $result = $MfCloudDataCenterMapGroupModel->groupCreate($param);
        return json($result);
    }

    /**
     * 时间 2024-11-17
     * @title 修改魔方云区域组
     * @desc 修改魔方云区域组
     * @url /admin/v1/mf_cloud_data_center_map_group/:id
     * @method PUT
     * @author hh
     * @version v1
     * @param int id - desc:区域组ID validate:required
     * @param string name - desc:区域组名称 validate:required
     * @param string description - desc:区域组描述 validate:optional
     * @param array data_center - desc:数据中心配置 validate:required
     * @param int data_center[].product_id - desc:商品ID validate:required
     * @param array data_center[].data_center_id - desc:数据中心ID数组 validate:required
     */
    public function groupUpdate(): Json
    {
        $param = $this->request->param();
        
        $MfCloudDataCenterMapGroupValidate = new MfCloudDataCenterMapGroupValidate();
        // 参数验证
        if (!$MfCloudDataCenterMapGroupValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($MfCloudDataCenterMapGroupValidate->getError())]);
        }

        // 实例化模型类
        $MfCloudDataCenterMapGroupModel = new MfCloudDataCenterMapGroupModel();

        $result = $MfCloudDataCenterMapGroupModel->groupUpdate($param);
        return json($result);
    }

    /**
     * 时间 2024-11-17
     * @title 删除魔方云区域组
     * @desc 删除魔方云区域组
     * @url /admin/v1/mf_cloud_data_center_map_group/:id
     * @method DELETE
     * @author hh
     * @version v1
     * @param int id - desc:区域组ID validate:required
     */
    public function groupDelete(): Json
    {
        $param = $this->request->param();
        
        $MfCloudDataCenterMapGroupValidate = new MfCloudDataCenterMapGroupValidate();
        // 参数验证
        if (!$MfCloudDataCenterMapGroupValidate->scene('delete')->check($param)){
            return json(['status' => 400 , 'msg' => lang($MfCloudDataCenterMapGroupValidate->getError())]);
        }

        // 实例化模型类
        $MfCloudDataCenterMapGroupModel = new MfCloudDataCenterMapGroupModel();

        $result = $MfCloudDataCenterMapGroupModel->groupDelete($param);
        return json($result);
    }

    /**
     * 时间 2025-11-18
     * @title 根据商品ID获取数据中心
     * @desc 根据商品ID获取数据中心
     * @url /admin/v1/product/:id/mf_cloud_data_center
     * @method GET
     * @author hh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @return array list - desc:数据中心列表
     * @return int list[].id - desc:数据中心ID
     * @return string list[].name - desc:数据中心名称
     */
    public function getProductDataCenter(): Json
    {
        $param = $this->request->param();
        
        // 验证商品ID
        if (empty($param['id']) || !is_numeric($param['id']) || $param['id'] <= 0) {
            return json(['status' => 400, 'msg' => lang('product_id_error')]);
        }

        // 实例化模型类
        $MfCloudDataCenterMapGroupModel = new MfCloudDataCenterMapGroupModel();

        $data = $MfCloudDataCenterMapGroupModel->getProductDataCenter($param);

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data,
        ];
        return json($result);
    }
}

