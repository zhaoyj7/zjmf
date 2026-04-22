<?php
namespace app\home\controller;

use app\common\model\HostModel;
use app\common\model\UpstreamHostModel;
use app\common\model\SelfDefinedFieldModel;
use app\common\model\HostIpModel;
use app\home\validate\HostValidate;
use app\admin\validate\HostValidate as AdminHostValidate;

/**
 * @title 产品管理
 * @desc 产品管理
 * @use app\home\controller\HostController
 */
class HostController extends HomeBaseController
{
    // 验证类
    protected $validate;

    public function initialize()
    {
        parent::initialize();
        $this->validate = new HostValidate();
    }
    
    /**
     * 时间 2022-05-19
     * @title 产品列表
     * @desc 产品列表
     * @author theworld
     * @version v1
     * @url /console/v1/host
     * @method GET
     * @param string keywords - desc:关键字 搜索范围产品ID商品名称标识 validate:optional
     * @param string status - desc:状态 Unpaid未付款 Pending开通中 Active已开通 Suspended已暂停 Deleted已删除 validate:optional
     * @param string tab - desc:状态 using使用中 expiring即将到期 overdue已逾期 deleted已删除 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 id active_time due_time validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:产品列表
     * @return int list[].id - desc:产品ID
     * @return int list[].product_id - desc:商品ID
     * @return string list[].product_name - desc:商品名称
     * @return string list[].name - desc:标识
     * @return int list[].active_time - desc:开通时间
     * @return int list[].due_time - desc:到期时间
     * @return string list[].first_payment_amount - desc:金额
     * @return string list[].billing_cycle - desc:周期
     * @return string list[].status - desc:状态 Unpaid未付款 Pending开通中 Active已开通 Suspended已暂停 Deleted已删除
     * @return string list[].renew_amount - desc:续费金额
     * @return string list[].client_notes - desc:用户备注
     * @return int list[].ip_num - desc:IP数量
     * @return int count - desc:产品总数
     */
	public function list()
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
     * 时间 2022-10-13
     * @title 会员中心已订购产品列表
     * @desc 会员中心已订购产品列表
     * @author theworld
     * @version v1
     * @url /console/v1/client/host
     * @method GET
     * @param string keywords - desc:关键字搜索 商品名称产品名称IP validate:optional
     * @param string status - desc:产品状态 Unpaid未付款 Pending开通中 Active已开通 Suspended已暂停 Deleted已删除 validate:optional
     * @param string tab - desc:状态 using使用中 expiring即将到期 overdue已逾期 deleted已删除 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @return array list - desc:产品列表
     * @return int list[].id - desc:产品ID
     * @return int list[].product_id - desc:商品ID
     * @return string list[].product_name - desc:商品名称
     * @return string list[].name - desc:标识
     * @return int list[].create_time - desc:订购时间
     * @return int list[].due_time - desc:到期时间
     * @return string list[].status - desc:状态 Unpaid未付款 Pending开通中 Active已开通 Suspended已暂停 Deleted已删除 Failed开通失败
     * @return string list[].client_notes - desc:用户备注
     * @return string list[].type - desc:类型
     * @return string list[].ip - desc:IP
     * @return int count - desc:产品总数
     * @return int using_count - desc:使用中产品数量
     * @return int expiring_count - desc:即将到期产品数量
     * @return int overdue_count - desc:已逾期产品数量
     * @return int deleted_count - desc:已删除产品数量
     * @return int all_count - desc:全部产品数量
     */
    public function clientHostList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $HostModel = new HostModel();

        // 获取产品列表
        $result = $HostModel->clientHostList($param);

        return json($result);
    }

    /**
     * 时间 2022-10-13
     * @title 自定义导航产品列表
     * @desc 自定义导航产品列表
     * @author theworld
     * @version v1
     * @url /console/v1/menu/:id/host
     * @method GET
     * @param int id - desc:导航ID validate:required
     * @return string data.content - desc:模块输出内容
     */
    public function menuHostList()
    {
        $param = $this->request->param();
        
        // 实例化模型类
        $HostModel = new HostModel();

        // 获取产品
        $result = $HostModel->menuHostList((int)$param['id']);
        return json($result);
    }

    /**
     * 时间 2022-10-13
     * @title 前台产品列表(跨模块)
     * @desc 前台产品列表(跨模块)
     * @author theworld
     * @version v1
     * @url /console/v1/home/host
     * @method GET
     * @param int m - desc:导航ID
     * @param int page - desc:页数
     * @param int limit - desc:每页条数
     * @param string orderby - desc:排序(id,due_time,status)
     * @param string sort - desc:升/降序
     * @param string keywords - desc:关键字搜索:商品名称/产品名称/IP
     * @param int country_id - desc:搜索:国家ID
     * @param string city - desc:搜索:城市
     * @param string area - desc:搜索:区域
     * @param string status - desc:产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除)
     * @param string tab - desc:状态using使用中expiring即将到期overdue已逾期deleted已删除
     * @return array list - desc:列表数据
     * @return int list[].id - desc:产品ID
     * @return int list[].product_id - desc:商品ID
     * @return string list[].name - desc:产品标识
     * @return string list[].status - desc:产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除)
     * @return int list[].active_time - desc:开通时间
     * @return int list[].due_time - desc:到期时间
     * @return string list[].client_notes - desc:用户备注
     * @return string list[].product_name - desc:商品名称
     * @return string list[].country - desc:国家
     * @return string list[].country_code - desc:国家代码
     * @return int list[].country_id - desc:国家ID
     * @return string list[].city - desc:城市
     * @return string list[].area - desc:区域
     * @return string list[].power_status - desc:电源状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return string list[].image_name - desc:镜像名称
     * @return string list[].image_icon - desc:镜像图标(Windows,CentOS,Ubuntu,Debian,ESXi,XenServer,FreeBSD,Fedora,ArchLinux,Rocky,AlmaLinux,OpenEuler,RedHat,其他)
     * @return int list[].ip_num - desc:IP数量
     * @return string list[].dedicate_ip - desc:主IP
     * @return string list[].assign_ip - desc:附加IP(英文逗号分隔)
     * @return string list[].base_info - desc:产品基础信息
     * @return array|object list[].self_defined_field - desc:自定义字段值(键是自定义字段ID,值是填的内容)
     * @return int list[].show_base_info - desc:产品列表是否展示基础信息：1是默认，0否
     * @return int list[].is_auto_renew - desc:是否自动续费(0=否,1=是)
     * @return int list[].billing_cycle - desc:计费方式(free=免费,onetime=一次性,recurring_prepayment=周期先付,on_demand=按需)
     * @return int count - desc:总条数
     * @return int using_count - desc:使用中产品数量
     * @return int expiring_count - desc:即将到期产品数量
     * @return int overdue_count - desc:已逾期产品数量
     * @return int deleted_count - desc:已删除产品数量
     * @return int all_count - desc:全部产品数量
     * @return int data_center[].country_id - desc:国家ID
     * @return string data_center[].city - desc:城市
     * @return string data_center[].area - desc:区域
     * @return string data_center[].country_name - desc:国家
     * @return string data_center[].country_code - desc:国家代码
     * @return string data_center[].customfield.multi_language.city - desc:城市多语言
     * @return string data_center[].customfield.multi_language.city - desc:区域多语言
     * @return int self_defined_field[].id - desc:自定义字段ID
     * @return string self_defined_field[].field_name - desc:自定义字段名称
     * @return string self_defined_field[].field_type - desc:字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区)
     */
    public function homeHostList()
    {
        $param = $this->request->param();
        
        // 实例化模型类
        $HostModel = new HostModel();

        // 获取产品
        $result = $HostModel->homeHostList($param);
        return json($result);
    }

	/**
     * 时间 2022-05-19
     * @title 产品详情
     * @desc 产品详情
     * @author theworld
     * @version v1
     * @url /console/v1/host/:id
     * @method GET
     * @param int id - desc:产品ID validate:required
     * @return object host - desc:产品
     * @return int host.id - desc:产品ID 
     * @return int host.order_id - desc:订单ID 
     * @return int host.product_id - desc:商品ID 
     * @return string host.name - desc:标识 
     * @return string notes - desc:备注 
     * @return string host.first_payment_amount - desc:订购金额
     * @return string host.renew_amount - desc:续费金额
     * @return string host.billing_cycle - desc:计费周期
     * @return string host.billing_cycle_name - desc:模块计费周期名称
     * @return string host.billing_cycle_time - desc:模块计费周期时间,秒
     * @return int host.active_time - desc:开通时间 
     * @return int host.due_time - desc:到期时间
     * @return string host.status - desc:状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @return string host.suspend_type - desc:暂停类型,overdue到期暂停,overtraffic超流暂停,certification_not_complete实名未完成,other其他
     * @return string host.suspend_reason - desc:暂停原因
     * @return int host.ratio_renew - desc:是否开启比例续费:0否,1是
     * @return string host.base_price - desc:购买周期原价
     * @return string host.product_name - desc:商品名称
     * @return int host.agent - desc:代理产品0否1是
     * @return string host.upstream_host_id - desc:上游产品ID
     * @return string host.base_info - desc:产品基础信息
     * @return int host.auto_release_time - desc:自动释放时间(0=未设置过)
     * @return string host.keep_time_price - desc:保留期价格
     * @return string host.on_demand_flow_price - desc:按需流量价格
     * @return string host.on_demand_billing_cycle_unit - desc:出账周期单位(hour=每小时,day=每天,month=每月)
     * @return int host.on_demand_billing_cycle_day - desc:出账周期号数
     * @return string host.on_demand_billing_cycle_point - desc:出账周期时间点(如00:00)
     * @return int host.change_billing_cycle_id - desc:是否申请了到期转按需(0=否,>0是,申请了不能执行续费/升降级操作)
     * @return int host.addition.country_id - desc:国家ID
     * @return string host.addition.city - desc:城市
     * @return string host.addition.area - desc:区域
     * @return string host.addition.image_icon - desc:镜像图标(Windows,CentOS,Ubuntu,Debian,ESXi,XenServer,FreeBSD,Fedora,ArchLinux,Rocky,AlmaLinux,OpenEuler,RedHat,其他)
     * @return string host.addition.image_name - desc:镜像名称
     * @return string host.addition.username - desc:实例用户名
     * @return string host.addition.password - desc:实例密码
     * @return int host.addition.port - desc:端口
     * @return int host.product_on_demand.client_auto_delete - desc:允许用户设置自动释放(0=否,1=是)
     * @return int host.product_on_demand.on_demand_to_duration - desc:允许按需转包年包月(0=否,1=是)
     * @return int host.product_on_demand.duration_to_on_demand - desc:允许包年包月/试用转按需(0=否,1=是)
     * @return  int self_defined_field[].id - desc:自定义字段ID
     * @return  string self_defined_field[].field_name - desc:字段名称
     * @return  string self_defined_field[].field_type - desc:字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,checkbox=勾选框,textarea=文本区)
     * @return  string self_defined_field[].value - desc:当前值
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
        $selfDefinedField = $SelfDefinedFieldModel->showClientHostDetailField(['host_id'=>$param['id']]);
        if(isset($host->addition)){
            $host['addition'] = (object)$host['addition'];
        }

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'host'              => $host,
                'self_defined_field'=> $selfDefinedField,
            ]
        ];
        return json($result);
	}

    /**
     * 时间 2022-05-30
     * @title 获取产品内页
     * @desc 获取产品内页
     * @url /console/v1/host/:id/view
     * @method GET
     * @author hh
     * @version v1
     * @param int id - desc:产品ID validate:required
     * @return string content - desc:模块输出内容
     */
    public function clientArea()
    {
        $param = $this->request->param();
        
        // 实例化模型类
        $HostModel = new HostModel();

        // 获取产品
        $result = $HostModel->clientArea((int)$param['id']);
        return json($result);
    }

    /**
     * 时间 2022-08-11
     * @title 修改产品备注
     * @desc 修改产品备注
     * @author theworld
     * @version v1
     * @url /console/v1/host/:id/notes
     * @method PUT
     * @param int id - desc:产品ID validate:required
     * @param string notes - desc:备注 validate:optional
     */
    public function updateHostNotes()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update_notes')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $HostModel = new HostModel();
        
        // 修改产品备注
        $result = $HostModel->updateHostNotes($param);

        return json($result);
    }

    /**
     * 时间 2025-01-27
     * @title 批量修改产品备注
     * @desc 批量修改产品备注
     * @author wyh
     * @version v1
     * @url /console/v1/host/notes/batch
     * @method PUT
     * @param array ids - desc:产品ID数组 validate:required
     * @param string notes - desc:备注 validate:optional
     * @return int success_count - desc:成功数量
     * @return int fail_count - desc:失败数量
     * @return int total - desc:总数
     */
    public function batchUpdateHostNotes()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('batch_update_notes')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $HostModel = new HostModel();
        
        // 批量修改产品备注
        $result = $HostModel->batchUpdateHostNotes($param);

        return json($result);
    }

    /**
     * 时间 2022-10-26
     * @title 获取用户所有产品
     * @desc 获取用户所有产品
     * @author theworld
     * @version v1
     * @url /console/v1/host/all
     * @method GET
     * @return array list - desc:产品
     * @return int list[].id - desc:产品ID 
     * @return int list[].product_id - desc:商品ID 
     * @return string list[].product_name - desc:商品名称 
     * @return string list[].name - desc:标识
     * @return string list[].status - desc:状态 Unpaid未付款 Pending开通中 Active已开通 Suspended已暂停 Deleted已删除 Failed开通失败 Cancelled已取消
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
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-20
     * @title 模块暂停
     * @desc 模块暂停
     * @url /console/v1/host/:id/module/suspend
     * @method POST
     * @author hh
     * @version v1
     * @param int id - desc:产品ID validate:required
     * @param string suspend_type - desc:暂停类型 overdue到期暂停 overtraffic超流暂停 certification_not_complete实名未完成 other其他 validate:required
     * @param string suspend_reason - desc:暂停原因 validate:optional
     */
    public function suspendAccount()
    {
        $param = $this->request->param();

        // 参数验证
        $AdminHostValidate = new AdminHostValidate();
        if (!$AdminHostValidate->scene('suspend')->check($param)){
            return json(['status' => 400 , 'msg' => lang($AdminHostValidate->getError())]);
        }

        // 实例化模型类
        $HostModel = new HostModel();

        $result = $HostModel->suspendAccount($param);
        return json($result);
    }

    /**
     * 时间 2023-02-20
     * @title 模块解除暂停
     * @desc 模块解除暂停
     * @url /console/v1/host/:id/module/unsuspend
     * @method POST
     * @author wyh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function unsuspendAccount()
    {
        return json(['status'=>200,'msg'=>lang('success_message')]);
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();

        $result = $HostModel->unsuspendAccount($param['id']);
        return json($result);
    }

    /**
     * 时间 2024-04-30
     * @title 产品IP详情
     * @desc  产品IP详情
     * @url /console/v1/host/:id/ip
     * @method GET
     * @author hh
     * @version v1
     * @param int id - desc:产品ID validate:required
     * @return string dedicate_ip - desc:主IP
     * @return string assign_ip - desc:附加IP(英文逗号分隔)
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

    public function hostUpdateDownstream()
    {
        $param = $this->request->param();

        $HostModel = new HostModel();

        $result = $HostModel->hostUpdateDownstream($param);

        return json($result);
    }

    /**
     * @时间 2024-12-09
     * @title 获取产品具体信息
     * @desc  获取产品具体信息,目前用于续费开关
     * @author hh
     * @version v1
     * @url /console/v1/host/:id/specific_info
     * @method GET
     * @param int id - desc:产品ID validate:required
     * @return int id - desc:产品ID
     * @return string name - desc:产品标识
     * @return string renew_amount - desc:续费金额
     * @return string billing_cycle_name - desc:模块计费周期名称
     * @return int due_time - desc:到期时间
     * @return int ip_num - desc:IP数量
     * @return string dedicate_ip - desc:主IP
     * @return string assign_ip - desc:附加IP(英文逗号分隔)
     * @return string country - desc:国家
     * @return string country_code - desc:国家代码
     * @return int country_id - desc:国家ID
     * @return string city - desc:城市
     * @return string area - desc:区域
     */
    public function hostSpecificInfo()
    {
        $param = $this->request->param();

        $HostModel = new HostModel();

        $data = $HostModel->hostSpecificInfo((int)$param['id']);

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => (object)$data,
        ];
        return json($result);
    }

    /**
     * 时间 2025-03-27
     * @title 修改自动释放时间
     * @desc  修改自动释放时间
     * @author hh
     * @version v1
     * @url /console/v1/host/:id/auto_release_time
     * @method PUT
     * @param int id - desc:产品ID validate:required
     * @param int auto_release_time - desc:自动释放时间戳 validate:required
     */
    public function updateHostAutoReleaseTime()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update_auto_release_time')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $HostModel = new HostModel();
        
        // 修改产品备注
        $result = $HostModel->updateHostAutoReleaseTime($param);

        return json($result);
    }

    /**
     * 时间 2025-04-07
     * @title 获取产品按需转包年包月周期价格
     * @desc  获取产品按需转包年包月周期价格
     * @author hh
     * @version v1
     * @url /console/v1/host/:id/on_demand_to_recurring_prepayment
     * @method GET
     * @param int id - desc:产品ID validate:required
     * @return int duration[].id - desc:周期ID
     * @return string duration[].price - desc:周期价格
     * @return string duration[].name_show - desc:周期显示名称
     * @return string duration[].client_level_discount - desc:用户等级折扣
     * @return string duration[].discount_renew_price - desc:续费可折扣部分
     * @return string duration[].discount_order_price - desc:订购可折扣部分
     */
    public function onDemandToRecurringPrepaymentDurationPrice()
    {
        $param = $this->request->param();

        $HostModel = new HostModel();
        
        $result = $HostModel->onDemandToRecurringPrepaymentDurationPrice($param);
        return json($result);
    }

    /**
     * 时间 2025-04-07
     * @title 产品按需转包年包月
     * @desc  产品按需转包年包月
     * @author hh
     * @version v1
     * @url /console/v1/host/:id/on_demand_to_recurring_prepayment
     * @method POST
     * @param int id - desc:产品ID validate:required
     * @param int duration_id - desc:周期ID validate:required
     * @return string id - desc:订单ID
     * @return string amount - desc:金额
     */
    public function onDemandToRecurringPrepayment()
    {
        $param = $this->request->param();

        $HostModel = new HostModel();
        
        $result = $HostModel->onDemandToRecurringPrepayment($param);
        return json($result);
    }

    /**
     * 时间 2025-04-08
     * @title 获取产品包年包月转按需价格
     * @desc  获取产品包年包月转按需价格
     * @author hh
     * @version v1
     * @url /console/v1/host/:id/recurring_prepayment_to_on_demand
     * @method GET
     * @param int id - desc:产品ID validate:required
     * @return string on_demand_price - desc:按需出账价格,可能已经折扣了
     * @return string base_renew_price - desc:按需出账原价
     * @return string keep_time_price - desc:保留期价格
     * @return string billing_cycle_name - desc:周期名称
     */
    public function recurringPrepaymentToOnDemandDurationPrice()
    {
        $param = $this->request->param();

        $HostModel = new HostModel();
        
        $result = $HostModel->recurringPrepaymentToOnDemandDurationPrice($param);
        return json($result);
    }

    /**
     * 时间 2025-04-09
     * @title 产品包年包月转按需
     * @desc  产品包年包月转按需
     * @author hh
     * @version v1
     * @url /console/v1/host/:id/recurring_prepayment_to_on_demand
     * @method POST
     * @param int id - desc:产品ID validate:required
     */
    public function recurringPrepaymentToOnDemand()
    {
        $param = $this->request->param();

        $HostModel = new HostModel();
        
        $result = $HostModel->recurringPrepaymentToOnDemand($param);
        return json($result);
    }

    /**
     * 时间 2025-04-09
     * @title 取消产品包年包月转按需
     * @desc  取消产品包年包月转按需
     * @author hh
     * @version v1
     * @url /console/v1/host/:id/recurring_prepayment_to_on_demand
     * @method DELETE
     * @param int id - desc:产品ID validate:required
     */
    public function cancelRecurringPrepaymentToOnDemand()
    {
        $param = $this->request->param();

        $HostModel = new HostModel();
        
        $result = $HostModel->cancelRecurringPrepaymentToOnDemand($param);
        return json($result);
    }

}