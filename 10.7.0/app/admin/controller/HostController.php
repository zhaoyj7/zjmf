<?php
namespace app\admin\controller;

use app\common\model\HostModel;
use app\admin\validate\HostValidate;
use app\common\model\SelfDefinedFieldModel;
use app\common\model\HostIpModel;

/**
 * @title 产品管理
 * @desc 产品管理
 * @use app\admin\controller\HostController
 */
class HostController extends AdminBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new HostValidate();
    }

    /**
     * 时间 2022-05-13
     * @title 产品列表
     * @desc 产品列表
     * @url /admin/v1/host
     * @method GET
     * @author theworld
     * @version v1
     * @param string keywords - desc:关键字 搜索范围:产品ID 商品名称 标识 用户名 邮箱 手机号 validate:optional
     * @param string billing_cycle - desc:付款周期 validate:optional
     * @param int client_id - desc:用户ID validate:optional
     * @param int host_id - desc:产品ID validate:optional
     * @param string status - desc:状态 Unpaid未付款 Pending开通中 Active已开通 Suspended已暂停 Deleted已删除 Failed开通失败 validate:optional
     * @param string due_time - desc:到期时间 today今天内 three最近三天 seven最近七天 month最近一个月 custom自定义 expired已到期 validate:optional
     * @param int start_time - desc:开始时间 validate:optional
     * @param int end_time - desc:结束时间 validate:optional
     * @param int product_id - desc:商品ID validate:optional
     * @param string name - desc:标识 validate:optional
     * @param string username - desc:用户名 validate:optional
     * @param string email - desc:邮箱 validate:optional
     * @param string phone - desc:手机号 validate:optional
     * @param int server_id - desc:接口ID validate:optional
     * @param string first_payment_amount - desc:订购金额 validate:optional
     * @param string ip - desc:IP validate:optional
     * @param string tab - desc:状态筛选 using使用中 expiring即将到期 overdue已逾期 deleted已删除 validate:optional
     * @param int view_id - desc:视图ID validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 id renew_amount due_time first_payment_amount active_time client_id reg_time validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @param string module - desc:模块搜索 validate:optional
     * @return array list - desc:产品列表
     * @return int list[].id - desc:产品ID
     * @return int list[].client_id - desc:用户ID
     * @return int list[].client_name - desc:用户名
     * @return string list[].email - desc:邮箱
     * @return string list[].phone_code - desc:国际电话区号
     * @return string list[].phone - desc:手机号
     * @return string list[].company - desc:公司
     * @return int list[].product_id - desc:商品ID
     * @return string list[].product_name - desc:商品名称
     * @return string list[].name - desc:标识
     * @return int list[].active_time - desc:开通时间
     * @return int list[].due_time - desc:到期时间
     * @return string list[].first_payment_amount - desc:金额
     * @return string list[].billing_cycle - desc:计费方式 free免费 onetime一次 recurring_prepayment周期先付 recurring_postpaid周期后付
     * @return string list[].billing_cycle_name - desc:周期
     * @return string list[].status - desc:状态 Unpaid未付款 Pending开通中 Active已开通 Suspended已暂停 Deleted已删除 Failed开通失败
     * @return string list[].renew_amount - desc:续费金额
     * @return string list[].client_notes - desc:用户备注
     * @return int list[].ip_num - desc:IP数量
     * @return string list[].dedicate_ip - desc:主IP
     * @return string list[].assign_ip - desc:附加IP 英文逗号分隔
     * @return string list[].server_name - desc:商品接口
     * @return string list[].admin_notes - desc:管理员备注
     * @return string list[].base_price - desc:当前周期原价
     * @return int list[].client_status - desc:用户是否启用 0禁用 1正常
     * @return int list[].reg_time - desc:用户注册时间
     * @return string list[].country - desc:国家
     * @return string list[].address - desc:地址
     * @return string list[].language - desc:语言
     * @return string list[].notes - desc:备注
     * @return bool list[].certification - desc:是否实名认识 true是 false否 显示字段有certification返回
     * @return string list[].certification_type - desc:实名类型 person个人 company企业 显示字段有certification返回
     * @return string list[].client_level - desc:用户等级 显示字段有client_level返回
     * @return string list[].client_level_color - desc:用户等级颜色 显示字段有client_level返回
     * @return string list[].sale - desc:销售 显示字段有sale返回
     * @return string list[].addon_client_custom_field_[id] - desc:用户自定义字段 显示字段有addon_client_custom_field_[id]返回 [id]为用户自定义字段ID
     * @return string list[].self_defined_field_[id] - desc:商品自定义字段 显示字段有self_defined_field_[id]返回 [id]为商品自定义字段ID
     * @return string list[].base_info - desc:产品基础信息
     * @return int count - desc:产品总数
     * @return int expiring_count - desc:即将到期产品数量
     * @return string total_renew_amount - desc:总续费金额
     * @return string page_total_renew_amount - desc:当前页总续费金额
     * @return int failed_action_count - desc:手动处理产品数量
     */
	public function hostList()
    {
		// 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $HostModel = new HostModel();

        // 获取产品列表
        $data = $HostModel->hostList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}

    /**
     * 时间 2022-05-13
     * @title 产品详情
     * @desc 产品详情
     * @url /admin/v1/host/:id
     * @method GET
     * @author theworld
     * @version v1
     * @param int id - desc:产品ID validate:required
     * @return object host - desc:产品
     * @return int host.id - desc:产品ID
     * @return int host.order_id - desc:订单ID
     * @return int host.product_id - desc:商品ID
     * @return int host.server_id - desc:接口ID
     * @return string host.name - desc:标识
     * @return string host.notes - desc:备注
     * @return string host.first_payment_amount - desc:订购金额
     * @return string host.renew_amount - desc:续费金额
     * @return string host.billing_cycle - desc:计费周期
     * @return string host.billing_cycle_name - desc:模块计费周期名称
     * @return string host.billing_cycle_time - desc:模块计费周期时间
     * @return int host.active_time - desc:开通时间
     * @return int host.due_time - desc:到期时间
     * @return string host.status - desc:状态 Unpaid未付款 Pending开通中 Active已开通 Suspended已暂停 Deleted已删除 Failed开通失败 Grace宽限 Keep保留
     * @return string host.suspend_type - desc:暂停类型 overdue到期暂停 overtraffic超流暂停 certification_not_complete实名未完成 other其他
     * @return string host.suspend_reason - desc:暂停原因
     * @return string host.client_notes - desc:用户备注
     * @return int host.ratio_renew - desc:是否开启比例续费 0否 1是
     * @return string host.base_price - desc:购买周期原价
     * @return array status - desc:状态列表
     * @return string host.product_name - desc:商品名称
     * @return int host.agent - desc:代理产品 0否 1是
     * @return string host.upstream_host_id - desc:上游产品ID
     * @return string host.mode - desc:商品代理模式 only_api仅调用接口 sync同步商品
     * @return string host.keep_time_price - desc:保留期价格
     * @return string host.on_demand_flow_price - desc:按需流量价格
     * @return string host.on_demand_billing_cycle_unit - desc:出账周期单位 hour每小时 day每天 month每月
     * @return int host.on_demand_billing_cycle_day - desc:出账周期号数
     * @return string host.on_demand_billing_cycle_point - desc:出账周期时间点 如00:00
     * @return int host.addition.country_id - desc:国家ID
     * @return string host.addition.city - desc:城市
     * @return string host.addition.area - desc:区域
     * @return string host.addition.image_icon - desc:镜像图标
     * @return string host.addition.image_name - desc:镜像名称
     * @return int self_defined_field[].id - desc:自定义字段ID
     * @return string self_defined_field[].field_name - desc:字段名称
     * @return string self_defined_field[].field_type - desc:字段类型 text文本框 link链接 password密码 dropdown下拉 checkbox勾选框 textarea文本区
     * @return string self_defined_field[].description - desc:字段描述
     * @return string self_defined_field[].field_option - desc:下拉选项
     * @return int self_defined_field[].is_required - desc:是否必填 0否 1是
     * @return string self_defined_field[].value - desc:当前值
     */
	public function index()
    {
		// 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $HostModel = new HostModel();
        $SelfDefinedFieldModel = new SelfDefinedFieldModel();

        // 获取产品
        $host = $HostModel->indexHost($param['id']);
        $selfDefinedField = $SelfDefinedFieldModel->showAdminHostDetailField(['host_id'=>$param['id']]);
        if(isset($host->addition)){
            $host['addition'] = (object)$host['addition'];
        }

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'host'              => $host,
                'status'            => config('idcsmart.host_status'),
                'self_defined_field'=> $selfDefinedField,
            ]
        ];
        return json($result);
	}	

    /**
     * 时间 2022-05-13
     * @title 修改产品
     * @desc 修改产品
     * @url /admin/v1/host/:id
     * @method PUT
     * @author theworld
     * @version v1
     * @param int id - desc:产品ID validate:required
     * @param int product_id - desc:商品ID validate:required
     * @param int server_id - desc:接口 validate:optional
     * @param string name - desc:标识 validate:optional
     * @param string notes - desc:备注 validate:optional
     * @param string upstream_host_id - desc:上游产品ID validate:optional
     * @param float first_payment_amount - desc:订购金额 validate:required
     * @param float renew_amount - desc:续费金额 validate:required
     * @param string billing_cycle - desc:计费周期 validate:required
     * @param string active_time - desc:开通时间 validate:optional
     * @param string due_time - desc:到期时间 validate:optional
     * @param string status - desc:状态 Unpaid未付款 Pending开通中 Active已开通 Suspended已暂停 Deleted已删除 Failed开通失败 validate:optional
     * @param object self_defined_field - desc:自定义字段 {"5":"123"} 5是自定义字段ID 123是填写的内容 validate:optional
     * @param int ratio_renew - desc:是否开启比例续费 0否 1是 validate:optional
     * @param float base_price - desc:购买周期原价 validate:optional
     * @param object customfield - desc:自定义字段 validate:optional
     * @param float keep_time_price - desc:保留期价格 validate:optional
     * @param float on_demand_flow_price - desc:按需流量价格 validate:optional
     * @param string on_demand_billing_cycle_unit - desc:出账周期单位 hour每小时 day每天 month每月 validate:optional
     * @param int on_demand_billing_cycle_day - desc:出账周期号数 每月有效 validate:optional
     * @param string on_demand_billing_cycle_point - desc:出账周期时间点 如00:00 每天每月生效 validate:optional
     * @param int upgrade_renew_cal - desc:升降级时是否按原价处理续费金额 1是 0否默认 validate:optional
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
        $HostModel = new HostModel();
        
        // 修改产品
        $result = $HostModel->updateHost($param);

        return json($result);
	}

    /**
     * 时间 2022-05-13
     * @title 删除产品
     * @desc 删除产品
     * @url /admin/v1/host/:id
     * @method DELETE
     * @author theworld
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
	public function delete()
    {
		// 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();
        
        // 删除产品
        $result = $HostModel->deleteHost($param);

        return json($result);
	}

    /**
     * 时间 2022-05-13
     * @title 批量删除产品
     * @desc 批量删除产品
     * @url /admin/v1/host
     * @method DELETE
     * @author theworld
     * @version v1
     * @param array id - desc:产品ID validate:required
     * @param int module_delete - desc:是否执行模块删除 1是 0否 validate:required
     */
    public function batchDelete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();
        
        // 批量删除产品
        $result = $HostModel->batchDeleteHost($param);

        return json($result);
    }

    /**
     * 时间 2022-05-30
     * @title 模块开通
     * @desc 模块开通
     * @url /admin/v1/host/:id/module/create
     * @method POST
     * @author hh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function createAccount()
    {
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();
        
        $result = $HostModel->createAccount($param['id']);
        return json($result);
    }

    /**
     * 时间 2022-05-30
     * @title 模块暂停
     * @desc 模块暂停
     * @url /admin/v1/host/:id/module/suspend
     * @method POST
     * @author hh
     * @version v1
     * @param int id - desc:产品ID validate:required
     * @param string suspend_type - desc:暂停类型 overdue到期暂停 overtraffic超流暂停 certification_not_complete实名未完成 other其他 validate:required
     * @param string suspend_reason - desc:暂停原因 validate:optional
     * @param int auto_unsuspend_time - desc:自动解除暂停时间 validate:optional
     */
    public function suspendAccount()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('suspend')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $HostModel = new HostModel();
        
        $result = $HostModel->suspendAccount($param);
        return json($result);
    }

    /**
     * 时间 2022-05-30
     * @title 模块解除暂停
     * @desc 模块解除暂停
     * @url /admin/v1/host/:id/module/unsuspend
     * @method POST
     * @author hh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function unsuspendAccount()
    {
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();
        
        $result = $HostModel->unsuspendAccount($param['id']);
        return json($result);
    }

    /**
     * 时间 2022-05-30
     * @title 模块删除
     * @desc 模块删除
     * @url /admin/v1/host/:id/module/terminate
     * @method POST
     * @author hh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function terminateAccount()
    {
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();
        
        $result = $HostModel->terminateAccount($param['id']);
        return json($result);
    }

    /**
     * 时间 2022-05-30
     * @title 产品内页模块
     * @desc 产品内页模块
     * @url /admin/v1/host/:id/module
     * @method GET
     * @author hh
     * @version v1
     * @param int id - desc:产品ID validate:required
     * @return string content - desc:模块输出内容
     */
    public function adminArea()
    {
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();
        
        $result = $HostModel->adminArea($param['id']);
        return json($result);
    }

    /**
     * 时间 2022-10-26
     * @title 获取用户所有产品
     * @desc 获取用户所有产品
     * @url /admin/v1/client/:id/host/all
     * @method GET
     * @author theworld
     * @version v1
     * @param int id - desc:用户ID validate:required
     * @return array list - desc:产品列表
     * @return int list[].id - desc:产品ID
     * @return int list[].product_id - desc:商品ID
     * @return string list[].product_name - desc:商品名称
     * @return string list[].name - desc:标识
     * @return int count - desc:产品总数
     */
    public function clientHost()
    {
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();

        // 获取用户产品
        $data = $HostModel->clientHost($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2023-01-31
     * @title 模块按鈕输出
     * @desc 模块按鈕输出
     * @url /admin/v1/host/:id/module/button
     * @method GET
     * @author hh
     * @version v1
     * @param int id - desc:产品ID validate:required
     * @return string button[].type - desc:按鈕类型 暂时都是default
     * @return string button[].func - desc:按鈕功能 create开通 suspend暂停 unsuspend解除暂停 terminate删除 renew续费 sync拉取信息 push推送到下游
     * @return string button[].name - desc:名称
     */
    public function moduleButton()
    {
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();

        $result = $HostModel->moduleAdminButton($param);
        return json($result);
    }

    /**
     * 时间 2023-04-14
     * @title 产品内页模块输入框输出
     * @desc 产品内页模块输入框输出
     * @url /admin/v1/host/:id/module/field
     * @method GET
     * @author hh
     * @version v1
     * @param int id - desc:产品ID validate:required
     * @return string [].name - desc:配置小标题
     * @return string [].field[].name - desc:名称
     * @return string [].field[].key - desc:标识 不要重复
     * @return string [].field[].value - desc:当前值
     * @return bool [].field[].disable - desc:状态 false可修改 true不可修改
     */
    public function moduleField()
    {
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();

        $result = $HostModel->moduleField($param['id']);
        return json($result);
    }

    /**
     * 时间 2024-01-10
     * @title 产品IP详情
     * @desc 产品IP详情
     * @url /admin/v1/host/:id/ip
     * @method GET
     * @author hh
     * @version v1
     * @param int id - desc:产品ID validate:required
     * @return string dedicate_ip - desc:主IP
     * @return string assign_ip - desc:附加IP 英文逗号分隔
     * @return int ip_num - desc:IP数量
     */
    public function hostIpIndex()
    {
        $param = $this->request->param();

        $HostIpModel = new HostIpModel();

        $data = $HostIpModel->getHostIp(['host_id'=>$param['id']]);

        $result = [
            'status'    => 200,
            'msg'       => lang('success_message'),
            'data'      => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2024-05-20
     * @title 后台产品内页实例操作输出
     * @desc 后台产品内页实例操作输出
     * @url /admin/v1/host/:id/module_operate
     * @method GET
     * @author hh
     * @version v1
     * @param int id - desc:产品ID validate:required
     * @return string content - desc:模块输出内容
     */
    public function adminAreaModuleOperate()
    {
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();
        
        $result = $HostModel->adminAreaModuleOperate($param['id']);
        return json($result);
    }

    /**
     * 时间 2024-06-06
     * @title 拉取上游信息
     * @desc 拉取上游信息
     * @url /admin/v1/host/:id/module/sync
     * @method GET
     * @author wyh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function syncAccount()
    {
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();

        $result = $HostModel->syncAccount($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-12-10
     * @title 手动处理产品列表
     * @desc 手动处理产品列表
     * @url /admin/v1/host/failed_action
     * @method GET
     * @author hh
     * @version v1
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string action - desc:失败动作搜索 create开通失败 suspend暂停失败 terminate删除失败 validate:optional
     * @param string keywords - desc:关键字 产品ID 商品名称 产品标识 IP地址 validate:optional
     * @param string orderby - desc:排序字段 id due_time failed_action_trigger_time validate:optional
     * @return int list[].id - desc:产品ID
     * @return string list[].name - desc:产品标识
     * @return int list[].product_id - desc:商品ID
     * @return string list[].product_name - desc:商品名称
     * @return int list[].client_id - desc:用户ID
     * @return string list[].status - desc:产品状态 Unpaid未付款 Pending开通中 Active已开通 Suspended已暂停 Deleted已删除
     * @return string list[].failed_action - desc:失败动作 create开通失败 suspend暂停失败 terminate删除失败
     * @return string list[].failed_action_reason - desc:失败原因
     * @return string list[].renew_amount - desc:续费金额
     * @return string list[].billing_cycle - desc:计费方式 free免费 onetime一次 recurring_prepayment周期先付 recurring_postpaid周期后付
     * @return string list[].billing_cycle_name - desc:模块计费周期名称
     * @return int list[].due_time - desc:到期时间
     * @return string list[].client_name - desc:用户名
     * @return string list[].email - desc:邮箱
     * @return int list[].phone_code - desc:区号
     * @return string list[].phone - desc:手机号
     * @return int list[].failed_action_trigger_time - desc:触发时间
     * @return int list[].retry - desc:是否可重试 0否 1是
     * @return int count - desc:总条数
     * @return int expiring_count - desc:即将到期产品数量
     * @return int failed_action_count - desc:手动处理产品数量
     */
    public function failedActionHostList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $HostModel = new HostModel();

        // 获取产品列表
        $data = $HostModel->failedActionHostList($param);

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2024-12-10
     * @title 标记已处理
     * @desc 标记已处理
     * @url /admin/v1/host/:id/mark_processed
     * @method POST
     * @author hh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function failedActionMarkProcessed()
    {
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();

        $result = $HostModel->failedActionMarkProcessed((int)$param['id']);
        return json($result);
    }

    /**
     * 时间 2025-04-15
     * @title 手动处理重试
     * @desc 手动处理重试
     * @url /admin/v1/host/retry
     * @method POST
     * @author theworld
     * @version v1
     * @param array id - desc:产品ID validate:required
     */
    public function failedActionRetry()
    {
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();

        $result = $HostModel->failedActionRetry($param);
        return json($result);
    }

    /**
     * 时间 2025-01-23
     * @title 批量同步
     * @desc 批量同步
     * @url /admin/v1/host/sync
     * @method POST
     * @author hh
     * @version v1
     * @param array product_id - desc:商品ID validate:required
     * @param array host_status - desc:产品状态 Active已开通 Suspended已暂停 validate:required
     */
    public function batchSyncAccount()
    {
        // 接收参数
        $param = $this->request->param();

        $data = [];
        $data['batch_sync_product_id'] = $param['product_id'] ?? [];
        $data['batch_sync_host_status'] = $param['host_status'] ?? [];

        if (!$this->validate->scene('batch_sync')->check($data)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $HostModel = new HostModel();
        
        $result = $HostModel->batchSyncAccount($param);

        return json($result);
    }

    /**
     * 时间 2025-09-23
     * @title 推送到下游
     * @desc 推送到下游
     * @url /admin/v1/host/:id/push_downstream
     * @method POST
     * @author hh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function pushDownstream(){
		// 接收参数
        $param = $this->request->param();

        $HostModel = new HostModel();

        $result = $HostModel->pushDownstream($param['id']);

        return json($result);
    }


}