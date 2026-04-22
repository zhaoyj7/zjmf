<?php
namespace app\admin\controller;

use app\common\model\CloudServerProductModel;
use app\admin\validate\CloudServerProductValidate;

/**
 * @title 模板控制器-云服务器商品
 * @desc 模板控制器-云服务器商品
 * @use app\admin\controller\CloudServerProductController
 */
class CloudServerProductController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new CloudServerProductValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 云服务器商品列表
     * @desc 云服务器商品列表
     * @author theworld
     * @version v1
     * @url /admin/v1/cloud_server_product
     * @method GET
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序 id validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:商品列表
     * @return int list[].id - desc:商品ID
     * @return int list[].area_id - desc:区域ID
     * @return string list[].first_area - desc:一级区域
     * @return string list[].second_area - desc:二级区域
     * @return string list[].title - desc:标题
     * @return string list[].description - desc:描述
     * @return string list[].cpu - desc:处理器
     * @return string list[].memory - desc:内存
     * @return string list[].system_disk - desc:系统盘
     * @return string list[].bandwidth - desc:带宽
     * @return string list[].duration - desc:时长
     * @return string list[].tag - desc:标签
     * @return string list[].original_price - desc:原价
     * @return string list[].original_price_unit - desc:原价单位 month月 year年
     * @return string list[].selling_price - desc:售价
     * @return string list[].selling_price_unit - desc:售价单位 month月 year年
     * @return int list[].product_id - desc:关联商品ID
     * @return int count - desc:商品数量
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $CloudServerProductModel = new CloudServerProductModel();

        // 云服务器商品列表
        $data = $CloudServerProductModel->productList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 创建云服务器商品
     * @desc 创建云服务器商品
     * @author theworld
     * @version v1
     * @url /admin/v1/cloud_server_product
     * @method POST
     * @param int area_id - desc:区域ID validate:required
     * @param string title - desc:标题 validate:required
     * @param string description - desc:描述 validate:required
     * @param string cpu - desc:处理器 validate:required
     * @param string memory - desc:内存 validate:required
     * @param string system_disk - desc:系统盘 validate:required
     * @param string bandwidth - desc:带宽 validate:required
     * @param string duration - desc:时长 validate:required
     * @param string tag - desc:标签 validate:required
     * @param float original_price - desc:原价 validate:required
     * @param string original_price_unit - desc:原价单位 month月 year年 validate:required
     * @param float selling_price - desc:售价 validate:required
     * @param string selling_price_unit - desc:售价单位 month月 year年 validate:required
     * @param int product_id - desc:关联商品ID validate:required
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
        $CloudServerProductModel = new CloudServerProductModel();
        
        // 创建云服务器商品
        $result = $CloudServerProductModel->createProduct($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 编辑云服务器商品
     * @desc 编辑云服务器商品
     * @author theworld
     * @version v1
     * @url /admin/v1/cloud_server_product/:id
     * @method PUT
     * @param int id - desc:商品ID validate:required
     * @param int area_id - desc:区域ID validate:required
     * @param string title - desc:标题 validate:required
     * @param string description - desc:描述 validate:required
     * @param string cpu - desc:处理器 validate:required
     * @param string memory - desc:内存 validate:required
     * @param string system_disk - desc:系统盘 validate:required
     * @param string bandwidth - desc:带宽 validate:required
     * @param string duration - desc:时长 validate:required
     * @param string tag - desc:标签 validate:required
     * @param float original_price - desc:原价 validate:required
     * @param string original_price_unit - desc:原价单位 month月 year年 validate:required
     * @param float selling_price - desc:售价 validate:required
     * @param string selling_price_unit - desc:售价单位 month月 year年 validate:required
     * @param int product_id - desc:关联商品ID validate:required
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
        $CloudServerProductModel = new CloudServerProductModel();
        
        // 编辑云服务器商品
        $result = $CloudServerProductModel->updateProduct($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除云服务器商品
     * @desc 删除云服务器商品
     * @author theworld
     * @version v1
     * @url /admin/v1/cloud_server_product/:id
     * @method DELETE
     * @param int id - desc:商品ID validate:required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $CloudServerProductModel = new CloudServerProductModel();
        
        // 删除云服务器商品
        $result = $CloudServerProductModel->deleteProduct($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 云服务器商品排序
     * @desc 云服务器商品排序
     * @author theworld
     * @version v1
     * @url /admin/v1/cloud_server_product/order
     * @method PUT
     * @param array id - desc:商品ID数组 validate:required
     */
    public function order()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $CloudServerProductModel = new CloudServerProductModel();
        
        // 云服务器商品排序
        $result = $CloudServerProductModel->productOrder($param);

        return json($result);
    }
}