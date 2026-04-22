<?php
namespace app\admin\controller;

use app\common\model\SslCertificateProductModel;
use app\admin\validate\SslCertificateProductValidate;

/**
 * @title 模板控制器-SSL证书商品
 * @desc 模板控制器-SSL证书商品
 * @use app\admin\controller\SslCertificateProductController
 */
class SslCertificateProductController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new SslCertificateProductValidate();
    }

    /**
     * 时间 2024-04-02
     * @title SSL证书商品列表
     * @desc SSL证书商品列表
     * @author theworld
     * @version v1
     * @url /admin/v1/ssl_certificate_product
     * @method GET
     * @return array list - desc:商品列表
     * @return int list[].id - desc:商品ID
     * @return string list[].title - desc:标题
     * @return string list[].description - desc:描述
     * @return string list[].price - desc:价格
     * @return string list[].price_unit - desc:价格单位 month月 year年
     * @return int list[].product_id - desc:关联商品ID
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $SslCertificateProductModel = new SslCertificateProductModel();

        // SSL证书商品列表
        $data = $SslCertificateProductModel->productList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 创建SSL证书商品
     * @desc 创建SSL证书商品
     * @author theworld
     * @version v1
     * @url /admin/v1/ssl_certificate_product
     * @method POST
     * @param string title - desc:标题 validate:required
     * @param string description - desc:描述 validate:required
     * @param float price - desc:价格 validate:required
     * @param string price_unit - desc:价格单位 month月 year年 validate:required
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
        $SslCertificateProductModel = new SslCertificateProductModel();
        
        // 创建SSL证书商品
        $result = $SslCertificateProductModel->createProduct($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 编辑SSL证书商品
     * @desc 编辑SSL证书商品
     * @author theworld
     * @version v1
     * @url /admin/v1/ssl_certificate_product/:id
     * @method PUT
     * @param int id - desc:商品ID validate:required
     * @param string title - desc:标题 validate:required
     * @param string description - desc:描述 validate:required
     * @param float price - desc:价格 validate:required
     * @param string price_unit - desc:价格单位 month月 year年 validate:required
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
        $SslCertificateProductModel = new SslCertificateProductModel();
        
        // 编辑SSL证书商品
        $result = $SslCertificateProductModel->updateProduct($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除SSL证书商品
     * @desc 删除SSL证书商品
     * @author theworld
     * @version v1
     * @url /admin/v1/ssl_certificate_product/:id
     * @method DELETE
     * @param int id - desc:商品ID validate:required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $SslCertificateProductModel = new SslCertificateProductModel();
        
        // 删除SSL证书商品
        $result = $SslCertificateProductModel->deleteProduct($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title SSL证书商品排序
     * @desc SSL证书商品排序
     * @author theworld
     * @version v1
     * @url /admin/v1/ssl_certificate_product/order
     * @method PUT
     * @param array id - desc:商品ID数组 validate:required
     */
    public function order()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $SslCertificateProductModel = new SslCertificateProductModel();
        
        // SSL证书商品排序
        $result = $SslCertificateProductModel->productOrder($param);

        return json($result);
    }
}