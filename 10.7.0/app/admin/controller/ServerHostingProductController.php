<?php
namespace app\admin\controller;

use app\common\model\ServerHostingProductModel;
use app\admin\validate\ServerHostingProductValidate;

/**
 * @title 模板控制器-服务器托管商品
 * @desc 模板控制器-服务器托管商品
 * @use app\admin\controller\ServerHostingProductController
 */
class ServerHostingProductController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ServerHostingProductValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 服务器托管商品列表
     * @desc 服务器托管商品列表
     * @author theworld
     * @version v1
     * @url /admin/v1/server_hosting_product
     * @method GET
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序 id validate:optional
     * @param string sort - desc:升/降序 asc desc validate:optional
     * @return array list - desc:商品列表
     * @return int list[].id - desc:商品ID
     * @return int list[].area_id - desc:区域ID
     * @return string list[].first_area - desc:所属区域
     * @return string list[].title - desc:标题
     * @return string list[].region - desc:地域
     * @return string list[].ip_num - desc:IP数量
     * @return string list[].bandwidth - desc:带宽
     * @return string list[].defense - desc:防御
     * @return string list[].bandwidth_price - desc:带宽价格
     * @return string list[].bandwidth_price_unit - desc:带宽价格单位 month/M/月 year/M/年
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
        $ServerHostingProductModel = new ServerHostingProductModel();

        // 服务器托管商品列表
        $data = $ServerHostingProductModel->productList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 创建服务器托管商品
     * @desc 创建服务器托管商品
     * @author theworld
     * @version v1
     * @url /admin/v1/server_hosting_product
     * @method POST
     * @param int area_id - desc:区域ID validate:required
     * @param string title - desc:标题 validate:required
     * @param string region - desc:地域 validate:required
     * @param string ip_num - desc:IP数量 validate:required
     * @param string bandwidth - desc:带宽 validate:required
     * @param string defense - desc:防御 validate:required
     * @param float bandwidth_price - desc:带宽价格 validate:required
     * @param string bandwidth_price_unit - desc:带宽价格单位 month/M/月 year/M/年 validate:required
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
        $ServerHostingProductModel = new ServerHostingProductModel();
        
        // 创建服务器托管商品
        $result = $ServerHostingProductModel->createProduct($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 编辑服务器托管商品
     * @desc 编辑服务器托管商品
     * @author theworld
     * @version v1
     * @url /admin/v1/server_hosting_product/:id
     * @method PUT
     * @param int id - desc:商品ID validate:required
     * @param int area_id - desc:区域ID validate:required
     * @param string title - desc:标题 validate:required
     * @param string region - desc:地域 validate:required
     * @param string ip_num - desc:IP数量 validate:required
     * @param string bandwidth - desc:带宽 validate:required
     * @param string defense - desc:防御 validate:required
     * @param float bandwidth_price - desc:带宽价格 validate:required
     * @param string bandwidth_price_unit - desc:带宽价格单位 month/M/月 year/M/年 validate:required
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
        $ServerHostingProductModel = new ServerHostingProductModel();
        
        // 编辑服务器托管商品
        $result = $ServerHostingProductModel->updateProduct($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除服务器托管商品
     * @desc 删除服务器托管商品
     * @author theworld
     * @version v1
     * @url /admin/v1/server_hosting_product/:id
     * @method DELETE
     * @param int id - desc:商品ID validate:required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $ServerHostingProductModel = new ServerHostingProductModel();
        
        // 删除服务器托管商品
        $result = $ServerHostingProductModel->deleteProduct($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 服务器托管商品排序
     * @desc 服务器托管商品排序
     * @author theworld
     * @version v1
     * @url /admin/v1/server_hosting_product/order
     * @method PUT
     * @param array id - desc:商品ID数组 validate:required
     */
    public function order()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $ServerHostingProductModel = new ServerHostingProductModel();
        
        // 服务器托管商品排序
        $result = $ServerHostingProductModel->productOrder($param);

        return json($result);
    }
}