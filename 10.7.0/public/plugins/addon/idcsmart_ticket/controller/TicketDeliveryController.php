<?php
namespace addon\idcsmart_ticket\controller;

use addon\idcsmart_ticket\model\IdcsmartTicketDeliveryModel;
use addon\idcsmart_ticket\validate\TicketDeliveryValidate;
use app\common\model\UpstreamProductModel;
use app\event\controller\PluginAdminBaseController;

/**
 * @title 工单自动向上传递规则(后台)
 * @desc 工单自动向上传递规则(后台)
 * @use addon\idcsmart_ticket\controller\TicketDeliveryController
 */
class TicketDeliveryController extends PluginAdminBaseController
{
    private $validate=null;

    public function initialize()
    {
        parent::initialize();
        $this->validate = new TicketDeliveryValidate();
    }

    /**
     * 时间 2024-06-13
     * @title 工单传递供应商商品列表
     * @desc 工单传递供应商商品列表
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/supplier/product
     * @method  GET
     * @return array list - 工单传递供应商商品列表
     * @return int list[].supplier_name - 供应商名称
     * @return array list[].group - 分组
     * @return string list[].group[].product_group_name - 分组名称
     * @return array list[].group[].products - 商品
     * @return int list[].group[].products[].product_id - 商品ID
     * @return int list[].group[].products[].supplier_id - 供应商ID
     * @return int list[].group[].products[].product_group_id - 商品分组ID
     * @return string list[].group[].products[].product_name - 商品名称
     * @return string list[].group[].products[].product_group_name - 分组名称
     * @return string list[].group[].products[].supplier_name - 供应商名称
     */
    public function supplierProductList()
    {
        $UpstreamProductModel = new UpstreamProductModel();

        $products = $UpstreamProductModel->alias('up')
            ->field('up.product_id,up.supplier_id,p.product_group_id,p.name product_name,
            pg.name product_group_name,s.name supplier_name')
            ->leftJoin('supplier s','up.supplier_id=s.id')
            ->leftJoin('product p','up.product_id=p.id')
            ->leftJoin('product_group pg','p.product_group_id=pg.id')
            ->where('p.product_group_id','>',0)
            ->group('up.product_id')
            ->select()
            ->toArray();

        $filter = [];

        foreach ($products as $product){
            if (!isset($filter[$product['supplier_id']])){
                $filter[$product['supplier_id']] = [];
                $filter[$product['supplier_id']][] = $product;
            }else{
                $filter[$product['supplier_id']][] = $product;
            }
        }

        $filter2 = [];

        foreach ($filter as $key=>$value){
            if (!empty($value)){
                $group = [];

                foreach ($value as $item){
                    if (!isset($group[$item['product_group_id']])){
                        $group[$item['product_group_id']] = [];
                        $group[$item['product_group_id']][] = $item;
                    }else{
                        $group[$item['product_group_id']][] = $item;
                    }
                }

                $groupFilter = [];

                foreach ($group as $key2=>$value2){
                    if (!empty($value2)){
                        $groupFilter[] = [
                            'product_group_name' => $value2[0]['product_group_name'],
                            'products' => $value2
                        ];
                    }
                }

                $filter2[] = [
                    'supplier_name' => $value[0]['supplier_name'],
                    'group' => $groupFilter
                ];
            }
        }

        return json([
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'list' => $filter2
            ]
        ]);
    }

    /**
     * 时间 2024-06-13
     * @title 工单传递规则列表
     * @desc 工单传递规则列表
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/delivery
     * @method  GET
     * @return array list - 工单传递规则列表
     * @return int list[].id - 规则ID
     * @return int list[].product_name - 商品名称
     * @return int list[].type_name - 类型名称
     * @return int list[].blocked_words - 屏蔽词，逗号分隔
     */
    public function ticketDeliveryList()
    {
        $param = $this->request->param();

        $IdcsmartTicketDeliveryModel = new IdcsmartTicketDeliveryModel();

        $IdcsmartTicketDeliveryModel->isAdmin = true;

        $result = $IdcsmartTicketDeliveryModel->ticketDeliveryList($param);

        return json($result);
    }

    /**
     * 时间 2024-06-13
     * @title 工单传递规则详情
     * @desc 工单传递规则详情
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/delivery/:id
     * @method  GET
     * @param int id - 工单传递规则ID required
     * @return object ticket_delivery - 工单传递规则详情
     * @return int ticket_delivery.product_id - 商品ID
     * @return string ticket_delivery.ticket_type_id - 类型ID
     * @return string ticket_delivery.blocked_words - 屏蔽词，逗号分隔
     */
    public function index()
    {
        $param = $this->request->param();

        $IdcsmartTicketDeliveryModel = new IdcsmartTicketDeliveryModel();

        $result = $IdcsmartTicketDeliveryModel->indexTicketDelivery(intval($param['id']));

        return json($result);
    }

    /**
     * 时间 2024-06-13
     * @title 创建工单传递规则
     * @desc 创建工单传递规则
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/delivery
     * @method  POST
     * @param int ticket_type_id - 工单类型ID required
     * @param array product_ids - 商品ID数组 required
     * @param string blocked_words - 屏蔽词
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartTicketDeliveryModel = new IdcsmartTicketDeliveryModel();

        $result = $IdcsmartTicketDeliveryModel->createTicketDelivery($param);

        return json($result);
    }

    /**
     * 时间 2024-06-13
     * @title 编辑工单传递规则
     * @desc 编辑工单传递规则
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/delivery/:id
     * @method  PUT
     * @param int id - 工单传递规则ID required
     * @param int product_id - 商品ID required
     * @param int ticket_type_id - 类型ID required
     * @param string blocked_words - 屏蔽词
     */
    public function update()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartTicketDeliveryModel = new IdcsmartTicketDeliveryModel();

        $result = $IdcsmartTicketDeliveryModel->updateTicketDelivery($param);

        return json($result);
    }

    /**
     * 时间 2024-06-13
     * @title 删除工单传递规则
     * @desc 删除工单传递规则
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/delivery/:id
     * @method  DELETE
     * @param int id - 工单传递规则ID required
     */
    public function delete()
    {
        $param = $this->request->param();

        $IdcsmartTicketDeliveryModel = new IdcsmartTicketDeliveryModel();

        $result = $IdcsmartTicketDeliveryModel->deleteTicketDelivery(intval($param['id']));

        return json($result);
    }

}