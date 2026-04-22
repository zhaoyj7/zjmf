<?php
namespace app\common\model;

use think\db\Query;
use think\db\Where;
use think\Exception;
use think\Model;
use think\facade\Db;
use app\admin\model\PluginModel;
use app\common\logic\ModuleLogic;
use app\common\logic\ResModuleLogic;
use app\admin\model\AdminModel;
use app\admin\model\AdminViewModel;
use app\common\logic\UploadLogic;

/**
 * @title 订单模型
 * @desc 订单模型
 * @use app\common\model\OrderModel
 */
class OrderModel extends Model
{
	protected $name = 'order';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'client_id'         => 'int',
        'type'              => 'string',
        'status'            => 'string',
        'amount'            => 'float',
        'credit'            => 'float',
        'amount_unpaid'     => 'float',
        'upgrade_refund'    => 'int',
        'gateway'           => 'string',
        'gateway_name'      => 'string',
        'notes'             => 'string',
        'pay_time'          => 'int',
        'due_time'          => 'int',
        'create_time'       => 'int',
        'update_time'       => 'int',
        'refund_amount'     => 'float',
        'admin_id'          => 'int',
        'base_price'        => 'float',
        'is_lock'           => 'int',
        'recycle_time'      => 'int',
        'will_delete_time'  => 'int',
        'is_recycle'        => 'int',
        'return_url'        => 'string',
        'submit_application_time'   => 'int',
        'voucher'           => 'string',
        'review_fail_reason'        => 'string',
        'is_refund'         => 'int',
        'refund_gateway_to_credit' => 'float',
        'unpaid_timeout'    => 'int',
    ];

	/**
     * 时间 2022-05-17
     * @title 订单列表
     * @desc 订单列表
     * @author theworld
     * @version v1
     * @param string param.keywords - 关键字,搜索范围:订单ID,商品名称,用户名称,邮箱,手机号
     * @param int param.client_id - 用户ID
     * @param string param.type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @param string param.status - 状态Unpaid未付款Paid已付款
     * @param string param.amount - 金额
     * @param array param.gateway - 支付方式
     * @param int param.start_time - 开始时间
     * @param int param.end_time - 结束时间
     * @param int param.order_id - 订单ID
     * @param string param.product_id - 商品ID
     * @param string param.username - 用户名称
     * @param string param.email - 邮箱
     * @param string param.phone - 手机号
     * @param int param.pay_time - 支付时间
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby id 排序(id,amount,client_id,reg_time)
     * @param string param.sort - 升/降序 asc,desc
     * @param  int param.start_recycle_time - 回收开始时间(scene=recycle有效)
     * @param  int param.end_recycle_time - 回收结束时间(scene=recycle有效)
     * @param  int param.start_pay_time - 搜索:开始支付时间
     * @param  int param.end_pay_time - 搜索:结束支付时间
     * @param  string scene - 场景(recycle_bin=回收站)
     * @return array list - 订单
     * @return int list[].id - 订单ID 
     * @return string list[].type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @return int list[].create_time - 创建时间 
     * @return string list[].amount - 金额 
     * @return string list[].status - 状态Unpaid未付款Paid已付款Cancelled已取消Refunded已退款WaitUpload待上传WaitReview待审核ReviewFail审核失败 
     * @return string list[].gateway - 支付方式 
     * @return float list[].credit - 使用余额,大于0代表订单使用了余额,和金额相同代表订单支付方式为余额 
     * @return int list[].client_id - 用户ID,前台接口调用时不返回
     * @return string list[].client_name - 用户名称,前台接口调用时不返回
     * @return string list[].client_credit - 用户余额,前台接口调用时不返回
     * @return string list[].email - 邮箱,前台接口调用时不返回
     * @return string list[].phone_code - 国际电话区号,前台接口调用时不返回 
     * @return string list[].phone - 手机号,前台接口调用时不返回 
     * @return string list[].company - 公司,前台接口调用时不返回
     * @return int list[].client_status - 用户是否启用0:禁用,1:正常,前台接口调用时不返回
     * @return int list[].reg_time - 用户注册时间,前台接口调用时不返回
     * @return string list[].country - 国家,前台接口调用时不返回
     * @return string list[].address - 地址,前台接口调用时不返回
     * @return string list[].language - 语言,前台接口调用时不返回
     * @return string list[].notes - 备注,前台接口调用时不返回
     * @return string list[].refund_amount - 订单已退款金额,前台接口调用时不返回
     * @return string list[].host_name - 产品标识
     * @return string list[].description - 描述
     * @return array list[].product_names - 订单下所有产品的商品名称
     * @return int list[].host_id - 产品ID
     * @return int list[].order_item_count - 订单子项数量
     * @return int list[].is_lock - 是否锁定(0=否,1=是),scene=recycle_bin返回
     * @return int list[].recycle_time - 放入回收站时间,scene=recycle_bin返回
     * @return int list[].will_delete_time - 彻底删除时间,scene=recycle_bin返回
     * @return bool list[].certification - 是否实名认证true是false否(显示字段有certification返回)
     * @return string list[].certification_type - 实名类型person个人company企业(显示字段有certification返回)
     * @return string list[].client_level - 用户等级(显示字段有client_level返回)
     * @return string list[].client_level_color - 用户等级颜色(显示字段有client_level返回)
     * @return string list[].sale - 销售(显示字段有sale返回)
     * @return string list[].addon_client_custom_field_[id] - 用户自定义字段(显示字段有addon_client_custom_field_[id]返回,[id]为用户自定义字段ID)
     * @return array list[].voucher - 上传的凭证
     * @return string list[].review_fail_reason - 审核失败原因
     * @return string list[].gateway_sign - 支付方式标识(credit=余额)
     * @return int count - 订单总数
     * @return string total_amount - 总金额
     * @return string page_total_amount - 当前页总金额
     */
    public function orderList($param, $scene = '')
    {
        // 获取当前应用
        $app = app('http')->getName();
        $selectField = [];
        $selectDataRange = [];
        if($app=='home'){
            $param['client_id'] = get_client_id();
            if(empty($param['client_id'])){
                return ['list' => [], 'count' => 0];
            }
        }else{
            $param['client_id'] = isset($param['client_id']) ? intval($param['client_id']) : 0;
            $param['tab'] = $param['tab'] ?? 'all';

            // 用户内页列表不使用视图
            if($param['tab'] == 'all'){
                // 获取当前显示字段
                $AdminViewModel = new AdminViewModel();
                $adminView = $AdminViewModel->adminViewIndex(['id' => $param['view_id'] ?? 0, 'view'=>'order']);
                if(isset($adminView['status']) && $adminView['status']==1){
                    $selectField = $adminView['select_field'];
                    $selectField = array_flip($selectField);
                    $selectDataRange = $adminView['data_range_switch']==1 ? $adminView['select_data_range'] : [];
                }
                unset($adminView);
            }
        }

        $param['keywords'] = $param['keywords'] ?? '';
        $param['type'] = $param['type'] ?? '';
        $param['status'] = $param['status'] ?? '';
        $param['amount'] = $param['amount'] ?? '';
        $param['gateway'] = $param['gateway'] ?? [];
        $param['start_time'] = intval($param['start_time'] ?? 0);
        $param['end_time'] = intval($param['end_time'] ?? 0);
        $param['order_id'] = intval($param['order_id'] ?? 0);
        $param['product_id'] = intval($param['product_id'] ?? 0);
        $param['username'] = $param['username'] ?? '';
        $param['pay_time'] = intval($param['pay_time'] ?? 0);
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id','amount','client_id','reg_time','pay_time']) ? $param['orderby'] : 'id';

        // 排序字段映射
        $orderReal = [
            'id'        => 'o.id',
            'amount'    => 'o.amount',
            'client_id' => 'o.client_id',
            'reg_time'  => 'c.create_time',
            'pay_time'  => 'o.pay_time',
        ];

        $where = function (Query $query) use ($param, $app, $scene, $selectDataRange){
            if($app=='home'){
                $query->where('o.status', '<>', 'Cancelled');
                // 修改为后台显示
                // $query->where('o.type', '<>', 'recharge');
                $query->where('o.type', '<>', 'combine');
                $query->where('o.type', '<>', 'credit_limit');

                // 排除超时的订单（只排除未支付且已超时的订单）
                $query->where(function($query){
                    $query->whereOr([
                        ['o.status', '<>', 'Unpaid'],  // 非未支付状态的都显示
                        ['o.unpaid_timeout', '=', 0],   // 未支付但无超时限制的显示
                        ['o.unpaid_timeout', '>', time()] // 未支付但未超时的显示
                    ]);
                });
            }
            if(!empty($param['client_id'])){
                $query->where('o.client_id', $param['client_id']);
            }
            // 优化：使用子查询处理关键字搜索，避免预先加载大量ID到内存
            if(!empty($param['keywords'])){
                $keyword = $param['keywords'];
                $query->where(function($subQuery) use ($keyword) {
                    // 1. 搜索订单ID（精确匹配，最快）
                    if (is_numeric($keyword)) {
                        $subQuery->whereOr('o.id', $keyword);
                    }

                    // 2. 搜索客户信息（使用EXISTS子查询）
                    $subQuery->whereOr('o.client_id', 'in', function(Query $query) use ($keyword) {
                        $query->name('client')->where('username|email|phone', 'like', "%{$keyword}%")->field('id');
                    });
                    
                    // 3. 搜索产品名称（使用EXISTS子查询，关联order_item）
                    $subQuery->whereOr('o.id', 'in', function($query) use ($keyword) {
                        $query->name('order_item')->alias('oi')
                            ->leftjoin('product p', 'p.id=oi.product_id')
                            ->where('p.name', 'like', "%{$keyword}%")
                            ->field('oi.order_id');
                    });
                    
                    // 4. 搜索主机名称（使用EXISTS子查询）
                    $subQuery->whereOr('o.id', 'in', function($query) use ($keyword) {
                        $query->name('host')->where('name', 'like', "%{$keyword}%")->field('order_id');
                    });
                });
            }
            if(!empty($param['type'])){
                $query->where('o.type', $param['type']);
            }
            if(!empty($param['status'])){
                $query->where('o.status', $param['status']);
            }
            if(!empty($param['amount'])){
                if(strpos($param['amount'],'.')!==false){
                    $query->where('o.amount', $param['amount']);
                }else{
                    $query->where('o.amount', 'like', "{$param['amount']}.%");
                }
            }
            if(!empty($param['gateway'])){
                $hasCredit = false;
                $gateways = array_column(gateway_list()['list']??[], 'name') ?? [];
                foreach ($param['gateway'] as $key => $gateway){
                    if(!in_array($gateway, $gateways) && ucfirst($gateway)!='Credit' && $gateway!='credit_limit'){
                        unset($param['gateway'][$key]);
                    }
                    if(ucfirst($gateway)=='Credit'){
                        $hasCredit = true;
                    }
                }
                $param['gateway'] = array_values($param['gateway']);
                if($hasCredit){
                    $query->whereRaw("o.credit>0 OR o.gateway='credit' OR o.gateway in ('".implode("','", $param['gateway'])."')");
                }else{
                    $query->whereRaw('o.amount>o.credit')->whereIn('o.gateway', $param['gateway']);
                }
            }
            if(!empty($param['start_time']) && !empty($param['end_time'])){
                $query->where('o.create_time', '>=', strtotime(date('Y-m-d', $param['start_time'])))->where('o.create_time', '<=', strtotime(date('Y-m-d 23:59:59', $param['end_time'])));
            }
            if (!empty($param['host_id'])){
                $query->where('oi.host_id',$param['host_id']);
            }
            if($scene == 'recycle_bin'){
                $query->where('o.is_recycle', 1);
                if(isset($param['start_recycle_time']) && $param['start_recycle_time'] !== ''){
                    $query->where('o.recycle_time', '>=', $param['start_recycle_time']);
                }
                if(isset($param['end_recycle_time']) && $param['end_recycle_time'] !== ''){
                    $query->where('o.recycle_time', '<=', $param['end_recycle_time']);
                }
            }else{
                $query->where('o.is_recycle', 0);
            }
            // 右下角搜索
            if(!empty($param['order_id'])){
                $query->where('o.id', $param['order_id']);
            }
            if(!empty($param['product_ids']) && is_array($param['product_ids'])){
                $query->whereIn('p.id', $param['product_ids']);
            }
            if (!empty($param['product_id'])){
                $query->where('p.id', $param['product_id']);
            }
            if(!empty($param['username'])){
                $query->where('c.username', 'like', "%{$param['username']}%");
            }
            if(!empty($param['email'])){
                $query->where('c.email', 'like', "%{$param['email']}%");
            }
            if(!empty($param['phone'])){
                $query->where('c.phone', 'like', "%{$param['phone']}%");
            }
            if(!empty($param['pay_time'])){
                $query->where('o.pay_time', '>=', strtotime(date('Y-m-d', $param['pay_time'])))->where('o.pay_time', '<=', strtotime(date('Y-m-d 23:59:59', $param['pay_time'])));
            }
            if(!empty($param['start_pay_time']) && !empty($param['end_pay_time'])){
                $query->where('o.pay_time', '>=', strtotime(date('Y-m-d', $param['start_pay_time'])))->where('o.pay_time', '<=', strtotime(date('Y-m-d 23:59:59', $param['end_pay_time'])));
            }
            if($scene != 'recycle_bin'){
                // 数据范围筛选
                if(!empty($selectDataRange)){
                    // 数据范围映射
                    $dataRangeReal = [
                        'id'                    => 'o.id',
                        'username'              => 'c.username',
                        'company'               => 'c.company',
                        'product_name'          => 'p.id',
                        'order_amount'          => 'o.amount',
                        'gateway'               => 'o.gateway',
                        'pay_time'              => 'o.pay_time',
                        'order_time'            => 'o.create_time',
                        'order_status'          => 'o.status',
                        'order_type'            => 'o.type',
                        'order_use_credit'      => 'o.credit',
                        'order_refund_amount'   => 'o.refund_amount',
                        'client_id'             => 'o.client_id', 
                        'phone'                 => 'c.phone',
                        'email'                 => 'c.email',
                        'client_status'         => 'c.status',
                        'reg_time'              => 'c.create_time',
                        'country'               => 'c.country_id',
                        'address'               => 'c.address',
                        'language'              => 'c.language',
                        'notes'                 => 'c.notes',
                    ];

                    foreach ($selectDataRange as $v) {
                        if(in_array($v['key'], ['id', 'order_amount', 'order_use_credit', 'order_refund_amount', 'client_id'])){
                            if($v['rule']=='equal'){
                                $query->where($dataRangeReal[$v['key']], $v['value']);
                            }else if($v['rule']=='not_equal'){
                                $query->where($dataRangeReal[$v['key']], '<>', $v['value']);
                            }else if($v['rule']=='include'){
                                $query->where($dataRangeReal[$v['key']], 'like', "%{$v['value']}%");
                            }else if($v['rule']=='not_include'){
                                $query->where($dataRangeReal[$v['key']], 'not like', "%{$v['value']}%");
                            }else if($v['rule']=='empty'){
                                $query->whereRaw("{$dataRangeReal[$v['key']]}=0 OR {$dataRangeReal[$v['key']]} is null");
                            }else if($v['rule']=='not_empty'){
                                $query->whereRaw("{$dataRangeReal[$v['key']]}!=0 AND {$dataRangeReal[$v['key']]} is not null");
                            }
                        }else if(in_array($v['key'], ['username', 'company', 'phone', 'email', 'address', 'notes'])){
                            if($v['rule']=='equal'){
                                $query->where($dataRangeReal[$v['key']], $v['value']);
                            }else if($v['rule']=='not_equal'){
                                $query->where($dataRangeReal[$v['key']], '<>', $v['value']);
                            }else if($v['rule']=='include'){
                                $query->where($dataRangeReal[$v['key']], 'like', "%{$v['value']}%");
                            }else if($v['rule']=='not_include'){
                                $query->where($dataRangeReal[$v['key']], 'not like', "%{$v['value']}%");
                            }else if($v['rule']=='empty'){
                                $query->whereRaw("{$dataRangeReal[$v['key']]}='' OR {$dataRangeReal[$v['key']]} is null");
                            }else if($v['rule']=='not_empty'){
                                $query->whereRaw("{$dataRangeReal[$v['key']]}!='' AND {$dataRangeReal[$v['key']]} is not null");
                            }
                        }else if(in_array($v['key'], ['product_name', 'gateway', 'order_status', 'order_type', 'language'])){
                            if($v['rule']=='equal'){
                                $query->whereIn($dataRangeReal[$v['key']], $v['value']);
                            }else if($v['rule']=='not_equal'){
                                $query->whereNotIn($dataRangeReal[$v['key']], $v['value']);
                            }
                        }else if(in_array($v['key'], ['client_status'])){
                            if($v['rule']=='equal'){
                                $query->where($dataRangeReal[$v['key']], $v['value']);
                            }
                        }else if(in_array($v['key'], ['country'])){
                            if($v['rule']=='equal'){
                                $query->whereIn($dataRangeReal[$v['key']], $v['value']);
                            }else if($v['rule']=='not_equal'){
                                $query->whereNotIn($dataRangeReal[$v['key']], $v['value']);
                            }else if($v['rule']=='empty'){
                                $query->where($dataRangeReal[$v['key']], 0);
                            }else if($v['rule']=='not_empty'){
                                $query->where($dataRangeReal[$v['key']], '<>', 0);
                            }
                        }else if(in_array($v['key'], ['order_time', 'reg_time', 'pay_time'])){
                            if($v['rule']=='equal'){
                                $query->where($dataRangeReal[$v['key']], '>=', strtotime($v['value']))->where($dataRangeReal[$v['key']], '<=', strtotime(date("Y-m-d 23:59:59", strtotime($v['value']))));
                            }else if($v['rule']=='interval'){
                                $query->where($dataRangeReal[$v['key']], '>=', strtotime($v['value']['start']))->where($dataRangeReal[$v['key']], '<=', strtotime(date("Y-m-d 23:59:59", strtotime($v['value']['end']))));
                            }else if($v['rule']=='dynamic'){
                                if($v['value']['condition1']=='now'){
                                    $day1 = strtotime(date("Y-m-d"));
                                }else if($v['value']['condition1']=='ago'){
                                    $day1 = strtotime(date("Y-m-d"))-$v['value']['day1']*24*3600;
                                }else if($v['value']['condition1']=='later'){
                                    $day1 = strtotime(date("Y-m-d"))+$v['value']['day1']*24*3600;
                                }
                                if($v['value']['condition2']=='now'){
                                    $day2 = strtotime(date("Y-m-d"));
                                }else if($v['value']['condition2']=='ago'){
                                    $day2 = strtotime(date("Y-m-d"))-$v['value']['day2']*24*3600;
                                }else if($v['value']['condition2']=='later'){
                                    $day2 = strtotime(date("Y-m-d"))+$v['value']['day2']*24*3600;
                                }
                                if($day1>$day2){
                                    $start = $day2;
                                    $end = strtotime(date("Y-m-d 23:59:59", $day1));
                                }else{
                                    $start = $day1;
                                    $end = strtotime(date("Y-m-d 23:59:59", $day2));
                                }
                                $query->where($dataRangeReal[$v['key']], '>=', $start)->where($dataRangeReal[$v['key']], '<=', $end);
                                unset($start,$end,$day1,$day2);
                            }else if($v['rule']=='empty'){
                                $query->where($dataRangeReal[$v['key']], 0);
                            }else if($v['rule']=='not_empty'){
                                $query->where($dataRangeReal[$v['key']], '<>', 0);
                            }
                        }else if($v['key']=='certification'){
                            $certificationHookResult = hook_one('get_certification_list');
                            $personCertification = [];
                            $companyCertification = [];
                            foreach ($certificationHookResult as $kk => $vv) {
                                if($vv=='person'){
                                    $personCertification[] = $kk;
                                }else{
                                    $companyCertification[] = $kk;
                                }
                            }
                            unset($certificationHookResult);
                            if($v['rule']=='equal'){
                                if(in_array('', $v['value']) && in_array('person', $v['value']) && in_array('company', $v['value'])){

                                }else if(in_array('', $v['value']) && in_array('person', $v['value']) && !in_array('company', $v['value'])){
                                    $query->whereNotIn('o.client_id', $companyCertification);
                                }else if(in_array('', $v['value']) && !in_array('person', $v['value']) && in_array('company', $v['value'])){
                                    $query->whereNotIn('o.client_id', $personCertification);
                                }else if(in_array('', $v['value']) && !in_array('person', $v['value']) && !in_array('company', $v['value'])){
                                    $query->whereNotIn('o.client_id', array_merge($personCertification, $companyCertification));
                                }else if(!in_array('', $v['value']) && in_array('person', $v['value']) && in_array('company', $v['value'])){
                                    $query->whereIn('o.client_id', array_merge($personCertification, $companyCertification));
                                }else if(!in_array('', $v['value']) && in_array('person', $v['value']) && !in_array('company', $v['value'])){
                                    $query->whereIn('o.client_id', $personCertification);
                                }else if(!in_array('', $v['value']) && !in_array('person', $v['value']) && in_array('company', $v['value'])){
                                    $query->whereIn('o.client_id', $companyCertification);
                                }else if(!in_array('', $v['value']) && !in_array('person', $v['value']) && !in_array('company', $v['value'])){
                                    $searchClientId = [];
                                }
                            }else if($v['rule']=='not_equal'){
                                if(in_array('', $v['value']) && in_array('person', $v['value']) && in_array('company', $v['value'])){
                                    $searchClientId = [];
                                }else if(in_array('', $v['value']) && in_array('person', $v['value']) && !in_array('company', $v['value'])){
                                    $query->whereIn('o.client_id', $companyCertification);
                                }else if(in_array('', $v['value']) && !in_array('person', $v['value']) && in_array('company', $v['value'])){
                                    $query->whereIn('o.client_id', $personCertification);
                                }else if(in_array('', $v['value']) && !in_array('person', $v['value']) && !in_array('company', $v['value'])){
                                    $query->whereIn('o.client_id', array_merge($personCertification, $companyCertification));
                                }else if(!in_array('', $v['value']) && in_array('person', $v['value']) && in_array('company', $v['value'])){
                                    $query->whereNotIn('o.client_id', array_merge($personCertification, $companyCertification));
                                }else if(!in_array('', $v['value']) && in_array('person', $v['value']) && !in_array('company', $v['value'])){
                                    $query->whereNotIn('o.client_id', $personCertification);
                                }else if(!in_array('', $v['value']) && !in_array('person', $v['value']) && in_array('company', $v['value'])){
                                    $query->whereNotIn('o.client_id', $companyCertification);
                                }else if(!in_array('', $v['value']) && !in_array('person', $v['value']) && !in_array('company', $v['value'])){

                                }
                            }
                            unset($personCertification,$companyCertification);
                        }else if($v['key']=='client_level'){
                            if($v['rule']=='equal'){
                                $orders = $this
                                    ->alias('o')
                                    ->field('o.id')
                                    ->leftjoin('client c', 'o.client_id=c.id')
                                    ->leftjoin('addon_idcsmart_client_level_client_link a', 'a.client_id=c.id')
                                    ->whereIn('a.addon_idcsmart_client_level_id', $v['value'])
                                    ->select()
                                    ->toArray();
                            }else if($v['rule']=='not_equal'){
                                $orders = $this
                                    ->alias('o')
                                    ->field('o.id')
                                    ->leftjoin('client c', 'o.client_id=c.id')
                                    ->leftjoin('addon_idcsmart_client_level_client_link a', 'a.client_id=c.id')
                                    ->whereNotIn('a.addon_idcsmart_client_level_id', $v['value'])
                                    ->select()
                                    ->toArray();
                            }else if($v['rule']=='empty'){
                                $orders = $this
                                    ->alias('o')
                                    ->field('o.id')
                                    ->leftjoin('client c', 'o.client_id=c.id')
                                    ->leftjoin('addon_idcsmart_client_level_client_link a', 'a.client_id=c.id')
                                    ->whereRaw("a.addon_idcsmart_client_level_id=0 OR a.addon_idcsmart_client_level_id is null")
                                    ->select()
                                    ->toArray();
                            }else if($v['rule']=='not_empty'){
                                $orders = $this
                                    ->alias('o')
                                    ->field('o.id')
                                    ->leftjoin('client c', 'o.client_id=c.id')
                                    ->leftjoin('addon_idcsmart_client_level_client_link a', 'a.client_id=c.id')
                                    ->where('a.addon_idcsmart_client_level_id', '>', 0)
                                    ->select()
                                    ->toArray();
                            }
                            if(isset($orders)){
                                $searchOrderId = isset($searchOrderId) ? array_intersect($searchOrderId, array_column($orders, 'id')) : array_column($orders, 'id');
                                unset($orders);
                            }
                        }else if($v['key']=='sale'){
                            if($v['rule']=='equal'){
                                $orders = $this
                                    ->alias('o')
                                    ->field('o.id')
                                    ->leftjoin('client c', 'o.client_id=c.id')
                                    ->leftjoin('addon_idcsmart_sale_client_bind a', 'a.client_id=c.id')
                                    ->whereIn('a.sale_id', $v['value'])
                                    ->select()
                                    ->toArray();
                            }else if($v['rule']=='not_equal'){
                                $orders = $this
                                    ->alias('o')
                                    ->field('o.id')
                                    ->leftjoin('client c', 'o.client_id=c.id')
                                    ->leftjoin('addon_idcsmart_sale_client_bind a', 'a.client_id=c.id')
                                    ->whereNotIn('a.sale_id', $v['value'])
                                    ->select()
                                    ->toArray();
                            }else if($v['rule']=='empty'){
                                $orders = $this
                                    ->alias('o')
                                    ->field('o.id')
                                    ->leftjoin('client c', 'o.client_id=c.id')
                                    ->leftjoin('addon_idcsmart_sale_client_bind a', 'a.client_id=c.id')
                                    ->whereRaw("a.sale_id=0 OR a.sale_id is null")
                                    ->select()
                                    ->toArray();
                            }else if($v['rule']=='not_empty'){
                                $orders = $this
                                    ->alias('o')
                                    ->field('o.id')
                                    ->leftjoin('client c', 'o.client_id=c.id')
                                    ->leftjoin('addon_idcsmart_sale_client_bind a', 'a.client_id=c.id')
                                    ->where('a.sale_id', '>', 0)
                                    ->select()
                                    ->toArray();
                            }
                            if(isset($orders)){
                                $searchOrderId = isset($searchOrderId) ? array_intersect($searchOrderId, array_column($orders, 'id')) : array_column($orders, 'id');
                                unset($orders);
                            }
                        }else if($v['key']=='order_invoice_status'){
                            // 发票状态筛选，通过钩子获取订单ID
                            if($v['rule']=='equal'){
                                $invoiceOrderIds = hook_one('get_order_ids_by_invoice_status', [
                                    'invoice_status' => $v['value'] ?? ''
                                ]);
                                if(!empty($invoiceOrderIds)){
                                    $searchOrderId = isset($searchOrderId) ? array_intersect($searchOrderId, $invoiceOrderIds) : $invoiceOrderIds;
                                }else{
                                    // 如果没有符合条件的订单，设置为空数组
                                    $searchOrderId = [];
                                }
                            }
                        }else if(strpos($v['key'], 'addon_client_custom_field_')===0){
                            $id = intval(str_replace('addon_client_custom_field_', '', $v['key']));
                            if($v['rule']=='equal'){
                                if(is_array($v['value'])){
                                    $orders = $this
                                        ->alias('o')
                                        ->field('o.id')
                                        ->leftjoin('client c', 'o.client_id=c.id')
                                        ->leftjoin('addon_client_custom_field_value a', 'a.client_id=c.id AND a.custom_field_id='.$id)
                                        ->whereIn('a.value', $v['value'])
                                        ->select()
                                        ->toArray();
                                }else{
                                    if($v['value']===0){
                                        $orders = $this
                                            ->alias('o')
                                            ->field('o.id')
                                            ->leftjoin('client c', 'o.client_id=c.id')
                                            ->leftjoin('addon_client_custom_field_value a', 'a.client_id=c.id AND a.custom_field_id='.$id)
                                            ->whereRaw("a.value='{$v['value']}' OR a.value is null")
                                            ->select()
                                            ->toArray();
                                    }else{
                                        $orders = $this
                                            ->alias('o')
                                            ->field('o.id')
                                            ->leftjoin('client c', 'o.client_id=c.id')
                                            ->leftjoin('addon_client_custom_field_value a', 'a.client_id=c.id AND a.custom_field_id='.$id)
                                            ->where('a.value', $v['value'])
                                            ->select()
                                            ->toArray();
                                    }
                                }
                            }else if($v['rule']=='not_equal'){
                                if(is_array($v['value'])){
                                    $orders = $this
                                        ->alias('o')
                                        ->field('o.id')
                                        ->leftjoin('client c', 'o.client_id=c.id')
                                        ->leftjoin('addon_client_custom_field_value a', 'a.client_id=c.id AND a.custom_field_id='.$id)
                                        ->whereRaw("a.value not in ('".implode("','", $v['value'])."') OR a.value is null")
                                        ->select()
                                        ->toArray();
                                }else{
                                    $orders = $this
                                        ->alias('o')
                                        ->field('o.id')
                                        ->leftjoin('client c', 'o.client_id=c.id')
                                        ->leftjoin('addon_client_custom_field_value a', 'a.client_id=c.id AND a.custom_field_id='.$id)
                                        ->whereRaw("a.value!='{$v['value']}' OR a.value is null")
                                        ->select()
                                        ->toArray();
                                }
                            }else if($v['rule']=='include'){
                                $orders = $this
                                    ->alias('o')
                                    ->field('o.id')
                                    ->leftjoin('client c', 'o.client_id=c.id')
                                    ->leftjoin('addon_client_custom_field_value a', 'a.client_id=c.id AND a.custom_field_id='.$id)
                                    ->where('a.value', 'like', "%{$v['value']}%")
                                    ->select()
                                    ->toArray();
                            }else if($v['rule']=='not_include'){
                                $orders = $this
                                    ->alias('o')
                                    ->field('o.id')
                                    ->leftjoin('client c', 'o.client_id=c.id')
                                    ->leftjoin('addon_client_custom_field_value a', 'a.client_id=c.id AND a.custom_field_id='.$id)
                                    ->whereRaw("a.value not like '%{$v['value']}%' OR a.value is null")
                                    ->select()
                                    ->toArray();
                            }else if($v['rule']=='empty'){
                                $orders = $this
                                    ->alias('o')
                                    ->field('o.id')
                                    ->leftjoin('client c', 'o.client_id=c.id')
                                    ->leftjoin('addon_client_custom_field_value a', 'a.client_id=c.id AND a.custom_field_id='.$id)
                                    ->whereRaw("a.value='' OR a.value is null")
                                    ->select()
                                    ->toArray();
                            }else if($v['rule']=='not_empty'){
                                $orders = $this
                                    ->alias('o')
                                    ->field('o.id')
                                    ->leftjoin('client c', 'o.client_id=c.id')
                                    ->leftjoin('addon_client_custom_field_value a', 'a.client_id=c.id AND a.custom_field_id='.$id)
                                    ->whereRaw("a.value!='' AND a.value is not null")
                                    ->select()
                                    ->toArray();
                            }
                            $searchOrderId =  isset($searchOrderId) ? array_intersect($searchOrderId, array_column(array_values($orders), 'id')) : array_column(array_values($orders), 'id');
                            unset($orders);
                        }
                    }
                    unset($selectDataRange);
                    unset($dataRangeReal);
                    if(isset($searchOrderId)){
                        $query->whereIn('o.id', $searchOrderId);
                    }
                    unset($searchOrderId);
                }
            }

            hook('order_list_where_query_append', ['param'=>$param, 'app'=>$app, 'query'=>$query]);
        };
        // wyh 20230510 增加 关联订单 子商品订单或父商品订单
        $whereOr = function (Query $query)use($param){
            if (!empty($param['host_id'])){
                if (class_exists('server\idcsmart_common_dcim\model\IdcsmartCommonSonHost')){
                    $IdcsmartCommonSonHost = new \server\idcsmart_common_dcim\model\IdcsmartCommonSonHost();
                    $links = $IdcsmartCommonSonHost->where('host_id',$param['host_id'])
                        ->whereOr('son_host_id',$param['host_id'])
                        ->select()->toArray();
                    $dcimOrderIds = array_column($links,'order_id');
                    $dcimHostIds = array_column($links,'host_id');
                    $dcimSonHostIds = array_column($links,'son_host_id');
                }
                if (class_exists('server\idcsmart_common_finance\model\IdcsmartCommonSonHost')){
                    $IdcsmartCommonSonHost = new \server\idcsmart_common_finance\model\IdcsmartCommonSonHost();
                    $links = $IdcsmartCommonSonHost->where('host_id',$param['host_id'])
                        ->whereOr('son_host_id',$param['host_id'])
                        ->select()->toArray();
                    $financeOrderIds = array_column($links,'order_id');
                    $financeHostIds = array_column($links,'host_id');
                    $financeSonHostIds = array_column($links,'son_host_id');
                }
                if (class_exists('server\idcsmart_common_cloud\model\IdcsmartCommonSonHost')){
                    $IdcsmartCommonSonHost = new \server\idcsmart_common_cloud\model\IdcsmartCommonSonHost();
                    $links = $IdcsmartCommonSonHost->where('host_id',$param['host_id'])
                        ->whereOr('son_host_id',$param['host_id'])
                        ->select()->toArray();
                    $cloudOrderIds = array_column($links,'order_id');
                    $cloudHostIds = array_column($links,'host_id');
                    $cloudSonHostIds = array_column($links,'son_host_id');
                }
                if (class_exists('server\idcsmart_common_business\model\IdcsmartCommonSonHost')){
                    $IdcsmartCommonSonHost = new \server\idcsmart_common_business\model\IdcsmartCommonSonHost();
                    $links = $IdcsmartCommonSonHost->where('host_id',$param['host_id'])
                        ->whereOr('son_host_id',$param['host_id'])
                        ->select()->toArray();
                    $businessOrderIds = array_column($links,'order_id');
                    $businessHostIds = array_column($links,'host_id');
                    $businessSonHostIds = array_column($links,'son_host_id');
                }
                // 续费 和 升降级订单
                $hostIds = array_merge($dcimHostIds??[],$dcimSonHostIds??[],$financeHostIds??[],$financeSonHostIds??[],$cloudHostIds??[],$cloudSonHostIds??[],
                    $businessHostIds??[],$businessSonHostIds??[]);
                $otherOrderIds = $this->alias('o')
                    ->leftJoin('order_item oi','o.id=oi.order_id')
                    ->whereIn('oi.host_id',$hostIds)
                    ->whereIn('oi.type',['host','renew','upgrade'])
                    ->column('o.id');
                $orderIds = array_merge($dcimOrderIds??[],$financeOrderIds??[],$cloudOrderIds??[],$businessOrderIds??[],$otherOrderIds??[]);

                if (!empty($orderIds)){
                    $query->whereIn('o.id',$orderIds);
                }
            }
        };

        // 优化：判断是否需要JOIN product、order_item、host表
        // 注意：使用子查询方式处理关键字搜索后，主查询不需要JOIN这些表
        // 只在有明确的product_id或host_id筛选时才JOIN
        $needProductJoin = !empty($param['product_id']) || !empty($param['product_ids']);
        $needHostJoin = !empty($param['host_id']);
        
        // 检查数据范围筛选是否用到product表
        if (!empty($selectDataRange)) {
            foreach ($selectDataRange as $v) {
                if ($v['key'] == 'product_name') {
                    $needProductJoin = true;
                    break;
                }
            }
        }
        
        $needOrderItemJoin = $needProductJoin || $needHostJoin;

        // 优化策略：使用子查询方式统计，避免加载大量ID到内存
        
        if ($needOrderItemJoin) {
            // 情况1：需要JOIN一对多表时，使用子查询方式统计
            // 不预先加载所有ID，直接在数据库中完成统计
            
            // 构建子查询SQL（用于COUNT）
            $subQueryForCount = $this->alias('o')
                ->field('o.id')
                ->leftjoin('client c', 'c.id=o.client_id');
            
            if ($needOrderItemJoin) {
                $subQueryForCount->leftjoin('order_item oi', "oi.order_id=o.id");
            }
            if ($needProductJoin) {
                $subQueryForCount->leftjoin('product p', "p.id=oi.product_id");
            }
            if ($needHostJoin) {
                $subQueryForCount->leftjoin('host h', 'o.id=h.order_id');
            }
            
            $subQueryForCount->where($where)->whereOr($whereOr)->group('o.id');
            
            // 使用buildSql()构建子查询SQL字符串
            $subSql = $subQueryForCount->buildSql();
            
            // 统计数量：使用原生SQL查询派生表
            $countSql = "SELECT COUNT(*) as total FROM {$subSql} temp";
            $countResult = Db::query($countSql);
            $count = $countResult[0]['total'] ?? 0;
            
            if ($count == 0) {
                return ['list' => [], 'count' => 0];
            }
            
            // 统计总金额：需要重新构建包含amount字段的子查询
            if($app!='home'){
                $subQueryForSum = $this->alias('o')
                    ->field('o.id, o.amount')
                    ->leftjoin('client c', 'c.id=o.client_id');
                
                if ($needOrderItemJoin) {
                    $subQueryForSum->leftjoin('order_item oi', "oi.order_id=o.id");
                }
                if ($needProductJoin) {
                    $subQueryForSum->leftjoin('product p', "p.id=oi.product_id");
                }
                if ($needHostJoin) {
                    $subQueryForSum->leftjoin('host h', 'o.id=h.order_id');
                }
                
                $subQueryForSum->where($where)->whereOr($whereOr)->group('o.id');
                $subSqlSum = $subQueryForSum->buildSql();
                
                // 使用原生SQL查询派生表
                $sumSql = "SELECT SUM(temp.amount) as total FROM {$subSqlSum} temp";
                $sumResult = Db::query($sumSql);
                $totalAmount = amount_format($sumResult[0]['total'] ?? 0);
            }
            
            // 标记需要使用子查询过滤列表
            $useSubQueryFilter = true;
            
        } else {
            // 情况2：不需要JOIN一对多表时，直接使用数据库COUNT和SUM（最快）
            $countQuery = $this->alias('o')
                ->leftjoin('client c', 'c.id=o.client_id');
            
            $countQuery->where($where)->whereOr($whereOr);
            
            // 直接使用数据库COUNT，效率最高
            $count = $countQuery->count('o.id');
            
            if ($count == 0) {
                return ['list' => [], 'count' => 0];
            }
            
            // 计算总金额（如果需要）
            if($app!='home'){
                $sumQuery = $this->alias('o')
                    ->leftjoin('client c', 'c.id=o.client_id');
                
                $sumQuery->where($where)->whereOr($whereOr);
                $totalAmount = amount_format($sumQuery->sum('o.amount'));
            }
            
            // 不需要使用子查询
            $useSubQueryFilter = false;
        }
        
        // 第三步：查询分页的订单详情
        if($app == 'home'){
            $language = get_client_lang();
        }else{
            $language = get_system_lang(true);
        }
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';
        
        // 构建列表查询
        $listQuery = $this->alias('o')
            ->field('o.id,o.type,o.create_time,o.amount,o.status,o.gateway_name gateway,o.credit,o.client_id,c.username client_name,c.credit client_credit,c.email,c.phone_code,c.phone,c.company,c.status client_status,c.create_time reg_time,co.'.$countryName.' country,c.address,c.language,c.notes,o.is_lock,o.recycle_time,o.will_delete_time,o.refund_amount,o.voucher,o.review_fail_reason,o.gateway gateway_sign,o.pay_time')
            ->leftjoin('client c', 'c.id=o.client_id')
            ->leftJoin('country co', 'co.id=c.country_id');
        
        // 根据是否需要子查询过滤，使用不同的查询方式
        if ($useSubQueryFilter) {
            // 使用子查询过滤订单ID（避免加载大量ID到内存）
            // 构建子查询（和COUNT用的一样，只查询ID）
            $subQueryForList = $this->alias('o')
                ->field('o.id')
                ->leftjoin('client c', 'c.id=o.client_id');
            
            if ($needOrderItemJoin) {
                $subQueryForList->leftjoin('order_item oi', "oi.order_id=o.id");
            }
            if ($needProductJoin) {
                $subQueryForList->leftjoin('product p', "p.id=oi.product_id");
            }
            if ($needHostJoin) {
                $subQueryForList->leftjoin('host h', 'o.id=h.order_id');
            }
            
            $subQueryForList->where($where)->whereOr($whereOr)->group('o.id');
            $subSqlList = $subQueryForList->buildSql();
            
            // 使用子查询过滤
            $listQuery->whereRaw("o.id IN {$subSqlList}");
        } else {
            // 直接使用WHERE条件查询
            $listQuery->where($where)->whereOr($whereOr);
        }
        
        $orders = $listQuery
            ->limit($param['limit'])
            ->page($param['page'])
            ->orderRaw("o.status = 'WaitReview' DESC")
            ->order($orderReal[$param['orderby']], $param['sort'])
            ->select()
            ->toArray();

        $orderId = array_column($orders, 'id');
        $clientId = array_column($orders, 'client_id');

        $orderItems = OrderItemModel::alias('oi')
        	->field('oi.order_id,oi.type,h.id,h.name,h.billing_cycle,h.billing_cycle_name,p.name product_name,oi.description')
        	->leftjoin('host h',"h.id=oi.host_id AND h.is_delete=0")
        	->leftjoin('product p',"p.id=oi.product_id")
            ->withAttr('product_name', function($val) use ($app) {
                if($app == 'home'){
                    $multiLanguage = hook_one('multi_language', [
                        'replace' => [
                            'product_name' => $val,
                        ],
                    ]);
                    if(isset($multiLanguage['product_name'])){
                        $val = $multiLanguage['product_name'];
                    }
                }
                return $val;
            })
        	->whereIn('oi.order_id', $orderId)
        	->select()
            ->toArray();

        $orderItemCount = [];
        $names = [];
        $billingCycles = [];
        $productNames = [];
        $descriptions = [];
        $hostIds = [];

        $orderItemCoin = [];

        $langPrice = lang('price');
        foreach ($orderItems as $key => $orderItem) {

            // wyh 20230130 有问题就注释
            $description = explode("\n",$orderItem['description']);
            if (!empty($description)){
                $newDes = '';
                foreach ($description as $item1){
                    $arr = explode('=>',$item1);
                    if (count($arr)==4){
                        $itemDes = $arr[0] . ':' . $arr[1] . $arr[2] . ' '.$langPrice.' ' . $arr[3];
                        $newDes = $newDes.$itemDes . "\n";
                    }else{
                        $newDes = $newDes . ' ' . $item1 . "\n";
                    }
                }
                $orderItem['description'] = trim($newDes,"\n");
            }

            $orderItemCount[$orderItem['order_id']] = $orderItemCount[$orderItem['order_id']] ?? 0;
            if (in_array($orderItem['type'],['host','renew','upgrade','app_market_deposit','artificial','combine','manual','on_demand','recharge'])){
                $orderItemCount[$orderItem['order_id']]++;
            }
            // 使用了平台币
            if (!isset($orderItemCoin[$orderItem['order_id']])){
                $orderItemCoin[$orderItem['order_id']] = 0;
            }
            if ($orderItem['type']=='addon_coin_coupon'){
                $orderItemCoin[$orderItem['order_id']] = 1;
            }


            // 获取产品ID
            if(!empty($orderItem['id'])){
                $hostIds[$orderItem['order_id']][] = $orderItem['id'];
            }
            // 获取产品名称
            $names[$orderItem['order_id']][] = $orderItem['name'];
            // 获取产品计费周期
            $billingCycles[$orderItem['order_id']][] = $orderItem['billing_cycle_name'];
            // 获取商品名称
            if(in_array($orderItem['type'], ['addon_promo_code', 'addon_idcsmart_promo_code', 'addon_idcsmart_client_level', 'addon_event_promotion','addon_coin_coupon','addon_idcsmart_voucher'])){
//                $productNames[$orderItem['order_id']][] = $orderItem['description'];
            }else if(!empty($orderItem['product_name'])){
                $productNames[$orderItem['order_id']][] = $orderItem['product_name'];
            }else{
                $productNames[$orderItem['order_id']][] = $orderItem['description'];
            }
            // 获取商品名称
            if(!empty($orderItem['description'])){
                $descriptions[$orderItem['order_id']][] = $orderItem['description'];
            }
        }

        if(isset($selectField['certification'])){
            $certificationHookResult = hook_one('get_certification_list');
        }

        // 获取用户等级
        if(isset($selectField['client_level'])){
            $clientLevel = hook_one('get_client_level_list', ['client_id'=>$clientId]);
        }

        // 获取销售
        if(isset($selectField['sale'])){
            $sale = hook_one('get_sale_list', ['client_id'=>$clientId]);
        }

        // 获取开票状态
        if(isset($selectField['order_invoice_status'])){
            $invoiceStatus = hook_one('get_invoice_status_list', ['order_id'=>$orderId]);
        }

        // 开发者
        $developer = hook_one('get_developer_list', ['client_id'=>$clientId]);

        // 获取用户自定义字段
        $clientCustomFieldIdArr = [];
        if(isset($selectField)){
            foreach($selectField as $k=>$v){
                if(stripos($k, 'addon_client_custom_field_') === 0){
                    $clientCustomFieldId = (int)str_replace('addon_client_custom_field_', '', $k);
                    $clientCustomFieldIdArr[ $clientCustomFieldId ] = 1;
                }
            }
            if(!empty($clientCustomFieldIdArr)){
                $clientCustomField = hook_one('get_client_custom_field_list', ['client_id'=>$clientId]);
            }
        }

        $pageTotalAmount = amount_format(array_sum(array_column($orders, 'amount')));

        if (class_exists('addon\coin\model\CoinConfigModel')){
            $CoinConfigModel = new \addon\coin\model\CoinConfigModel();
            $config = $CoinConfigModel->configIndex();
            $coinName = $config['name'] ?? '';
        }

        foreach ($orders as $key => $order) {
            $orders[$key]['amount'] = amount_format($order['amount']); // 处理金额格式
            $orders[$key]['client_credit'] = amount_format($order['client_credit']); // 处理金额格式
            // 处理null值
            $orders[$key]['client_name'] = $order['client_name'] ?? '';
            $orders[$key]['email'] = $order['email'] ?? '';
            $orders[$key]['phone_code'] = $order['phone_code'] ?? '';
            $orders[$key]['phone'] = $order['phone'] ?? '';
            $orders[$key]['company'] = $order['company'] ?? '';
            $orders[$key]['client_status'] = $order['client_status'] ?? 0;
            $orders[$key]['reg_time'] = $order['reg_time'] ?? 0;
            $orders[$key]['country'] = $order['country'] ?? '';
            $orders[$key]['address'] = $order['address'] ?? '';
            $orders[$key]['language'] = $order['language'] ?? '';
            $orders[$key]['notes'] = $order['notes'] ?? '';
            $orders[$key]['voucher'] = !empty($order['voucher']) ? explode(',', $order['voucher']) : [];
            foreach($orders[$key]['voucher'] as $kk=>$vv){
                $orders[$key]['voucher'][$kk] = getOssUrl([
                    'file_path' => WEB_ROOT . 'upload/common/order/',
                    'file_name' => $vv,
                ]);
            }

            // 获取产品标识,产品标识不一致是返回空字符串
            if($order['type']=='artificial'){
                $orders[$key]['host_name'] = $descriptions[$order['id']] ?? [];
            }else{
                $orders[$key]['host_name'] = $names[$order['id']] ?? [];
            }
            if(!empty($orders[$key]['host_name']) && count($orders[$key]['host_name'])==1){
                $orders[$key]['host_name'] = $orders[$key]['host_name'][0] ?? '';
            }else{
                $orders[$key]['host_name'] = '';
            } 
            $orders[$key]['description'] = $descriptions[$order['id']] ?? [];
            if(!empty($orders[$key]['description']) && count($orders[$key]['description'])==1){
                $orders[$key]['description'] = $orders[$key]['description'][0] ?? '';
            }else{
                $orders[$key]['description'] = '';
            }
        	

            // 获取计费周期,计费周期不一致是返回空字符串
            /*$billingCycle = isset($billingCycles[$order['id']]) ? array_values(array_unique($billingCycles[$order['id']])) : [];
            if(!empty($billingCycle) && count($billingCycle)==1){
                $orders[$key]['billing_cycle'] = $billingCycle[0] ?? '';
            }else{
                $orders[$key]['billing_cycle'] = '';
            }*/

            // 获取商品名称
            $orders[$key]['product_names'] = $productNames[$order['id']] ?? [];

            if(count($orders[$key]['product_names'])==1){
                $orders[$key]['host_id'] = $hostIds[$order['id']][0] ?? 0;
            }else{
                $orders[$key]['host_id'] = 0;
            }

            $orders[$key]['order_item_count'] = $orderItemCount[$order['id']] ?? 0;

            if (!empty($orderItemCoin[$order['id']]) && !empty($coinName)){
                $orders[$key]['gateway'] = $orders[$key]['gateway'] . "+" . $coinName;
            }

            // 插件中的字段
            if(isset($selectField['certification'])){
                // 实名认证字段
                $orders[$key]['certification'] = isset($certificationHookResult[$order['client_id']]) && $certificationHookResult[$order['client_id']]?true:false;
                $orders[$key]['certification_type'] = $certificationHookResult[$order['client_id']]??'person';
            }
            // 用户等级字段
            if(isset($selectField['client_level'])){
                $orders[$key]['client_level'] = $clientLevel[ $order['client_id'] ]['name'] ?? '';
                $orders[$key]['client_level_color'] = $clientLevel[ $order['client_id'] ]['background_color'] ?? '';
            }
            // 销售字段
            if(isset($selectField['sale'])){
                $orders[$key]['sale'] = $sale[ $order['client_id'] ]['name'] ?? '';
            }
            // 开票状态
            if(isset($selectField['order_invoice_status'])){
                $orders[$key]['order_invoice_status'] = $invoiceStatus[ $order['id'] ] ?? '';
            }
            // 开发者
            $orders[$key]['developer_type'] = $developer[$order['client_id']]['type']??0;

            // 用户自定义字段
            if(!empty($clientCustomFieldIdArr)){
                foreach($clientCustomFieldIdArr as $kk=>$vv){
                    $orders[$key]['addon_client_custom_field_'.$kk] = $clientCustomField[$order['client_id']][$kk] ?? '';
                }
            }

            // 前台接口去除字段
            if($app=='home'){
                unset($orders[$key]['client_id'], $orders[$key]['client_name'], $orders[$key]['client_credit'], $orders[$key]['email'], $orders[$key]['phone_code'], $orders[$key]['phone'], $orders[$key]['company'],$orders[$key]['client_status'],$orders[$key]['refund_amount'],$orders[$key]['reg_time'],$orders[$key]['country'],$orders[$key]['address'],$orders[$key]['language'],$orders[$key]['notes']);
            }
            if($scene != 'recycle_bin'){
                unset($orders[$key]['is_lock'],$orders[$key]['recycle_time'],$orders[$key]['will_delete_time']);
            }
        }

        if($app=='home'){
            return ['list' => $orders, 'count' => $count];
        }else{
            return ['list' => $orders, 'count' => $count, 'total_amount' => $totalAmount, 'page_total_amount' => $pageTotalAmount];
        }
    }

    /**
     * 时间 2022-05-17
     * @title 订单详情
     * @desc 订单详情
     * @author theworld
     * @version v1
     * @param int id - 订单ID required
     * @return int id - 订单ID 
     * @return string type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @return string amount - 金额 
     * @return int create_time - 创建时间 
     * @return int pay_time - 支付时间 
     * @return string status - 状态Unpaid未付款Paid已付款Cancelled已取消Refunded已退款WaitUpload待上传WaitReview待审核ReviewFail审核失败
     * @return string gateway - 支付方式 
     * @return string credit - 使用余额,大于0代表订单使用了余额,和金额相同代表订单支付方式为余额 
     * @return int client_id - 用户ID
     * @return string client_name - 用户名称
     * @return string notes - 备注
     * @return string refund_amount - 订单已退款金额
     * @return string amount_unpaid - 未支付金额 
     * @return string refundable_amount - 订单可退款金额
     * @return string apply_credit_amount - 订单可应用余额金额 
     * @return int admin_id - 管理员ID
     * @return string admin_name - 管理员名称
     * @return int is_recycle - 是否在回收站(0=否,1=是)
     * @return int refund_orginal - 订单支付方式退款时是否支持原路返回：1是，0否(退款至下拉就不显示'原支付路径')
     * @return array voucher - 上传的凭证
     * @return string review_fail_reason - 审核失败原因
     * @return string refund_credit - 已退款余额
     * @return string refund_gateway - 已退款渠道
     * @return string gateway_sign - 支付接口标识(credit=余额,credit_limit=信用额)
     * @return int unpaid_timeout - 未支付超时时间,0表示不限制
     * @return int remain_pay_time - 剩余支付时间,配合unpaid_timeout使用
     * @return array items - 订单子项
     * @return int items[].id - 订单子项ID 
     * @return string items[].description - 描述
     * @return string items[].amount - 金额 
     * @return int items[].host_id - 产品ID 
     * @return string items[].product_name - 商品名称 
     * @return string items[].host_name - 产品标识 
     * @return string items[].billing_cycle - 计费周期 
     * @return string items[].host_status - 产品状态Unpaid未付款Pending开通中Active使用中Suspended暂停Deleted删除Failed开通失败
     * @return int items[].edit - 是否可编辑1是0否
     * @return string items[].profit - 利润
     * @return int items[].agent - 代理订单1是0否
     */
    public function indexOrder($id)
    {
        // 获取当前应用
        $app = app('http')->getName();

        $order = $this->field('id,type,amount,create_time,pay_time,status,gateway_name gateway,gateway gateway_sign,credit,client_id,notes,refund_amount,amount_unpaid,admin_id,is_recycle,voucher,review_fail_reason,unpaid_timeout')->find($id);
        if (empty($order)){
            return (object)[]; // 转换为对象
        }

        // 插件用户限制,限制可查看的用户数据
        $res = hook('plugin_check_client_limit', ['client_id' => $order['client_id']]);
        foreach ($res as $value){
            if (isset($value['status']) && $value['status']==400){
                return (object)[]; // 转换为对象
            }
        }

        if (plugin_method_exist($order['gateway_sign'],'gateway','handle_refund')){
            $order['refund_orginal'] = 1;
        }else{
            $order['refund_orginal'] = 0;
        }
        // unset($order['plugin_name']);

        // 计算剩余支付时间
        $order['remain_pay_time'] = 0;
        if($order['unpaid_timeout'] > 0){
            $order['remain_pay_time'] = max(0, $order['unpaid_timeout'] - time());
        }

        $client = ClientModel::find($order['client_id']);
        $order['client_name'] = $client['username'] ?? '';

        $admin = AdminModel::find($order['admin_id']);
        $order['admin_name'] = $admin['name'] ?? '';

        // 订单的用户ID和前台用户不一致时返回空对象
        if($app=='home'){
            $client_id = get_client_id();
            if($order['client_id']!=$client_id || $order['status']=='Cancelled' || $order['is_recycle'] == 1){
                return (object)[]; // 转换为对象
            }
            unset($order['client_id'], $order['admin_id'], $order['client_name'], $order['admin_name'],$order['is_recycle']);
        }else{
            // $amount = TransactionModel::where('order_id', $id)->sum('amount'); // 订单流水金额
            // $refundAmount = RefundRecordModel::where('order_id', $id)->where('type', 'credit')
            //     ->where('status','Refunded')
            //     ->sum('amount'); // 订单已退款金额
            // $refundAmount = RefundRecordModel::where('order_id', $id)
                // ->whereIn('status',['Refunded','Pending','Refunding'])
                // ->sum('amount');
            
            $orderRefund = $this->orderRefundIndex(['order'=>$order]);

            $order['refund_amount'] = amount_format($order['refund_amount']);
            $order['refund_credit'] = $orderRefund['refund_credit'];
            $order['refund_gateway'] = $orderRefund['refund_gateway'];
            $order['refundable_amount'] = $orderRefund['leave_gateway'];
            $order['apply_credit_amount'] = bcsub($order['amount'], $orderRefund['leave_total'], 2);
        }

        $order['amount'] = amount_format($order['amount']); // 处理金额格式
        $order['credit'] = amount_format($order['credit']); // 处理金额格式
        //unset($order['client_id']);

        $orderItems = OrderItemModel::alias('oi')
            ->field('oi.id,oi.type,oi.description,oi.amount,h.id host_id,p.name product_name,h.name host_name,h.billing_cycle,h.billing_cycle_name,h.status host_status,uo.id upstream_order_id,uo.profit')
            ->leftjoin('host h',"h.id=oi.host_id AND h.is_delete=0")
            ->leftjoin('product p',"p.id=oi.product_id")
            ->leftjoin('upstream_order uo',"uo.host_id=oi.host_id AND uo.order_id=oi.order_id")
            ->withAttr('product_name', function($val) use ($app) {
                if($app == 'home'){
                    $multiLanguage = hook_one('multi_language', [
                        'replace' => [
                            'product_name' => $val,
                        ],
                    ]);
                    if(isset($multiLanguage['product_name'])){
                        $val = $multiLanguage['product_name'];
                    }
                }
                return $val;
            })
            ->where('oi.order_id', $id)
            ->select()
            ->toArray();
        $useCoin = false;
        foreach ($orderItems as $key => $orderItem) {
            if ($orderItem['type']=='addon_coin_coupon'){
                $useCoin = true;
            }
            $orderItems[$key]['amount'] = amount_format($orderItem['amount']); // 处理金额格式
            $orderItems[$key]['host_id'] = $orderItem['host_id'] ?? 0; // 处理空数据
            $orderItems[$key]['product_name'] = $orderItem['product_name'] ?? ''; // 处理空数据
            $orderItems[$key]['product_name'] = !empty($orderItems[$key]['product_name']) ? $orderItems[$key]['product_name'] : $orderItem['description'];
            $orderItems[$key]['host_name'] = $orderItem['host_name'] ?? ''; // 处理空数据
            $orderItems[$key]['billing_cycle'] = $orderItem['billing_cycle_name']; // 处理空数据
            $orderItems[$key]['host_status'] = $orderItem['host_status'] ?? ''; // 处理空数据
            $orderItems[$key]['profit'] = amount_format($orderItem['profit']); // 处理金额格式
            $orderItems[$key]['agent'] = !empty($orderItem['upstream_order_id']) ? 1 : 0;

            if(in_array($orderItem['type'], ['addon_promo_code', 'addon_idcsmart_promo_code', 'addon_idcsmart_client_level', 'addon_event_promotion','addon_coin_coupon'])){
                $orderItems[$key]['product_name'] = $orderItem['description'];
                $orderItems[$key]['host_name'] = '';
            }

            $description = explode("\n",$orderItem['description']);
            if (!empty($description)){
                $newDes = '';
                foreach ($description as $item1){
                    if (count(explode('=>',$item1))==4){
                        $arr = explode('=>',$item1);
                        $itemDes = $arr[0] . ':' . $arr[1] . $arr[2] .' '.lang('price').' ' . $arr[3];
                        $newDes = $newDes.$itemDes . "\n";
                    }else{
                        $newDes = $newDes . $item1 . "\n";
                    }
                }
                $orderItems[$key]['description'] = trim($newDes,"\n");
            }

            if($app!='home'){
                //$orderItems[$key]['edit'] = $order['status']=='Unpaid' ? ($orderItem['type']=='manual' ? 1 : 0) : 0;
                // wyh 20230412修改 都可更改
                $orderItems[$key]['edit'] = $order['status']=='Unpaid' ? 1 : 0;
            }else{
                unset($orderItems[$key]['profit'], $orderItems[$key]['agent']);
            }
            unset($orderItems[$key]['billing_cycle_name'], $orderItems[$key]['type'], $orderItems[$key]['upstream_order_id']);
        }

        if (class_exists('addon\coin\model\CoinConfigModel')){
            $CoinConfigModel = new \addon\coin\model\CoinConfigModel();
            $config = $CoinConfigModel->configIndex();
            $coinName = $config['name'] ?? '';
        }
        if ($useCoin && !empty($coinName)){
            $order['gateway'] = $order['gateway'] . '+' . $coinName;
        }

        $order['items'] = $orderItems;
        $voucher = !empty($order['voucher']) ? explode(',', $order['voucher']) : [];
        foreach($voucher as $k=>$v){
            $voucher[$k] = getOssUrl([
                'file_path' => WEB_ROOT . 'upload/common/order/',
                'file_name' => $v,
            ]);
        }
        $order['voucher'] = $voucher;

        return $order;
    }

    /**
     * 时间 2022-05-17
     * @title 新建订单
     * @desc 新建订单
     * @author theworld
     * @version v1
     * @param string type - 类型new新订单upgrade升降级商品订单upgrade_config升降级配置订单renew续费订单artificial人工订单 required
     * @param array products - 商品 类型为新订单时需要
     * @param int products[].product_id - 商品ID
     * @param object products[].config_options - 自定义配置
     * @param int products[].qty - 数量
     * @param float products[].price - 商品价格
     * @param object products[].customfield - 自定义字段
     * @param int host_id - 产品ID 类型为升降级商品订单时需要
     * @param object product - 升降级商品 类型为升降级商品订单时需要
     * @param int product.product_id - 商品ID
     * @param object product.config_options - 自定义配置
     * @param float product.price - 商品价格
     * @param int upgrade_refund - 是否退款0否1是 类型为升降级商品订单和升降级配置订单时需要
     * @param float price_difference - 产品价格差价 类型为升降级配置订单时需要
     * @param float renew_price_difference - 产品续费价格差价 类型为升降级配置订单时需要
     * @param float base_price - 产品新原价 类型为升降级配置订单时需要
     * @param object config_options - 自定义配置 类型为升降级配置订单时需要
     * @param int id - 产品ID 类型为续费订单时需要
     * @param float amount - 金额 类型为升降级配置订单和人工订单时需要
     * @param string description - 描述 类型为升降级配置订单和人工订单时需要
     * @param int client_id - 用户ID required
     * @param object customfield - 自定义字段
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createOrder($param)
    {
        $result = hook('get_client_parent_id',['client_id'=>$param['client_id']]);

        foreach ($result as $value){
            if ($value){
                $param['client_id'] = (int)$value;
            }
        }

        // 验证用户ID
        $client = ClientModel::find($param['client_id']);
        if (empty($client)){
            return ['status'=>400, 'msg'=>lang('client_is_not_exist')];
        }
        // 验证支付方式
        /*if (!check_gateway($gateway)){
            return ['status'=>400, 'msg'=>lang('gateway_is_not_exist')];
        }*/
        if($param['type']=='new'){
            $result = $this->createNewOrder($param);
        }else if($param['type']=='renew'){
            if (class_exists("addon\\idcsmart_renew\\model\\IdcsmartRenewModel")){
                $data = [
                    'id' => $param['id'],
                    'billing_cycle' => $param['customfield']['billing_cycle']??'',
                    'promo_code' => $param['customfield']['promo_code']??[],
                    //'pay' => 1, // 标记支付
                ];
                if (isset($param['customfield']['custom_amount']) && $param['customfield']['custom_amount']>=0){
                    $data['custom_amount'] = $param['customfield']['custom_amount'];
                }
                $IdcsmartRenewModel = new \addon\idcsmart_renew\model\IdcsmartRenewModel();
                $IdcsmartRenewModel->isAdmin = true;
                $result = $IdcsmartRenewModel->renew($data);
            }
            $param['host_id'] = $param['id'];
        }else if($param['type']=='upgrade'){
            $result = $this->createUpgradeOrder($param);
        }else if($param['type']=='upgrade_config'){
            $result = $this->createUpgradeConfigOrder($param);
        }else if($param['type'] == 'change_billing_cycle'){
            // 新增变更计费周期订单类型
            $result = $this->createChangeBillingCycleOrder($param);
        }else{
            $this->startTrans();
            try {
                // 按需订单,都是单个产品
                if($param['type'] == 'on_demand'){
                    if(empty($param['items'])){
                        $param['items'] = [
                            [
                                'description'   => $param['description'],
                                'amount'        => $param['amount'],
                                'host_id'       => $param['host_id'],
                                'product_id'    => $param['product_id'] ?? 0,
                                'type'          => 'on_demand',
                            ],
                        ];
                    }
                }else{
                    $param['items'][] = [
                        'description'   => $param['description'],
                        'amount'        => $param['amount'],
                        'product_id'    => $param['product_id'] ?? 0,
                        'type'          => $param['item_type'] ?? '',
                    ];
                }
                $id = $this->createOrderBase($param);

                hook('after_order_create',['id'=>$id,'customfield'=>$param['customfield']??[]]);
                # 记录日志

                // wyh 20240402 新增 支付后跳转地址
                $domain = configuration('website_url');
                $returnUrl = "{$domain}/finance.htm";
                $OrderModel = new OrderModel();
                $OrderModel->update([
                    'return_url' => $returnUrl,
                ],['id'=>$id]);

                $this->commit();
            } catch (\Exception $e) {
                // 回滚事务
                $this->rollback();
                return ['status' => 400, 'msg' => $e->getMessage()];
            }
            if($param['type'] == 'on_demand'){
                active_log(lang('admin_create_on_demand_order', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$client->username.'#', '{order}'=>'#'.$id]), 'order', $id);
            }else{
                active_log(lang('admin_create_artificial_order', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$client->username.'#', '{order}'=>'#'.$id]), 'order', $id);
            }

            // amount可能会被改
            $amount = $this->where('id', $id)->value('amount') ?? 0;

            $result = ['status' => 200, 'msg' => lang('create_success'), 'data' => ['id' => $id, 'amount'=>$amount]];
        }

        if ($result['status']==200 && in_array($param['type'],['upgrade','upgrade_config','renew'])){
            $HostModel = new HostModel();
            $host = $HostModel->find($param['host_id']);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$host['product_id']??0)->find();
            if (!empty($upstreamProduct) && $upstreamProduct['mode']=='sync'){
                UpstreamOrderModel::create([
                    'supplier_id' 	=> $upstreamProduct['supplier_id'],
                    'order_id' 		=> $result['data']['id'],
                    'host_id' 		=> $param['host_id'],
                    'amount' 		=> $param['amount'],
                    'profit' 		=> $param['amount'], // 此处以购买金额作为利润，在上游下单后更改利润
                    'create_time' 	=> time(),
                ]);
            }
        }
    
        return $result;
    }

    # 新订单
    private function createNewOrder($param)
    {
        $amount = 0;
        $products = $param['products'] ?? [];
        if(empty($products)){
            return ['status'=>400, 'msg'=>lang('please_select_product')];
        }
        $credit = ClientModel::where('id', $param['client_id'])->value('credit');

        $appendOrderItem = [];
        $ModuleLogic = new ModuleLogic();
        $ProductOnDemandModel = new ProductOnDemandModel();
        foreach ($products as $key => $value) {
            $product = ProductModel::find($value['product_id']);
            if(empty($product)){
                return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
            }
            $value['config_options'] = $value['config_options'] ?? [];
            
            $upstreamProduct = UpstreamProductModel::where('product_id', $value['product_id'])->find();

            if($upstreamProduct){
                $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                $result = $ResModuleLogic->cartCalculatePrice($product, $value['config_options']);
            }else{
                $result = $ModuleLogic->cartCalculatePrice($product, $value['config_options'], 1, 'buy', $key);
            }
            if($result['status']!=200){
                return $result;
            }
            if($product['pay_type']=='free'){
                $result['data']['price'] = 0;
            }
            $appendOrderItem = $result['data']['order_item'] ?? [];
            $result['data']['price'] = isset($value['price']) ? $value['price'] : $result['data']['price'];
            $amount +=  $result['data']['price'] *  $value['qty'];
            $products[$key] = $value;
            // wyh 20240428 修改bug，追加子项会被最后一个产品子项覆盖
            $cartData[$key]['order_item'] = $result['data']['order_item'] ?? [];
            $products[$key]['price'] = $result['data']['price'];
            $products[$key]['discount'] = $result['data']['discount'] ?? 0;
            $products[$key]['renew_price'] = $result['data']['renew_price'] ?? $products[$key]['price'];
            $products[$key]['billing_cycle'] = $result['data']['billing_cycle'];
            $products[$key]['duration'] = $result['data']['duration'];
            $products[$key]['description'] = $result['data']['description'];
            // 产品费用类型
            $products[$key]['host_billing_cycle'] = $product['pay_type'] == 'recurring_prepayment_on_demand' ? ($result['data']['host_billing_cycle'] ?? 'recurring_prepayment') : $product['pay_type'];
            // 按需时获取出账周期,验证余额
            if($products[$key]['host_billing_cycle'] == 'on_demand'){
                $products[$key]['keep_time_price'] = $result['data']['keep_time_price'] ?? '0.0000';
                $products[$key]['on_demand_flow_price'] = $result['data']['on_demand_flow_price'] ?? '0.0000';
                $productOnDemand = ProductOnDemandModel::getProductOnDemand($value['product_id']);
                if(!empty($productOnDemand['min_credit']) && $productOnDemand['min_credit'] > $credit){
                    return ['status'=>400, 'msg'=>lang('product_on_demand_buy_need_min_credit', ['{product}'=>$product['name'], '{credit}'=>$productOnDemand['min_credit'] ]) ];
                }
                $products[$key]['on_demand_billing_cycle_unit'] = $productOnDemand['billing_cycle_unit'];
                $products[$key]['on_demand_billing_cycle_day'] = $productOnDemand['billing_cycle_day'];
                $products[$key]['on_demand_billing_cycle_point'] = $productOnDemand['billing_cycle_point'];
            }else{
                // 设定默认值
                $products[$key]['keep_time_price'] = '0.0000';
                $products[$key]['on_demand_flow_price'] = '0.0000';
                $products[$key]['on_demand_billing_cycle_unit'] = 'hour';
                $products[$key]['on_demand_billing_cycle_day'] = 1;
                $products[$key]['on_demand_billing_cycle_point'] = '00:00';
            }
            // 代理商品不支持按需购买
            if($upstreamProduct && $products[$key]['host_billing_cycle'] == 'on_demand'){
                return ['status'=>400, 'msg'=>lang('upstream_product_cannot_on_demand_buy', ['{product}'=>$product['name']]) ];
            }
            $products[$key]['discount_renew_price'] = 0;
            $products[$key]['renew_use_current_client_level'] = 0;
            if(isset($result['data']['discount_renew_price']) && is_numeric($result['data']['discount_renew_price']) ){
                $products[$key]['discount_renew_price'] = $result['data']['discount_renew_price'];
                $products[$key]['renew_use_current_client_level'] = 1;
            }
        }
        $this->startTrans();
        try {
            /*$gateway = gateway_list();
            $gateway = $gateway['list'][0]??[];*/

            // 创建订单
            $clientId = $param['client_id'];
            $time = time();
            $order = $this->create([
                'client_id' => $clientId,
                'type' => 'new',
                'status' => $amount>0 ? 'Unpaid' : 'Paid',
                'amount' => $amount,
                'credit' => 0,
                'amount_unpaid' => $amount,
                //'gateway' => $gateway['name'] ?? '',
                //'gateway_name' => $gateway['title'] ?? '',
                'pay_time' => $amount>0 ? 0 : $time ,
                'create_time' => $time,
                'admin_id' => get_admin_id(),
            ]);
            
            // 创建产品
            $ModuleLogic = new ModuleLogic();
            $orderItem = [];
            $hostIds = [];
            foreach ($products as $key => $value) {
                $product = ProductModel::find($value['product_id']);
                if($product['stock_control']==1){
                    if($product['qty']<$value['qty']){
                        throw new \Exception(lang('product_inventory_shortage'));
                    }
                    ProductModel::where('id', $value['product_id'])->dec('qty', $value['qty'])->update();
                }
                if($product['type']=='server_group'){
                    if($product['rel_id'] > 0){
                        $server = ServerModel::where('server_group_id', $product['rel_id'])->where('status', 1)->find();
                        $serverId = $server['id'] ?? 0;
                    }else{
                        $serverId = 0;
                    }
                }else{
                    $serverId = $product['rel_id'];
                }
                for ($i=1; $i<=$value['qty']; $i++) {
                    $host = HostModel::create([
                        'client_id' => $clientId,
                        'order_id' => $order->id,
                        'product_id' => $value['product_id'],
                        'server_id' => $serverId,
                        'name' => generate_host_name($value['product_id']),
                        'status' => 'Unpaid',
                        'first_payment_amount' => $value['price'],
                        'renew_amount' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment' || $value['host_billing_cycle'] == 'on_demand') ? $value['renew_price'] : 0,
                        'billing_cycle' => $value['host_billing_cycle'],
                        'billing_cycle_name' => $value['billing_cycle'],
                        'billing_cycle_time' => $value['duration'],
                        'active_time' => $time,
                        'due_time' => !in_array($value['host_billing_cycle'], ['on_demand','onetime']) ? $time : 0,
                        'create_time' => $time,
                        'keep_time_price' => $value['keep_time_price'],
                        'on_demand_flow_price' => $value['on_demand_flow_price'],
                        'on_demand_billing_cycle_unit' => $value['on_demand_billing_cycle_unit'],
                        'on_demand_billing_cycle_day' => $value['on_demand_billing_cycle_day'],
                        'on_demand_billing_cycle_point' => $value['on_demand_billing_cycle_point'],
                        'discount_renew_price' => $value['discount_renew_price'],
                        'renew_use_current_client_level' => $value['renew_use_current_client_level'],
                    ]);

                    $hostIds[] = $host->id;

                    // 产品和对应自定义字段
                    $param['customfield']['host_customfield'][] = ['id'=>$host->id, 'customfield' => $value['customfield'] ?? []];

                    $upstreamProduct = UpstreamProductModel::where('product_id', $value['product_id'])->find();

                    if($upstreamProduct){
                        $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                        $ResModuleLogic->afterSettle($product, $host->id, $value['config_options'], $key);
                    }else{
                        $ModuleLogic->afterSettle($product, $host->id, $value['config_options'], $key);
                    }
                    $orderItem[] = [
                        'order_id' => $order->id,
                        'client_id' => $clientId,
                        'host_id' => $host->id,
                        'product_id' => $value['product_id'],
                        'type' => 'host',
                        'rel_id' => $host->id,
                        'amount' => bcadd($value['price'], $value['discount']),
                        'description' => $value['description'],
                        'create_time' => $time,
                    ];

                    // wyh 20240428 修改bug，追加子项会被最后一个产品子项覆盖
                    if (!empty($value['order_item'])){
                        foreach($value['order_item'] as $v){
                            $v['order_id'] = $order->id;
                            $v['client_id'] = $clientId;
                            $v['host_id'] = $host->id;
                            $v['product_id'] = $value['product_id'];
                            $v['create_time'] = $time;
                            $orderItem[] = $v;
                        }
                    }
                }
            }

            // 创建订单子项
            $OrderItemModel = new OrderItemModel();
            $OrderItemModel->saveAll($orderItem);

            hook('after_order_create',['id'=>$order->id,'customfield'=>$param['customfield']??[]]);

            update_upstream_order_profit($order->id);

            # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
            $amount = $this->where('id',$order->id)->value('amount');

            if($amount<=0){
                $this->processPaidOrder($order->id);
            }

            $client = ClientModel::find($clientId);
            # 记录日志
            active_log(lang('admin_create_new_purchase_order', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$client->username.'#', '{order}'=>'#'.$order->id]), 'order', $order->id);
			
            // wyh 20240402 新增 支付后跳转地址
            $domain = configuration('website_url');
            if (count($hostIds)>1){
                $returnUrl = "{$domain}/finance.htm";
            }else{
                if (isset($hostIds[0]) && !empty($hostIds[0])){
                    $returnUrl = "{$domain}/productdetail.htm?id=".$hostIds[0];
                }else{
                    $returnUrl = "{$domain}/finance.htm";
                }
            }
            $order->save([
                'return_url' => $returnUrl,
            ]);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }

        system_notice([
            'name'                  => 'order_create',
            'email_description'     => lang('order_create_send_mail'),
            'sms_description'       => lang('order_create_send_sms'),
            'task_data' => [
                'client_id' => $clientId,
                'order_id'  => $order->id,
            ],
        ]);

        return ['status' => 200, 'msg' => lang('success_message'), 'data' => ['id' => $order->id]];
    }

    /**
     * 时间 2022-07-01
     * @title 获取升降级订单金额
     * @desc 获取升降级订单金额
     * @author theworld
     * @version v1
     * @param int host_id - 产品ID required
     * @param object product - 升降级商品 required
     * @param int product.product_id - 商品ID
     * @param object product.config_options - 自定义配置
     * @param float product.price - 商品价格
     * @param int client_id - 用户ID required
     * @return string refund - 原产品应退款金额
     * @return string pay - 新产品应付金额
     * @return string amount - 升降级订单金额,前两者之差
     */
    public function getUpgradeAmount($param)
    {
        $hostId = $param['host_id'] ?? 0;
        $host = HostModel::find($hostId);
        if(empty($host) || $host['is_delete']){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        if($host['client_id']!=$param['client_id']){
            return ['status'=>400, 'msg'=>lang('client_host_error')];
        }
        if($host['status']!='Active'){
            return ['status'=>400, 'msg'=>lang('active_host_can_be_upgraded')];
        }
        $oldProduct = ProductModel::find($host['product_id']);
        $upgradeProductId = ProductUpgradeProductModel::where('product_id', $host['product_id'])->column('upgrade_product_id');
        if(!in_array($param['product']['product_id'], $upgradeProductId)){
            return ['status'=>400, 'msg'=>lang('host_cannot_be_upgraded_to_the_product')];
        }
        $param['product']['product_id'] = $param['product']['product_id'] ?? 0;
        $param['product']['config_options'] = $param['product']['config_options'] ?? [];

        $product = ProductModel::find($param['product']['product_id']);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        
        $upstreamProduct = UpstreamProductModel::where('product_id', $product['id'])->find();

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $result = $ResModuleLogic->cartCalculatePrice($product, $param['product']['config_options']);
        }else{
            $ModuleLogic = new ModuleLogic();
            $result = $ModuleLogic->cartCalculatePrice($product, $param['product']['config_options']);
        }
        if($result['status']!=200){
            return $result;
        }
        if($product['pay_type']=='free'){
            $result['data']['price'] = 0;
        }

        $result['data']['price'] = isset($param['product']['price']) ? $param['product']['price'] : $result['data']['price'];
        $time = time(); // 获取当前时间

        // 计算退款金额
        if($oldProduct['pay_type']=='onetime'){
            $refund = $host['first_payment_amount'];
        }else if($oldProduct['pay_type']=='free'){
            $refund = 0;
        }else{
            if($host['billing_cycle_time']>0){
                if(($host['due_time']-$time)>0){
                    $refund = bcdiv($host['first_payment_amount']/$host['billing_cycle_time']*($host['due_time']-$time), 1, 2);
                }else{
                    $refund = $host['first_payment_amount'];
                }
            }else{
                $refund = $host['first_payment_amount'];
            }
        }

        if($product['pay_type']=='onetime'){
            $pay = $result['data']['price'];
        }else if($product['pay_type']=='free'){
            $pay = 0;
        }else{
            if($result['data']['duration']>0){
                if(($host['due_time']-$time)>0){
                    $pay = bcdiv($result['data']['price']/$result['data']['duration']*($host['due_time']-$time), 1, 2);
                }else{
                    $pay = $result['data']['price'];
                }
            }else{
                $pay = $result['data']['price'];
            }
        }
        $amount = bcsub($pay, $refund, 2);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'refund' => amount_format($refund), 
                'pay' => amount_format($pay), 
                'amount' => amount_format($amount)
            ]
        ];
        return $result;
    }

    # 删除产品未付款升降级订单
    public function deleteHostUnpaidUpgradeOrder($id)
    {
        $OrderModel = new OrderModel();

        $orderIds = $OrderModel->alias('o')
            ->leftJoin('order_item oi','oi.order_id=o.id')
            ->where('oi.host_id',$id)
            ->where('o.type','upgrade')
            ->whereIn('o.status',['Unpaid','WaitUpload','WaitReview','ReviewFail'])
            ->group('o.id')
            ->column('o.id');
        if (!empty($orderIds)){
            // 外层必须捕获异常,这个hook会抛出异常
            hook('before_delete_host_unpaid_upgrade_order', ['id'=>$orderIds]);
            // 删除 未支付升降级订单日志
//            foreach ($orderIds as $orderId){
//                active_log("删除未支付续费订单#".$orderId,'order',$orderId);
//            }
            $this->cancelUserCustomOrder($orderIds);

            $OrderModel->whereIn('id',$orderIds)->delete();
            $OrderItemModel = new OrderItemModel();
            $OrderItemModel->whereIn('order_id',$orderIds)->delete();
            # 删除升降级数据
            $UpgradeModel = new UpgradeModel();
            foreach ($orderIds as $orderId){
                $UpgradeModel->where('order_id',$orderId)
                    ->where('host_id',$id)
                    ->delete();
            }
        }

        return true;
    }

    /**
     * 时间 2024-01-22
     * @title 删除未支付的续费类型订单
     * @desc  删除未支付的续费类型订单
     * @author hh
     * @version v1
     * @throws \Exception 可能抛出异常,需要catch
     * @param   int $id - 产品ID require
     */
    public function deleteUnpaidRenewOrder($id,$orderId=0)
    {
        if (empty($id)){
            return false;
        }
        $OrderModel = new OrderModel();

        $unpaidRenewOrders = $OrderModel->alias('o')
            ->field('oi.order_id')
            ->leftJoin('order_item oi','oi.order_id=o.id')
            ->where('oi.type','renew')
            ->where('oi.host_id',$id)
            ->whereIn('o.status',['Unpaid','WaitUpload','WaitReview','ReviewFail'])
            ->where('o.id','<>',$orderId)
            ->select()->toArray();
        if (!empty($unpaidRenewOrders)){
            // 删除 未支付续费订单日志
            /*foreach ($unpaidRenewOrders as $unpaidRenewOrder){
                active_log("删除未支付续费订单#".$unpaidRenewOrder['order_id'],'order',$unpaidRenewOrder['order_id']);
            }*/
            $orderIds = array_column($unpaidRenewOrders,'order_id');

            // 这个hook会抛出异常
            hook('before_delete_unpaid_renew_order', ['id'=>$orderIds]);

            $this->cancelUserCustomOrder($orderIds);

            $OrderItemModel = new OrderItemModel();
            $renewIds = $OrderItemModel->whereIn('order_id',$orderIds)
                ->where('type','renew')
                ->column('rel_id');
            $OrderItemModel->whereIn('order_id',$orderIds)->delete();
            $OrderModel->whereIn('id',$orderIds)->delete();
            // 问题所在
            if (class_exists("addon\idcsmart_renew\model\IdcsmartRenewModel")){
                $IdcsmartRenewModel = new \addon\idcsmart_renew\model\IdcsmartRenewModel();
                $IdcsmartRenewModel->whereIn('id',$renewIds)->delete();
            }

            $UpstreamOrderModel = new UpstreamOrderModel();
            $UpstreamOrderModel->whereIn('order_id',$orderIds)->delete();
        }

        return true;
    }


    # 升降级订单
    public function createUpgradeOrder($param)
    {
        $hostId = $param['host_id'] ?? 0;
        $host = HostModel::find($hostId);
        if(empty($host) || $host['is_delete']){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        if($host['client_id']!=$param['client_id']){
            return ['status'=>400, 'msg'=>lang('client_host_error')];
        }
        if($host['status']!='Active'){
            return ['status'=>400, 'msg'=>lang('active_host_can_be_upgraded')];
        }
        $oldProduct = ProductModel::find($host['product_id']);
        $upgradeProductId = ProductUpgradeProductModel::where('product_id', $host['product_id'])->column('upgrade_product_id');
        if(!in_array($param['product']['product_id'], $upgradeProductId)){
            return ['status'=>400, 'msg'=>lang('host_cannot_be_upgraded_to_the_product')];
        }
        $param['product']['product_id'] = $param['product']['product_id'] ?? 0;
        $param['product']['config_options'] = $param['product']['config_options'] ?? [];

        $product = ProductModel::find($param['product']['product_id']);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        
        $upstreamProduct = UpstreamProductModel::where('product_id', $product['id'])->find();

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $result = $ResModuleLogic->cartCalculatePrice($product, $param['product']['config_options']);
        }else{
            $ModuleLogic = new ModuleLogic();
            $result = $ModuleLogic->cartCalculatePrice($product, $param['product']['config_options']);
        }

        if($result['status']!=200){
            return $result;
        }
        if($product['pay_type']=='free'){
            $result['data']['price'] = 0;
        }

        $result['data']['price'] = isset($param['product']['price']) ? $param['product']['price'] : $result['data']['price'];
        $result['data']['renew_price'] = isset($param['product']['price']) ? $param['product']['price'] : ($result['data']['renew_price'] ?? $result['data']['price']);
        $time = time(); // 获取当前时间
        
        $this->startTrans();
        try {
            // wyh 20230508 删除未支付升降级订单
            $this->deleteHostUnpaidUpgradeOrder($hostId);

            $product = ProductModel::find($param['product']['product_id']);
            if($product['stock_control']==1){
                if($product['qty']<1){
                    throw new \Exception(lang('product_inventory_shortage'));
                }
                ProductModel::where('id', $param['product']['product_id'])->dec('qty', 1)->update();
            }

            // 计算退款金额
            if($oldProduct['pay_type']=='onetime'){
                $refund = $host['first_payment_amount'];
            }else if($oldProduct['pay_type']=='free'){
                $refund = 0;
            }else if($host['billing_cycle'] == 'on_demand'){
                $refund = 0;
            }else{
                if($host['billing_cycle_time']>0){
                    if(($host['due_time']-$time)>0){
                        //$refund = bcdiv($host['first_payment_amount']/$host['billing_cycle_time']*($host['due_time']-$time), 1, 2);
                        $hookResult = hook_one('renew_host_refund_amount',['id'=>$hostId]);
                        $renewRefundTotal = $hookResult[0]??0; // 总续费退款
                        $renewCycleTotal = $hookResult[1]??0; // 总续费周期
                        if (isset($hookResult[2]) && $hookResult[2]){
                            $refund = $renewRefundTotal;
                        }else{
                            $hostBillingCycleTime = $host['due_time']-$renewCycleTotal-$host['active_time']; // 产品购买周期=(总到期时间-续费周期-开通时间)
                            $refund = bcdiv(bcdiv($host['first_payment_amount'],$hostBillingCycleTime,20)*($host['due_time']-$renewCycleTotal-$time), 1, 2);
                            $refund = bcadd($refund,$renewRefundTotal,2);
                        }
                    }else{
                        $refund = $host['first_payment_amount'];
                    }
                }else{
                    $refund = $host['first_payment_amount'];
                }
                
            }

            /*if($product['pay_type']=='onetime'){
                $amount = bcsub($result['data']['price'], $refund, 2);
            }else if($product['pay_type']=='free'){
                $amount = bcsub(0, $refund, 2);
            }else{
                if($result['data']['duration']>0){
                    if(($host['due_time']-$time)>0){
                        $amount = bcsub($result['data']['price']/$result['data']['duration']*($host['due_time']-$time), $refund, 2);
                    }else{
                        $amount = bcsub($result['data']['price'], $refund, 2);
                    }
                }else{
                    $amount = bcsub($result['data']['price'], $refund, 2);
                }
            }*/
            //计算应付金额
            if($product['pay_type']=='onetime'){
                $pay = $result['data']['price'];
            }else if($product['pay_type']=='free'){
                $pay = 0;
            }else if($host['billing_cycle'] == 'on_demand'){
                $pay = $result['data']['price'];
            }else{
                if($result['data']['duration']>0){
                    if(($host['due_time']-$time)>0){
                        $pay = $result['data']['price'];//bcdiv($result['data']['price']/$result['data']['duration']*($host['due_time']-$time), 1, 2);
                    }else{
                        $pay = $result['data']['price'];
                    }
                }else{
                    $pay = $result['data']['price'];
                }
            }

            // 计算差价
            $amount = bcsub($pay, $refund, 2);
            
            $param['upgrade_refund'] = $param['upgrade_refund'] ?? 1; // 是否退款,默认退款

            /*$gateway = gateway_list();
            $gateway = $gateway['list'][0]??[];*/

            // 创建订单
            $order = $this->create([
                'client_id' => $host['client_id'],
                'type' => 'upgrade',
                'status' => $amount>0 ? 'Unpaid' : 'Paid',
                'amount' => $amount,
                'credit' => 0,
                'amount_unpaid' => $amount>0 ? $amount : 0,
                'upgrade_refund' => $param['upgrade_refund'],
                //'gateway' => $gateway['name'] ?? '',
                //'gateway_name' => $gateway['title'] ?? '',
                'pay_time' => $amount>0 ? 0 : $time,
                'create_time' => $time,
                'admin_id' => get_admin_id(),
            ]);
            // 20231225 新增
            if (isset($result['data']['base_price_son'])){
                $pay = $result['data']['base_price'];
                $param['product']['config_options']['base_price_son'] = $result['data']['base_price_son'];
            }
            // 创建升降级
            $param['product']['config_options']['old_product_id'] = $oldProduct['id'];
            $upgrade = UpgradeModel::create([
                'client_id' => $host['client_id'],
                'order_id' => $order->id,
                'host_id' => $host['id'],
                'type' => 'product',
                'rel_id' => $param['product']['product_id'],
                'data' => json_encode($param['product']['config_options']),
                'amount' => $amount,
                'price' => $result['data']['price'],
                'renew_price' => in_array($host['billing_cycle'], ['recurring_prepayment','on_demand']) ? $result['data']['renew_price'] : 0,
                'billing_cycle_name' => $result['data']['billing_cycle'],
                'billing_cycle_time' => $result['data']['duration'],
                'status' => $amount>0 ? 'Unpaid' : 'Pending',
                'description' => $result['data']['description'],
                'create_time' => $time,
                'base_price' => max($pay, 0),
            ]);

            if (isset($result['data']['description']) && is_array($result['data']['description'])){
                $result['data']['description'] = implode("\n",$result['data']['description']);
            }
            // 创建订单子项
            $orderItem = OrderItemModel::create([
                'order_id' => $order->id,
                'client_id' => $host['client_id'],
                'host_id' => $host['id'],
                'product_id' => $param['product']['product_id'],
                'type' => 'upgrade',
                'rel_id' => $upgrade->id,
                'description' => $result['data']['description'],
                'amount' => $amount,
                //'gateway' => $gateway['name'] ?? '',
                //'gateway_name' => $gateway['title'] ?? '',
                'notes' => '',
                'create_time' => $time,
            ]);

            hook('after_order_create',['id'=>$order->id,'customfield'=>$param['customfield']??[]]);

            update_upstream_order_profit($order->id);

            # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
            $amount = $this->where('id',$order->id)->value('amount');

            // wyh 20230531 升降级统一处理续费金额：续费差价 = 升降级金额 - (优惠码*升降级金额) - （客户等级*升降级金额）
            // 续费差价<0
            //$renewPriceDifference = bcsub($param['amount'],)
            $baseRenewPrice = $result['data']['renew_price'];
            $discountPromo = 0;
            $promoCodeExcludeWithClientLevel = false; // hh 20260227 标记循环优惠码是否与用户等级互斥
            $hookPromoCodeResults = hook('apply_promo_code',['host_id'=>$host->id,'product_id'=>$param['product']['product_id'],'price'=>$baseRenewPrice,'scene'=>'upgrade','duration'=>$host['billing_cycle_time']]);
            foreach ($hookPromoCodeResults as $hookPromoCodeResult){
                if ($hookPromoCodeResult['status']==200){
                    if (isset($hookPromoCodeResult['data']['loop']) && $hookPromoCodeResult['data']['loop']){
                        $discountPromo = $hookPromoCodeResult['data']['discount']??0;
                        // hh 20260227 检查循环优惠码是否与用户等级互斥
                        $promoCodeExcludeWithClientLevel = !empty($hookPromoCodeResult['data']['exclude_with_client_level']);
                    }
                }
            }
            $renewPrice = bcsub($baseRenewPrice, $discountPromo, 4);
            // hh 20260227 如果循环优惠码与用户等级互斥，则不应用用户等级折扣
            if(!$promoCodeExcludeWithClientLevel){
                $hookDiscountResults = hook("client_discount_by_amount",['client_id'=>$host['client_id'],'product_id'=>$param['product']['product_id'],'amount'=>$baseRenewPrice]);
                foreach ($hookDiscountResults as $hookDiscountResult){
                    if ($hookDiscountResult['status']==200){
                        $renewPrice = bcsub($renewPrice, $hookDiscountResult['data']['discount']??0, 4);
                    }
                }
            }
            $renewPrice = max($renewPrice, 0);
            $upgrade->save([
                'renew_price' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment')?$renewPrice:0
            ]);

            $product = (new ProductModel())->find($host['product_id']);
            if (in_array($host['billing_cycle'],['onetime','free','on_demand'])){
                $desDueTime = '∞';
            }else{
                $desDueTime = date('Y/m/d',$host['due_time']);
            }

            $billingCycleName = multi_language_replace($host['billing_cycle_name']);
            
            //$des = $product['name'] . '(' .$host['name']. '),'.lang('purchase_duration').':'.$host['billing_cycle_name'] .'(' . date('Y/m/d',$host['active_time']) . '-'. $desDueTime .')';
            //$des = lang('order_description_append',['{product_name}'=>$product['name'],'{name}'=>$host['name'],'{billing_cycle_name}'=>$host['billing_cycle_name'],'{time}'=>date('Y/m/d',$host['active_time']) . '-' . $desDueTime]);
            $des = lang('order_description_append',['{product_name}'=>$product->name,'{name}'=>$host['name'],'{billing_cycle_name}'=>$billingCycleName,'{time}'=>date('Y/m/d',time()) . '-' . $desDueTime]);
            $newOrderItem = OrderItemModel::find($orderItem['id']);
            $newOrderItem->save([
                'description' => $newOrderItem['description'] . "\n" . $des
            ]);
            
            if($amount<=0){
                // 获取接口
                /*if($product['type']=='server_group'){
                    $server = ServerModel::where('server_group_id', $product['rel_id'])->where('status', 1)->find();
                    $serverId = $server['id'] ?? 0;
                }else{
                    $serverId = $product['rel_id'];
                }

                if($oldProduct['stock_control']==1){
                    ProductModel::where('id', $host['product_id'])->inc('qty', 1)->update();
                }
                HostModel::update([
                    'product_id' => $param['product']['product_id'],
                    'server_id' => $serverId,
                    'first_payment_amount' => $result['data']['price'],
                    'renew_amount' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $result['data']['renew_price'] : 0,
                    'billing_cycle' => $product['pay_type'],
                    'billing_cycle_name' => $result['data']['billing_cycle'],
                    'billing_cycle_time' => $result['data']['duration'],
                ],['id' => $host['id']]);
                $ModuleLogic = new ModuleLogic();
                $host = HostModel::find($host['id']);
                $ModuleLogic->changeProduct($host, $param['product']['config_options']);*/

                // 退款到余额
                if($amount<0 && $param['upgrade_refund']==1){
                    $result = update_credit([
                        'type' => 'Refund',
                        'amount' => -$amount,
                        'notes' => lang('upgrade_refund'),
                        'client_id' => $host['client_id'],
                        'order_id' => $order->id,
                        'host_id' => $host['id']
                    ]);
                    if(!$result){
                        throw new \Exception(lang('fail_message'));           
                    }
                }else if($amount<0 && $param['upgrade_refund']!=1){
                    OrderItemModel::create([
                        'type' => 'manual',
                        'order_id' => $order->id,
                        'client_id' => $host['client_id'],
                        'description' => lang('update_amount'),
                        'amount' => -$amount,
                        'create_time' => $time
                    ]);
                    $this->update([
                        'amount' => 0,
                    ], ['id' => $order->id]);

                }

                $this->processPaidOrder($order->id);
            }

            $client = ClientModel::find($host['client_id']);
            # 记录日志
            active_log(lang('admin_create_upgrade_order', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$client->username.'#', '{order}'=>'#'.$order->id]), 'order', $order->id);

            // wyh 20240402 新增 支付后跳转地址
            $domain = configuration('website_url');
            $returnUrl = "{$domain}/productdetail.htm?id=".$hostId;
            $order->save([
                'return_url' => $returnUrl,
            ]);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage() ];
        }

        return ['status' => 200, 'msg' => lang('success_message'), 'data' => ['id' => $order->id,'amount'=>$amount??0]];


    }

    # 升降级配置订单
    public function createUpgradeConfigOrder($param)
    {
        $hostId = $param['host_id'] ?? 0;
        $host = HostModel::find($hostId);
        if(empty($host) || $host['is_delete']){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        if($host['client_id']!=$param['client_id']){
            return ['status'=>400, 'msg'=>lang('client_host_error')];
        }
        if($host['status']!='Active'){
            return ['status'=>400, 'msg'=>lang('active_host_can_be_upgraded')];
        }

        $param['config_options'] = $param['config_options'] ?? [];
        $param['renew_price_difference'] = $param['renew_price_difference'] ?? $param['price_difference'];
        $param['base_renew_price'] = bcadd($host['base_renew_amount'], $param['renew_price_difference'], 4);
        $param['base_renew_price'] = $param['base_renew_price'] > 0 ? $param['base_renew_price'] : 0;

        $time = time(); // 获取当前时间
        $appendOrderItem = $param['order_item'] ?? [];

        //$this->startTrans();
        //try {
            // hh 20240410 产品升降级时 判断是否有正在执行的升降级，有则拦截订单 TAPD-ID1005869
            $task = TaskModel::where('rel_id', $hostId)->where('type', 'host_upgrade')->whereRaw("(status='Wait' OR (status='Exec' AND start_time>=".($time-600).'))')->find();
            if(!empty($task)){
                throw new \Exception(lang('order_host_is_upgrade_please_wait_and_retry'));
            }
            
            // wyh 20230508 删除未支付升降级订单
            $this->deleteHostUnpaidUpgradeOrder($hostId);

            // 金额
            $amount = $param['amount'];
            $discount = $param['discount'] ?? 0; // 用户等级折扣 
            
            $param['upgrade_refund'] = $param['upgrade_refund'] ?? 1; // 是否退款,默认退款

            /*$gateway = gateway_list();
            $gateway = $gateway['list'][0]??[];*/

            // 创建订单
            $order = $this->create([
                'client_id' => $host['client_id'],
                'type' => 'upgrade',
                'status' => $amount>0 ? 'Unpaid' : 'Paid',
                'amount' => $amount,
                'credit' => 0,
                'amount_unpaid' => $amount>0 ? $amount : 0,
                'upgrade_refund' => $param['upgrade_refund'],
                //'gateway' => $gateway['name'] ?? '',
                //'gateway_name' => $gateway['title'] ?? '',
                'pay_time' => $amount>0 ? 0 : $time,
                'create_time' => $time,
                'admin_id' => get_admin_id(),
            ]);
            $param['base_price'] = max($param['base_price'], 0);
            // 创建升降级
            $upgradeData = [
                'client_id' => $host['client_id'],
                'order_id' => $order->id,
                'host_id' => $host['id'],
                'type' => 'config_option',
                'rel_id' => 0,
                'data' => json_encode($param['config_options']),
                'amount' => $amount,
                'price' => ($host['first_payment_amount']+$param['price_difference'])>0 ? ($host['first_payment_amount']+$param['price_difference']) : 0,
                // 'renew_price' => ($host['billing_cycle']=='recurring_postpaid' || $host['billing_cycle']=='recurring_prepayment') ? (($host['renew_amount']+$param['renew_price_difference'])>0 ? ($host['renew_amount']+$param['renew_price_difference']) : 0) : 0,
                'renew_price' => ($host['billing_cycle']=='recurring_postpaid' || $host['billing_cycle']=='recurring_prepayment' || $host['billing_cycle'] == 'on_demand') ? max(bcadd($host['upgrade_renew_cal']?$host['base_price']:$host['renew_amount'], $param['renew_price_difference'], 4), 0) : 0, // 修改，升降级时是否按原价处理续费金额
                'billing_cycle_name' => $host['billing_cycle_name'],
                'billing_cycle_time' => $host['billing_cycle_time'],
                'status' => $amount>0 ? 'Unpaid' : 'Pending',
                'description' => $param['description'] ?? '',
                'create_time' => $time,
                'base_price' => $param['base_price']??0,
                'on_demand_flow_price' => $param['on_demand_flow_price'] ?? -1,
                'keep_time_price' => $host['billing_cycle'] == 'on_demand' && isset($param['keep_time_price_difference']) ? max(bcadd($host['keep_time_price'], $param['keep_time_price_difference'], 4), 0)  : -1,
                'base_renew_price' => $param['base_renew_price'] ?? 0,
                'renew_price_difference_client_level_discount' => $param['renew_discount_price_difference'] ?? '0.0000',
            ];
            $upgrade = UpgradeModel::create($upgradeData);
            // 创建订单子项
            if (isset($param['description']) && is_array($param['description'])){
                $param['description'] = implode("\n",$param['description']);
            }
            $orderItem = OrderItemModel::create([
                'order_id' => $order->id,
                'client_id' => $host['client_id'],
                'host_id' => $host['id'],
                'product_id' => $host['product_id'],
                'type' => 'upgrade',
                'rel_id' => $upgrade->id,
                'description' => ($param['description'] ?? ''),
                'amount' => bcadd($amount, $discount, 2),
                //'gateway' => $gateway['name'] ?? '',
                //'gateway_name' => $gateway['title'] ?? '',
                'notes' => '',
                'create_time' => $time,
            ]);

            if(!empty($appendOrderItem)){
                foreach($appendOrderItem as $k=>$v){
                    $appendOrderItem[$k]['order_id'] = $order->id;
                    $appendOrderItem[$k]['client_id'] = $host['client_id'];
                    $appendOrderItem[$k]['host_id'] = $host['id'];
                    $appendOrderItem[$k]['product_id'] = $host['product_id'];
                    $appendOrderItem[$k]['create_time'] = $time;
                }
                $OrderItemModel = new OrderItemModel();
                $OrderItemModel->saveAll($appendOrderItem);
            }

            hook('after_order_create',['id'=>$order->id,'customfield'=>$param['customfield']??[]]);



            update_upstream_order_profit($order->id);

            # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
            $amount = $this->where('id',$order->id)->value('amount');

            $discountPromo = 0;
            $promoCodeExcludeWithClientLevel = false; // hh 20260227 标记循环优惠码是否与用户等级互斥
            $hookPromoCodeResults = hook('apply_promo_code',['host_id'=>$host->id,'price'=>$host['upgrade_renew_cal']?$param['base_price']:$param['renew_price_difference'],'scene'=>'upgrade','duration'=>$host['billing_cycle_time']]);
            foreach ($hookPromoCodeResults as $hookPromoCodeResult){
                if ($hookPromoCodeResult['status']==200){
                    if (isset($hookPromoCodeResult['data']['loop']) && $hookPromoCodeResult['data']['loop']){
                        $discountPromo = $hookPromoCodeResult['data']['discount']??0;
                        // hh 20260227 检查循环优惠码是否与用户等级互斥
                        $promoCodeExcludeWithClientLevel = !empty($hookPromoCodeResult['data']['exclude_with_client_level']);
                    }
                }
            }
//            $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
            // TODO WYH 20240524 修改，不需要此逻辑，升降级时处理了
            // 降级
//            if ($param['renew_price_difference']<0){ // $discountPromo应该是负数
//                // wyh 20231025 获取产品周期原价
//                $amountBase = $amountBase1= 0;
//                $ModuleLogic = new ModuleLogic();
//                if (!empty($upstreamProduct)){
//                    $ResModuleLogic = new ResModuleLogic($upstreamProduct);
//                    $durationResult = $ResModuleLogic->durationPrice($host);
//                }else{
//                    $durationResult = $ModuleLogic->durationPrice($host);
//                }
//
//                $cycles = $durationResult['data']?:[];
//                foreach ($cycles as $item2){
//                    $flag = $host->billing_cycle_time == $item2['duration'] || $host->billing_cycle_name==$item2['billing_cycle'];
//                    if ($flag){
//                        $amountBase = $amountBase1 = $item2['price'];
//                        break; # 只取一个值(存在开发者在模块中把周期写一样的情况)
//                    }
//                }
//
//                $hookPromoCodeResultsOrgins = hook('apply_promo_code',['host_id'=>$host->id,'price'=>$amountBase1,'scene'=>'upgrade','duration'=>$host['billing_cycle_time']]);
//                foreach ($hookPromoCodeResultsOrgins as $hookPromoCodeResultsOrgin){
//                    if ($hookPromoCodeResultsOrgin['status']==200){
//                        if (isset($hookPromoCodeResultsOrgin['data']['loop']) && $hookPromoCodeResultsOrgin['data']['loop']){
//                            $amountBase = $amountBase - ($hookPromoCodeResultsOrgin['data']['discount']??0);
//                        }
//                    }
//                }
//
//                $hookDiscountResultsOrgins = hook("client_discount_by_amount",['client_id'=>$host['client_id'],'product_id'=>$host['product_id'],'amount'=>$amountBase1,'id'=>$order->id]);
//                foreach ($hookDiscountResultsOrgins as $hookDiscountResultsOrgin){
//                    if ($hookDiscountResultsOrgin['status']==200){
//                        $amountBase = $amountBase - ($hookDiscountResultsOrgin['data']['discount']??0);
//                    }
//                }
//                $renewPrice = $amountBase + $param['renew_price_difference']-$discountPromo;
//                // 若有优惠码，折扣金额这样计算！
//            }else{
//                // 升级
//                $renewPrice = $host['renew_amount'] + $param['renew_price_difference'] - $discountPromo;
//            }

            // 按需升降级不能使用循环优惠
            if($host['billing_cycle'] == 'on_demand'){
//                $renewPrice = $host['renew_amount'] + $param['renew_price_difference'];
                $renewPrice = $host['upgrade_renew_cal']?$param['base_price']:($host['renew_amount'] + $param['renew_price_difference']);
                $promoCodeExcludeWithClientLevel = false;
            }else{
                $renewPrice = ($host['upgrade_renew_cal']?$param['base_price']:($host['renew_amount'] + $param['renew_price_difference'])) - $discountPromo;
            }
            
            $updateData = [];
            // 按需模块自行计算
            // if((empty($appendOrderItem) || $host['upgrade_renew_cal'])){
                if(isset($param['renew_discount_price_difference']) && is_numeric($param['renew_discount_price_difference'])){
                    // hh 20260227 如果循环优惠码与用户等级互斥，则不应用用户等级折扣
                    if(!$promoCodeExcludeWithClientLevel){
                        $hookDiscountResults = hook("client_discount_by_amount",[
                            'client_id'     => $host['client_id'],
                            'product_id'    => $host['product_id'],
                            'amount'        => $param['renew_discount_price_difference'],
                            'id'            => $order->id,
                            'scale'         => $host['billing_cycle'] == 'on_demand' ? 4 : 2,
                        ]);
                        foreach ($hookDiscountResults as $hookDiscountResult){
                            if ($hookDiscountResult['status']==200){
                                $discountClient = $hookDiscountResult['data']['discount']??0;
                                $renewPrice = bcsub($renewPrice, $discountClient, 4);
                            }
                        }
                    }
                }else{
                    // hh 20260227 如果循环优惠码与用户等级互斥，则不应用用户等级折扣
                    if(!$promoCodeExcludeWithClientLevel){
                        $hookDiscountResults = hook("client_discount_by_amount",[
                            'client_id'     => $host['client_id'],
                            'product_id'    => $host['product_id'],
                            'amount'        => $host['upgrade_renew_cal']?$param['base_price']:$param['renew_price_difference'],
                            'id'            => $order->id,
                            'scale'         => $host['billing_cycle'] == 'on_demand' ? 4 : 2,
                        ]);
                        foreach ($hookDiscountResults as $hookDiscountResult){
                            if ($hookDiscountResult['status']==200){
                                $discountClient = $hookDiscountResult['data']['discount']??0;
                                $renewPrice = bcsub($renewPrice, $discountClient, 4);
                            }
                        }
                    }
                }
            // }
            // 如果是按需
            if($host['billing_cycle'] == 'on_demand'){
                // 按需流量使用用户等级折扣
                if($upgradeData['on_demand_flow_price'] > 0){
                    $hookDiscountResults = hook("client_discount_by_amount",[
                        'client_id'     => $host['client_id'],
                        'product_id'    => $host['product_id'],
                        'amount'        => $upgradeData['on_demand_flow_price'], 
                        'id'            => $order->id,
                        'scale'         => 4,
                    ]);
                    foreach ($hookDiscountResults as $hookDiscountResult){
                        if ($hookDiscountResult['status']==200){
                            $discountClient = $hookDiscountResult['data']['discount']??0;
                            $upgradeData['on_demand_flow_price'] = bcsub($upgradeData['on_demand_flow_price'], $discountClient, 4);
                        }
                    }
                    $upgradeData['on_demand_flow_price'] = $upgradeData['on_demand_flow_price'] > 0 ? $upgradeData['on_demand_flow_price'] : 0;
                    $updateData['on_demand_flow_price'] = $upgradeData['on_demand_flow_price'];
                }
                // 按需保留期价格使用用户等级折扣
                if($upgradeData['keep_time_price'] > 0){
                    $keepTimePriceDifference = bcsub($upgradeData['keep_time_price'], $host['keep_time_price'], 4);
                    $hookDiscountResults = hook("client_discount_by_amount",[
                        'client_id'     => $host['client_id'],
                        'product_id'    => $host['product_id'],
                        'amount'        => $keepTimePriceDifference, 
                        'id'            => $order->id,
                        'scale'         => 4,
                    ]);
                    foreach ($hookDiscountResults as $hookDiscountResult){
                        if ($hookDiscountResult['status']==200){
                            $discountClient = $hookDiscountResult['data']['discount']??0;
                            $upgradeData['keep_time_price'] = bcsub($upgradeData['keep_time_price'], $discountClient, 4);
                        }
                    }
                    // $keepTimePriceDifference = $keepTimePriceDifference > 0 ? $keepTimePriceDifference : 0;
                    // $updateData['keep_time_price'] = bcadd($host['keep_time_price'], $keepTimePriceDifference, 4);
                    $updateData['keep_time_price'] = $upgradeData['keep_time_price']>0 ? $upgradeData['keep_time_price'] : 0;
                }
            }

            // 一次性时，实际首付金额
            if ($host['billing_cycle']=='onetime'){
                $updateData['price'] = $host['first_payment_amount']+$renewPrice;
            }

            $renewPrice = $renewPrice>0?$renewPrice:0;

            $updateData['renew_price'] = in_array($host['billing_cycle'], ['recurring_postpaid','recurring_prepayment','on_demand']) ? $renewPrice : 0;

            $upgrade->save($updateData);

            $product = (new ProductModel())->find($host['product_id']);
            if (in_array($host['billing_cycle'],['onetime','free','on_demand'])){
                $desDueTime = '∞';
            }else{
                $desDueTime = date('Y/m/d',$host['due_time']);
            }

            $billingCycleName = $host['billing_cycle_name'];
            $multiLanguage = hook_one('multi_language', [
                'replace' => [
                    'name' => $billingCycleName,
                ],
            ]);
            if(isset($multiLanguage['name'])){
                $billingCycleName = $multiLanguage['name'];
            }
            //$des = $product['name'] . '(' .$host['name']. '),'.lang('purchase_duration').':'.$host['billing_cycle_name'] .'(' . date('Y/m/d',$host['active_time']) . '-'. $desDueTime .')';
            //$des = lang('order_description_append',['{product_name}'=>$product['name'],'{name}'=>$host['name'],'{billing_cycle_name}'=>$host['billing_cycle_name'],'{time}'=>date('Y/m/d',$host['active_time']) . '-' . $desDueTime]);

            // 非按需才添加
            if($host['billing_cycle'] != 'on_demand'){
                $des = lang('order_description_append',['{product_name}'=>$product->name,'{name}'=>$host['name'],'{billing_cycle_name}'=>$billingCycleName,'{time}'=>date('Y/m/d',time()) . '-' . $desDueTime]);
                $newOrderItem = OrderItemModel::find($orderItem['id']);
                $newOrderItem->save([
                    'description' => $newOrderItem['description'] . "\n" . $des
                ]);
            }

            if($amount<=0){
                $this->processPaidOrder($order->id);
                
                /*HostModel::update([
                    'first_payment_amount' => $upgrade['price'],
                    'renew_amount' => ($host['billing_cycle']=='recurring_postpaid' || $host['billing_cycle']=='recurring_prepayment') ? (($host['renew_amount']+$param['renew_price_difference'])>0 ? ($host['renew_amount']+$param['renew_price_difference']) : 0) : 0,
                ],['id' => $host['id']]);

                $ModuleLogic = new ModuleLogic();
                $host = HostModel::find($host['id']);
                $ModuleLogic->changePackage($host, $param['config_options']);*/

                // 退款到余额
                if($amount<0 && $param['upgrade_refund']==1){
                    $result = update_credit([
                        'type' => 'Refund',
                        'amount' => -$amount,
                        'notes' => lang('upgrade_refund'),
                        'client_id' => $host['client_id'],
                        'order_id' => $order->id,
                        'host_id' => $host['id']
                    ]);
                    if(!$result){
                        throw new \Exception(lang('fail_message'));           
                    }
                }else if($amount<0 && $param['upgrade_refund']!=1){
                    OrderItemModel::create([
                        'type' => 'manual',
                        'order_id' => $order->id,
                        'client_id' => $host['client_id'],
                        'description' => lang('update_amount'),
                        'amount' => -$amount,
                        'create_time' => $time
                    ]);
                    $this->update([
                        'amount' => 0,
                    ], ['id' => $order->id]);
                }
            }

            $client = ClientModel::find($host['client_id']);
            # 记录日志
            active_log(lang('admin_create_upgrade_order', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$client->username.'#', '{order}'=>'#'.$order->id]), 'order', $order->id);

            // wyh 20240402 新增 支付后跳转地址
            $domain = configuration('website_url');
            $returnUrl = "{$domain}/productdetail.htm?id=".$hostId;
            $order->save([
                'return_url' => $returnUrl,
            ]);

            $this->commit();
        //}
//        catch (\Exception $e) {
//            // 回滚事务
//            $this->rollback();
//            return ['status' => 400, 'msg' => $e->getMessage()];
//        }

        return ['status' => 200, 'msg' => lang('success_message'), 'data' => ['id' => $order->id,'amount'=>$amount??0]];


    }

    /**
     * 时间 2022-05-24
     * @title 新建订单基础方法
     * @desc 新建订单基础方法,供系统内所有订单创建使用,未使用事务,只有基础的创建
     * @author theworld
     * @version v1
     * @param string param.type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单recharge充值 required
     * @param float param.amount - 金额 required
     * @param float param.credit - 余额 required
     * @param int param.upgrade_refund - 升降级是否退款0否1是
     * @param string param.status - 状态Unpaid未付款Paid已付款 required
     * @param string param.gateway - 支付方式 required
     * @param int param.client_id - 用户ID required
     * @param array param.items - 订单子项 required
     * @param int param.items[].host_id - 关联产品ID
     * @param string param.items[].type - 关联类型
     * @param int param.items[].rel_id - 关联ID
     * @param string param.items[].description - 描述 有关联的子项不需要描述
     * @param float param.items[].amount - 金额 required
     * @return int
     */
    public function createOrderBase($param)
    {
        // 处理传入数据
        $param['type'] = $param['type'] ?? 'new';
        $param['amount'] = $param['amount'] ?? 0;
        $param['credit'] = $param['credit'] ?? 0;
        $param['status'] = $param['amount']>0 ? ($param['status'] ?? 'Unpaid') : 'Paid';
        $param['gateway'] = $param['gateway'] ?? '';
        $param['client_id'] = $param['client_id'] ?? 0;
        $param['items'] = $param['items'] ?? [];
        if($param['status']=='Unpaid'){
            $param['amount_unpaid'] = $param['amount'] - $param['credit'];
        }else{
            $param['amount_unpaid'] = 0;
        }
        $time = time();

        // 获取支付接口名称
        $gateway = PluginModel::where('module', 'gateway')->where('name', $param['gateway'])->find();
        if(!empty($gateway)){
            $gateway['config'] = json_decode($gateway['config'],true);
            $gateway['title'] =  (isset($gateway['config']['module_name']) && !empty($gateway['config']['module_name']))?$gateway['config']['module_name']:$gateway['title'];
        }/*else{
            $gateway = gateway_list();
            $gateway = $gateway['list'][0]??[];
        }*/


        // 新建订单
        $order = $this->create([
            'type' => $param['type'],
            'amount' => $param['amount'],
            'credit' => $param['credit'],
            'amount_unpaid' => $param['amount_unpaid'],
            'upgrade_refund' => $param['upgrade_refund'] ?? 1,
            'status' => $param['status'],
            'gateway' => $param['gateway'],
            'gateway_name' => $gateway['title'] ?? '',
            'client_id' => $param['client_id'],
            'pay_time' => $param['status']=='Paid' ? $time : 0,
            'create_time' => $time,
            'admin_id' => get_admin_id(),
        ]);

        // 新建订单子项
        $list = [];
        foreach ($param['items'] as $key => $value) {
            $list[] = [
                'order_id' => $order->id,
                'client_id' => $param['client_id'],
                'host_id' => $value['host_id'] ?? 0,
                'product_id' => $value['product_id'] ?? 0,
                'type' => $value['type'] ?? '',
                'rel_id' => $value['rel_id'] ?? 0,
                'description' => $value['description'] ?? '',
                'amount' => $value['amount'] ?? 0,
                'gateway' => $param['gateway'],
                'gateway_name' => $gateway['title'] ?? '',
                'create_time' => $time
            ];
        }
        // 创建订单子项
        $OrderItemModel = new OrderItemModel();
        $OrderItemModel->saveAll($list);

        // 返回订单ID
        return $order->id;
    }

    /**
     * 时间 2022-05-17
     * @title 调整订单金额
     * @desc 调整订单金额
     * @author theworld
     * @version v1
     * @param int param.id - 订单ID required
     * @param float param.amount - 金额 required
     * @param string param.description - 描述 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateAmount($param)
    {
        // 验证订单ID
        $order = $this->find($param['id']);
        if (empty($order) || $order['is_recycle'] == 1){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        //if(in_array($order['status'], ['Paid', 'Refunded']) && $order['type']!='artificial'){
        if(in_array($order['status'], ['Paid', 'Refunded'])){
            return ['status'=>400, 'msg'=>lang('order_already_paid_cannot_adjustment_amount')];
        }

        // 调整后的订单金额不能小于0
        $order['amount_unpaid'] +=  $param['amount'];
        if($order['amount_unpaid']<0){
            return ['status'=>400, 'msg'=>lang('order_amount_adjustment_failed')];
        }

        $hookRes = hook('before_update_amount',['id'=>$param['id']]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }

        $this->startTrans();
        try {
            OrderItemModel::create([
                'type' => 'manual',
                'order_id' => $param['id'],
                'client_id' => $order['client_id'],
                'description' => $param['description'],
                'amount' => $param['amount'],
                'create_time' => time()
            ]);
            // 修改订单金额
            $this->update(['amount' => $order['amount_unpaid'] + $order['credit'], 'amount_unpaid' => $order['amount_unpaid'], 'update_time' => time()], ['id' => $param['id']]);

            $client = ClientModel::find($order->client_id);
            if(empty($client)){
                $clientName = '#'.$order->client_id;
            }else{
                $clientName = 'client#'.$client->id.'#'.$client->username.'#';
            }
            # 记录日志
            active_log(lang('admin_adjust_user_order_price', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id, '{old}'=>$order->amount, '{new}'=>($order['amount_unpaid'] + $order['credit'])]), 'order', $order->id);
			
            update_upstream_order_profit($order->id);
			
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }

        system_notice([
            'name'                  => 'admin_order_amount',
            'email_description'     => lang('admin_order_amount_send_mail'),
            'sms_description'       => lang('admin_order_amount_send_sms'),
            'task_data' => [
                'client_id' => $order->client_id,
                'order_id'  => $order->id,
            ],
        ]);

        hook('after_update_order_amount',['id'=>$param['id'],'amount'=>$param['amount'],'description'=>$param['description']]);

        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-10-11
     * @title 编辑人工调整的订单子项
     * @desc 编辑人工调整的订单子项
     * @author theworld
     * @version v1
     * @param int param.id - 订单子项ID required
     * @param float param.amount - 金额 required
     * @param string param.description - 描述 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateOrderItem($param)
    {
        // 验证订单ID
        $orderItem = OrderItemModel::find($param['id']);
        if (empty($orderItem)){
            return ['status'=>400, 'msg'=>lang('order_item_is_not_exist')];
        }

        // wyh 20230412 注释 所有类型都可以修改
        /*if ($orderItem['type']!='manual'){
            return ['status'=>400, 'msg'=>lang('order_item_cannot_update')];
        }*/

        // 验证订单ID
        $order = $this->find($orderItem['order_id']);
        if (empty($order) || $order['is_recycle']){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        if (in_array($order['status'], ['Paid', 'Refunded'])){
            return ['status'=>400, 'msg'=>lang('order_already_paid_cannot_adjustment_amount')];
        }

        // 调整后的订单金额不能小于0
        $order['amount_unpaid'] = $order['amount_unpaid'] - $orderItem['amount'] + $param['amount'];
        if($order['amount_unpaid']<0){
            return ['status'=>400, 'msg'=>lang('order_amount_adjustment_failed')];
        }

        $this->startTrans();
        try {
            OrderItemModel::update([
                //'type' => 'manual',
                'description' => $param['description'],
                'amount' => $param['amount'],
                'create_time' => time()
            ], ['id' => $param['id']]);
            // 修改订单金额
            $this->update(['amount' => $order['amount_unpaid'] + $order['credit'], 'amount_unpaid' => $order['amount_unpaid'], 'update_time' => time()], ['id' => $order['id']]);

            $client = ClientModel::find($order->client_id);
            if(empty($client)){
                $clientName = '#'.$order->client_id;
            }else{
                $clientName = 'client#'.$client->id.'#'.$client->username.'#';
            }
            # 记录日志
            active_log(lang('admin_adjust_user_order_price', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id, '{old}'=>$order->amount, '{new}'=>($order['amount_unpaid'] + $order['credit'])]), 'order', $order->id);
            
            update_upstream_order_profit($order->id);
            
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }

        system_notice([
            'name'                  => 'admin_order_amount',
            'email_description'     => lang('admin_order_amount_send_mail'),
            'sms_description'       => lang('admin_order_amount_send_sms'),
            'task_data' => [
                'client_id' => $order->client_id,
                'order_id'  => $order->id,
            ],
        ]);

        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2023-01-30
     * @title 删除人工调整的订单子项
     * @desc 删除人工调整的订单子项
     * @author theworld
     * @version v1
     * @param int id - 订单子项ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteOrderItem($id)
    {
        // 验证订单ID
        $orderItem = OrderItemModel::find($id);
        if (empty($orderItem)){
            return ['status'=>400, 'msg'=>lang('order_item_is_not_exist')];
        }

        if ($orderItem['type']!='manual'){
            return ['status'=>400, 'msg'=>lang('order_item_cannot_delete')];
        }

        // 验证订单ID
        $order = $this->find($orderItem['order_id']);
        if (empty($order) || $order['is_recycle']){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        if (in_array($order['status'], ['Paid', 'Refunded'])){
            return ['status'=>400, 'msg'=>lang('order_already_paid_cannot_adjustment_amount')];
        }

        // 调整后的订单金额不能小于0
        $order['amount_unpaid'] = $order['amount_unpaid'] - $orderItem['amount'];
        if($order['amount_unpaid']<0){
            return ['status'=>400, 'msg'=>lang('order_amount_adjustment_failed')];
        }

        $this->startTrans();
        try {
            OrderItemModel::destroy($id);
            // 修改订单金额
            $this->update(['amount' => $order['amount_unpaid'] + $order['credit'], 'amount_unpaid' => $order['amount_unpaid'], 'update_time' => time()], ['id' => $order['id']]);

            $client = ClientModel::find($order->client_id);
            if(empty($client)){
                $clientName = '#'.$order->client_id;
            }else{
                $clientName = 'client#'.$client->id.'#'.$client->username.'#';
            }
            # 记录日志
            active_log(lang('admin_adjust_user_order_price', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id, '{old}'=>$order->amount, '{new}'=>($order['amount_unpaid'] + $order['credit'])]), 'order', $order->id);
            
            update_upstream_order_profit($order->id);
            
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }

        system_notice([
            'name'                  => 'admin_order_amount',
            'email_description'     => lang('admin_order_amount_send_mail'),
            'sms_description'       => lang('admin_order_amount_send_sms'),
            'task_data' => [
                'client_id' => $order->client_id,
                'order_id'  => $order->id,
            ],
        ]);

        return ['status' => 200, 'msg' => lang('delete_success')];
    }

    /**
     * 时间 2022-05-17
     * @title 标记支付
     * @desc 标记支付
     * @author theworld
     * @version v1
     * @param int id - 订单ID required
     * @param string transaction_number - 交易流水号
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function orderPaid($param)
    {
        $this->startTrans();
        try {
            // 验证订单ID
            $order = $this->lock(true)->find($param['id']);
            if (empty($order) || $order['is_recycle']){
                throw new \Exception(lang('order_is_not_exist'));
            }

            // 已付款的订单不能标记支付
            if(in_array($order['status'], ['Paid', 'Refunded'])){
                throw new \Exception(lang('order_already_paid'));
            }

            /*if(isset($param['use_credit']) && $param['use_credit']==1){
                $client = ClientModel::find($order['client_id']);

                if($client['credit']>0){
                    if($client['credit']>$order['amount_unpaid']){
                        update_credit([
                            'type' => 'Applied',
                            'amount' => -$order['amount_unpaid'],
                            'notes' => lang('order_apply_credit')."#{$param['id']}",
                            'client_id' => $order->client_id,
                            'order_id' => $param['id'],
                            'host_id' => 0,
                        ]);
                        $order['amount_unpaid'] = 0;
                        $order['credit'] = $order['credit']+$order['amount_unpaid'];
                    }else{
                        $order['amount_unpaid'] = $order['amount_unpaid']-$client['credit'];
                        update_credit([
                            'type' => 'Applied',
                            'amount' => -$client['credit'],
                            'notes' => lang('order_apply_credit')."#{$param['id']}",
                            'client_id' => $order->client_id,
                            'order_id' => $param['id'],
                            'host_id' => 0,
                        ]);
                        $order['credit'] = $order['credit']+$client['credit'];
                    }
                }
            }*/
            if($order['amount_unpaid']>0){
                // 创建交易流水
                TransactionModel::create([
                    'order_id' => $order['id'],
                    'amount' => $order['amount_unpaid'],
                    'gateway' => $order['gateway'],
                    'gateway_name' => $order['gateway_name'],
                    'transaction_number' => $param['transaction_number'] ?? '',
                    'client_id' => $order['client_id'],
                    'create_time' => time(),
                    'notes' => $param['notes']??'',
                ]);
            }
            if($order['credit']>0){
                $res = update_credit([
                    'type' => 'Applied',
                    'amount' => -$order['credit'],
                    'notes' => lang('order_apply_credit')."#{$param['id']}",
                    'client_id' => $order->client_id,
                    'order_id' => $param['id'],
                    'host_id' => 0,
                ]);
                if(!$res){
                    throw new \Exception(lang('insufficient_credit_deduction_failed'));
                }
            }

            $this->update(['status' => 'Paid', 'credit' => $order['credit'], 'amount_unpaid'=>0, 'pay_time' => time(), 'update_time' => time()], ['id' => $param['id']]);

            // 处理已支付订单

            $this->processPaidOrder($param['id']);

            $client = ClientModel::find($order->client_id);
            if(empty($client)){
                $clientName = '#'.$order->client_id;
            }else{
                $clientName = 'client#'.$client->id.'#'.$client->username.'#';
            }
            # 记录日志
            active_log(lang('admin_mark_user_order_payment_status', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id]), 'order', $order->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail') . ':' . $e->getMessage()];
        }
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-05-17
     * @title 删除订单
     * @desc 删除订单
     * @author theworld
     * @version v1
     * @param int id - 订单ID required
     * @param int delete_host 1 是否删除产品:0否1是 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteOrder($param)
    {
        $id = $param['id']??0;

        $delete_host = $param['delete_host']??1;

        // 验证订单ID
        $order = $this->find($id);
        if (empty($order) || $order['is_recycle']){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }
        // 未支付的按需订单不能删除
        if ($order['status'] == 'Unpaid' && $order['type'] == 'on_demand'){
            return ['status'=>400, 'msg'=>lang('unpaid_on_demand_order_cannot_delete') ];
        }

        $hosts = HostModel::where('status', 'Pending')->where('order_id', $id)->where('is_delete', 0)->select()->toArray();
        if (!empty($hosts)){
            return ['status'=>400, 'msg'=>lang('hosts_under_activation_in_the_order')];
        }

        // 订单回收站
        $recycleBin = configuration('order_recycle_bin');
        if(!empty($recycleBin)){
            return $this->recycleOrder([
                'id'            => [$id],
                'delete_host'   => $delete_host,
            ]);
        }

        $hookRes = hook('before_order_delete',['id'=>$id]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }

        $this->startTrans();
        try {
            $client = ClientModel::find($order->client_id);
            if(empty($client)){
                $clientName = '#'.$order->client_id;
            }else{
                $clientName = 'client#'.$client->id.'#'.$client->username.'#';
            }
            # 记录日志
            active_log(lang('admin_delete_user_order', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id]), 'order', $order->id);

            $this->cancelUserCustomOrder($id);

            $this->destroy($id);
            // 删除订单子项
            OrderItemModel::destroy(function($query) use($id){
                $query->where('order_id', $id);
            });
            // 删除上游订单
            UpstreamOrderModel::destroy(function($query) use($id){
                $query->where('order_id', $id);
            });
            if($delete_host==1){
                // 删除订单产品
                HostModel::destroy(function($query) use($id){
                    $query->where('status', '<>', 'Active')->where('order_id', $id);
                });
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }

        hook('after_order_delete',['id'=>$id, 'order'=>$order]);

        return ['status' => 200, 'msg' => lang('delete_success')];
    }

    /**
     * 时间 2022-05-17
     * @title 批量删除订单
     * @desc 批量删除订单
     * @author theworld
     * @version v1
     * @param array id - 订单ID required
     * @param int delete_host 1 是否删除产品:0否1是 required
     * @param  string type - 类型(recycle_bin=从回收站删除,clear_recycle_bin=清空回收站)
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function batchDeleteOrder($param, $type = '')
    {
        $id = $param['id']??[];

        $delete_host = $param['delete_host']??1;

        // 验证订单ID
        if($type == 'clear_recycle_bin'){
            $order = $this->where('is_recycle', 1)->where('is_lock', 0)->select()->toArray();
            $id = array_column($order, 'id');
            if(empty($id)){
                return ['status'=>200, 'msg'=>lang('delete_success') ];
            }
        }else if($type == 'recycle_bin'){
            $order = $this->whereIn('id', $id)->where('is_recycle', 1)->where('is_lock', 0)->select()->toArray();
            $id = array_column($order, 'id');
            if(empty($id)){
                return ['status'=>400, 'msg'=>lang('order_locked_or_not_found')];
            }
        }else{
            $order = $this->whereIn('id', $id)->select()->toArray();
        }
        if (empty($order)){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }
        if(count($order)!=count($id)){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        $hosts = HostModel::where('status', 'Pending')->whereIn('order_id', $id)->where('is_delete', 0)->select()->toArray();
        if (!empty($hosts)){
            return ['status'=>400, 'msg'=>lang('hosts_under_activation_in_the_order')];
        }

        if($type != 'clear_recycle_bin' && $type != 'recycle_bin'){
            // 订单回收站
            $recycleBin = configuration('order_recycle_bin');
            if(!empty($recycleBin)){
                return $this->recycleOrder($param);
            }
        }

        // hh 20240409 改为删除能删除的
        $deleteFailMsg = []; // 删除失败信息
        foreach ($id as $value) {
            $hookRes = hook('before_order_delete',['id'=>$value]);
            foreach($hookRes as $v){
                if(isset($v['status']) && $v['status'] == 400){
                    $deleteFailMsg[ $value ] = $v['msg'] ?? '';
                    // return $v;
                }
            }
        }
        // 未支付的按需订单不能删除
        foreach($order as $v){
            if ($v['status'] == 'Unpaid' && $v['type'] == 'on_demand'){
                $deleteFailMsg[ $v['id'] ] = lang('unpaid_on_demand_order_cannot_delete');
            }
        }
        if(count($id) == count($deleteFailMsg)){
            return ['status'=>400, 'msg'=>implode(';', array_filter(array_unique(array_values($deleteFailMsg)))) ?: lang('delete_fail')];
        }

        $this->startTrans();
        try {
            foreach ($order as $key => $value) {
                $orderId = $value['id'];
                if(isset($deleteFailMsg[$orderId])){
                    continue;
                }
                $client = ClientModel::find($value['client_id']);
                if(empty($client)){
                    $clientName = '#'.$value['client_id'];
                }else{
                    $clientName = 'client#'.$client['id'].'#'.$client['username'].'#';
                }
                # 记录日志
                if($type == 'clear_recycle_bin' || $type == 'recycle_bin'){
                    active_log(lang('log_order_delete_from_recycle_bin_success', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$orderId]), 'order', $orderId);
                }else{
                    active_log(lang('admin_delete_user_order', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$orderId]), 'order', $orderId);
                }

                $this->cancelUserCustomOrder($orderId);

                $this->destroy($orderId);
                // 删除订单子项
                OrderItemModel::destroy(function($query) use($orderId){
                    $query->where('order_id', $orderId);
                });
                // 删除上游订单
                UpstreamOrderModel::destroy(function($query) use($orderId){
                    $query->where('order_id', $orderId);
                });
                if($delete_host==1){
                    // 从回收站删除
                    if($type == 'clear_recycle_bin' || $type == 'recycle_bin'){
                        // 删除已经逻辑删除的产品
                        HostModel::where('order_id', $orderId)->where('is_delete', 1)->delete();
                    }else{
                        // 删除订单产品
                        HostModel::destroy(function($query) use($orderId){
                            $query->where('status', '<>', 'Active')->where('order_id', $orderId);
                        });
                    }
                }
            }
            

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }
        foreach ($id as $value) {
            hook('after_order_delete', ['id'=>$value]);
        }

        return ['status' => 200, 'msg' => lang('delete_success')];
    }

    /**
     * 时间 2022-10-18
     * @title 取消订单
     * @desc 取消订单
     * @author theworld
     * @version v1
     * @param int id - 订单ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function cancelOrder($id)
    {
        // 验证订单ID
        $order = $this->find($id);
        if (empty($order) || $order['is_recycle']){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }
        $clientId = get_client_id();
        if($clientId!=$order['client_id']){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        if(!in_array($order['status'], ['Unpaid','WaitUpload','WaitReview','ReviewFail'])){
            return ['status'=>400, 'msg'=>lang('order_cannot_cancel')];
        }
        // 按需订单不能取消
        if(in_array($order['type'], ['on_demand'])){
            return ['status'=>400, 'msg'=>lang('order_cannot_cancel')];
        }

        $hosts = HostModel::where('status', '<>', 'Unpaid')->where('order_id', $id)->where('is_delete', 0)->select()->toArray();
        if (!empty($hosts)){
            return ['status'=>400, 'msg'=>lang('order_host_not_unpaid')];
        }

        $hookRes = hook('before_order_cancel',['id'=>$id]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }

        $this->startTrans();
        try {
            $client = ClientModel::find($clientId);
            if(empty($client)){
                $clientName = '#'.$clientId;
            }else{
                $clientName = 'client#'.$clientId.'#'.$client->username.'#';
            }
            # 记录日志
            active_log(lang('log_client_cancel_order', ['{client}'=>$clientName, '{order}'=>'#'.$order->id]), 'order', $order->id);

            // 退钱,放在状态修改前
            $this->cancelUserCustomOrder($id);

            $this->update([
                'status' => 'Cancelled',
                'update_time' => time()
            ], ['id' => $id]);
            
            HostModel::update([
                'status' => 'Cancelled',
                'update_time' => time()
            ], ['order_id' => $id]);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }

        hook('after_order_cancel',['id'=>$id]);

        return ['status' => 200, 'msg' => lang('delete_success')];
    }

    /**
     * 时间 2022-10-18
     * @title 批量取消订单
     * @desc 批量取消订单
     * @author theworld
     * @version v1
     * @param int param.id - 订单ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function batchCancelOrder($param)
    {
        $id = $param['id']??[];
        $order = $this->whereIn('id', $id)->select()->toArray();
        if (empty($order)){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }
        $clientId = get_client_id();
        foreach ($order as $value){
            if($clientId!=$value['client_id']){
                return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
            }
            if(!in_array($value['status'], ['Unpaid','WaitUpload','WaitReview','ReviewFail'])){
                return ['status'=>400, 'msg'=>lang('order_cannot_cancel')];
            }
        }
        $hosts = HostModel::where('status', '<>', 'Unpaid')->whereIn('order_id', $id)->where('is_delete', 0)->select()->toArray();
        if (!empty($hosts)){
            return ['status'=>400, 'msg'=>lang('order_host_not_unpaid')];
        }
        $deleteFailMsg = [];
        foreach ($id as $value){
            $hookRes = hook('before_order_cancel',['id'=>$value]);
            foreach($hookRes as $v){
                if(isset($v['status']) && $v['status'] == 400){
                    $deleteFailMsg[ $value ] = $v['msg'] ?? '';
                }
            }
        }
        // 未支付的按需订单不能取消
        foreach($order as $v){
            if ($v['status'] == 'Unpaid' && $v['type'] == 'on_demand'){
                $deleteFailMsg[ $v['id'] ] = lang('unpaid_on_demand_order_cannot_delete');
            }
        }
        if(count($id) == count($deleteFailMsg)){
            return ['status'=>400, 'msg'=>implode(';', array_filter(array_unique(array_values($deleteFailMsg)))) ?: lang('delete_fail')];
        }


        $this->startTrans();
        try {
            $client = ClientModel::find($clientId);
            if(empty($client)){
                $clientName = '#'.$clientId;
            }else{
                $clientName = 'client#'.$clientId.'#'.$client->username.'#';
            }

            foreach ($order as $key => $value) {
                if(isset($deleteFailMsg[$value['id']])){
                    continue;
                }
                # 记录日志
                active_log(lang('log_client_cancel_order', ['{client}'=>$clientName, '{order}'=>'#'.$value['id']]), 'order', $value['id']);

                // 退钱,放在状态修改前
                $this->cancelUserCustomOrder($value['id']);

                $this->update([
                    'status' => 'Cancelled',
                    'update_time' => time()
                ], ['id' => $value['id']]);

                HostModel::update([
                    'status' => 'Cancelled',
                    'update_time' => time()
                ], ['order_id' => $value['id']]);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }

        foreach ($id as $value) {
            hook('after_order_cancel', ['id'=>$value]);
        }

        return ['status' => 200, 'msg' => lang('delete_success')];
    }

    /**
     * 时间 2023-01-29
     * @title 订单退款
     * @desc  订单退款
     * @author hh
     * @version v1
     * @param   int param.id - 订单ID require
     * @param   int param.host_id - 产品ID
     * @param   float param.amount - 退款金额 require
     * @param   string param.type - 退款类型(credit_first=余额优先,gateway_first=渠道优先,credit=余额,transaction=支付接口) require
     * @param   string param.notes - 备注
     * @param   string param.gateway - 支付接口 requireIf:type=transaction
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.id - 退款记录ID
     */
    public function orderRefund($param)
    {
        $param['host_id'] = $param['host_id'] ?? 0;

        $order = $this->find($param['id']);
        if(empty($order) || $order['is_recycle']){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist') ];
        }
        if(!in_array($order['status'], ['Paid','Refunded'])){
            return ['status'=>400, 'msg'=>lang('recharge_order_cannot_refund') ];
        }
        // 信用额不能订单退款
        if(in_array($order['type'], ['credit_limit','combine'])){
            return ['status'=>400, 'msg'=>lang('order_not_support_refund') ];
        }
        // 充值订单是否可以退款
        if($order['type'] == 'recharge'){
            if(configuration('recharge_order_support_refund') == 0){
                return ['status'=>400, 'msg'=>lang('order_not_support_refund') ];
            }
            if(!in_array($param['type'], ['gateway_first','transaction'])){
                return ['status'=>400, 'msg'=>lang('recharge_order_refund_only_support_transaction') ];
            }
        }
        if($order['is_refund'] == 1){
            return ['status'=>400, 'msg'=>lang('order_have_refund_record_untreated') ];
        }

        $param['order'] = $order;

        $data = $this->orderRefundIndex($param);
        if(empty($data)){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist') ];
        }
        // 信用额订单,永远渠道优先
        if($order['gateway'] == 'credit_limit'){
            $param['type'] = 'gateway_first';
        }

        if(($param['amount'] > $data['leave_total']) || (!empty($param['host_id']) && $param['amount'] > $data['leave_host_amount']) || ($param['type'] == 'transaction' && $param['amount'] > $data['leave_gateway']) ){
            return ['status'=>400, 'msg'=>lang('order_refund_amount_not_enough') ];
        }

        // 没有剩余渠道了
        if(empty($data['leave_gateway']) && $param['type'] != 'credit'){
            // $param['type'] = 'credit';
            return ['status'=>400, 'msg'=>lang('order_refund_amount_only_refund_to_credit') ];
        }

        // 计算退款金额
        $refundCredit = 0;
        $refundGateway = 0;

        if($param['type'] == 'credit_first'){
            // 全退余额
            if($param['amount'] <= $data['leave_credit']){
                $refundCredit = $param['amount'];
                $refundGateway = 0;
            }else{
                $refundCredit = $data['leave_credit'];
                $refundGateway = bcsub($param['amount'], $data['leave_credit'], 2);
            }
        }else if($param['type'] == 'gateway_first'){
            // 全退渠道
            if($param['amount'] <= $data['leave_gateway']){
                $refundCredit = 0;
                $refundGateway = $param['amount'];
            }else{
                $refundCredit = bcsub($param['amount'], $data['leave_gateway'], 2);
                $refundGateway = $data['leave_gateway'];
            }
        }else if($param['type'] == 'transaction'){
            $refundCredit = 0;
            $refundGateway = $param['amount'];
        }else{
            // 余额退款
            $refundCredit = $param['amount'];
            $refundGateway = 0;
        }

        if($param['type'] == 'credit_first' || $param['type'] == 'gateway_first'){
            // 验证回退支付接口
            if($refundGateway > 0){
                // 信用额默认支持
                if($order['gateway'] != 'credit_limit' && !plugin_method_exist($order['gateway'],'gateway','handle_refund')){
                    return ['status'=>400, 'msg'=>lang('order_cannot_refund_to_gateway') ];
                }
            }
        }else if($param['type'] == 'transaction'){
            // 获取支付接口名称
            $gateway = PluginModel::where('module', 'gateway')->where('name', $param['gateway'])->find();
            if(empty($gateway)){
                return ['status' => 400, 'msg' => lang('gateway_is_not_exist')];
            }
            $gateway['config'] = json_decode($gateway['config'],true);
            $gateway['title'] =  (isset($gateway['config']['module_name']) && !empty($gateway['config']['module_name']))?$gateway['config']['module_name']:$gateway['title'];
        }

        // 是否有插件退款
        if(!empty($data['host_id'])){
            $addonRefund = RefundRecordModel::where('refund_type', 'addon')
                        ->whereIn('host_id', $data['host_id'])
                        ->whereNotIn('status', ['Reject','Cancelled','Refunded'])
                        ->find();
            if(!empty($addonRefund)){
                return ['status'=>400, 'msg'=>lang('order_have_refund_record_untreated') ];
            }
        }

        $hookRes = hook('before_order_refund',['id'=>$order->id ]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }

        $adminId = get_admin_id();

        $this->startTrans();
        try{
            $update = $this
                    ->where('id', $order->id)
                    ->where('is_refund', 0)
                    ->whereIn('status', ['Paid','Refunded'])
                    ->update([
                        'is_refund'     => 1,
                        'update_time'   => time(),
                    ]);
            if(empty($update)){
                throw new \Exception( lang('order_status_changed_please_refresh_and_retry') );
            }

            // 添加退款记录
            $refundRecord = RefundRecordModel::create([
                'order_id'              => $order->id,
                'client_id'             => $order['client_id'],
                'admin_id'              => $adminId,
                'type'                  => $param['type'],
                'transaction_id'        => 0,
                'amount'                => $param['amount'],
                'create_time'           => time(),
                'status'                => 'Pending',
                'gateway'               => $param['type'] == 'transaction' ? $param['gateway'] : ($refundGateway > 0 ? $order['gateway'] : ''),
                'host_id'               => $param['host_id'],
                'refund_type'           => 'order',
                'notes'                 => $param['notes'] ?? '',
                'refund_credit'         => $refundCredit,
                'refund_gateway'        => $refundGateway,
            ]);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();

            return ['status'=>400, 'msg'=>$e->getMessage() ];
        }

        $type = [
            'credit_first'  => lang('order_refund_type_credit_first'),
            'gateway_first' => lang('order_refund_type_gateway_first'),
            'credit'        => lang('order_refund_type_credit'),
            'transaction'   => lang('order_refund_type_transaction'),
        ];

        $description = lang('log_start_order_refund_success', [
            '{id}'      => 'order#' . $order->id . '#',
            '{amount}'  => $param['amount'],
            '{type}'    => $type[ $param['type'] ],
        ]);

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => [
                'id' => (int)$refundRecord->id,
            ],
        ];

        return $result;
    }

    /**
     * 时间 2023-01-29
     * @title 订单应用余额
     * @desc 订单应用余额
     * @author theworld
     * @version v1
     * @param int id - 订单ID required
     * @param float amount - 金额 required
     * @param string status - 状态Refunded已退款Paid已付款,订单状态为已退款时需传
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function orderApplyCredit($param)
    {
        $id = $param['id']??0;
        $adminId = get_admin_id();

        $hookRes = hook('before_order_apply_credit',['id'=>$id]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }

        $RefundRecordModel = new RefundRecordModel();

        $this->startTrans();
        try {
            // 验证订单ID
            $order = $this->lock(true)->find($id);
            if (empty($order) || $order['is_recycle']){
                throw new \Exception(lang('order_is_not_exist'));
            }
            if(in_array($order['status'], ['WaitUpload','WaitReview','ReviewFail'])){
                throw new \Exception(lang('order_not_support_apply_credit'));
            }
            if($order['is_refund'] == 1){
                throw new \Exception( lang('order_have_refund_record_untreated') );
            }
            // 是否有插件退款记录
            $refund = $RefundRecordModel->isOrderAddonRefund($id);
            if($refund){
                throw new \Exception( lang('order_have_refund_record_untreated') );
            }

            $amount = TransactionModel::where('order_id', $id)->sum('amount');
            //$refundAmount = RefundRecordModel::where('order_id', $id)->where('type', 'credit')->sum('amount');

            // $refundAmount = RefundRecordModel::where('order_id', $id)
            //     ->whereIn('status',['Refunded','Refunding','Pending'])
            //     ->sum('amount');
            // $amount = $amount-$refundAmount;
            // if($amount<0){
            //     throw new \Exception(lang('order_not_support_apply_credit'));
            // }

            $amount = $order['amount']-$order['credit']-$amount;

            $amount = amount_format($amount);
            if($param['amount']>$amount){
                throw new \Exception(lang('apply_credit_not_enough'));
            }


            $apply = false; // 应用余额

            if(in_array($order['status'], ['Paid'])){
                $this->update([
                    'credit'        => bcadd($order['credit'], $param['amount'], 2),
                    // 'refund_amount' => max(bcsub($order['refund_amount'], $param['amount'], 2), 0),
                ], ['id' => $id]);
                $apply = true;
            }else if(in_array($order['status'], ['Refunded'])){
                if(!isset($param['status']) || !in_array($param['status'], ['Refunded', 'Paid'])){
                    throw new \Exception(lang('param_error'));
                }

                $this->update([
                    'status'        => $param['status'] ?? $order['status'],
                    'credit'        => bcadd($order['credit'], $param['amount'], 2),
                    // 'refund_amount' => max(bcsub($order['refund_amount'], $param['amount'], 2), 0),
                ], ['id' => $id]);
                $apply = true;
            }else if($order['status']=='Unpaid'){
                $this->update([
                    'credit' => $order['credit']+$param['amount'],
                    'status' => $param['amount']==$amount ? 'Paid' : $order['status'],
                    'amount_unpaid' => $order['amount']-$order['credit']-$param['amount'],
                    'gateway' => ($order['credit']+$param['amount'])==$order['amount'] ? 'credit' : $order['gateway'],
                    'gateway_name' => ($order['credit']+$param['amount'])==$order['amount'] ? lang('credit_payment') : $order['gateway_name'],
                    'pay_time' => $param['amount']==$amount ? time() :  $order['pay_time'],
                ], ['id' => $id]);
                if($param['amount']==$amount){
                    $res = update_credit([
                        'type' => 'Applied',
                        'amount' => -($order['credit']+$param['amount']),
                        'notes' => lang('order_apply_credit')."#{$id}",
                        'client_id' => $order['client_id'],
                        'order_id' => $id,
                        'host_id' => 0,
                    ]);

                    if(!$res){
                        throw new \Exception(lang('insufficient_credit_deduction_failed'));
                    }
                    $this->processPaidOrder($param['id']);
                }
            }else{
                $this->update([
                    'status' => $param['status'] ?? $order['status'],
                    'credit' => $order['credit']+$param['amount'],
                    'amount_unpaid' => $order['amount']-$order['credit']-$param['amount'],
                ], ['id' => $id]);
            }

            if($apply){
                $res = update_credit([
                    'type' => 'Applied',
                    'amount' => -$param['amount'],
                    'notes' => lang('order_apply_credit')."#{$id}",
                    'client_id' => $order['client_id'],
                    'order_id' => $id,
                    'host_id' => 0,
                ]);

                if(!$res){
                    throw new \Exception(lang('insufficient_credit_deduction_failed'));
                }
            }

            $client = ClientModel::find($order['client_id']);
            if(empty($client)){
                $clientName = '#'.$order['client_id'];
            }else{
                $clientName = 'client#'.$client->id.'#'.$client->username.'#';
            }
            # 记录日志
            active_log(lang('admin_apply_credit_to_user_order', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id, '{amount}'=>$param['amount']]), 'order', $order->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2023-01-29
     * @title 订单扣除余额
     * @desc 订单扣除余额
     * @author theworld
     * @version v1
     * @param int id - 订单ID required
     * @param float amount - 金额 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function orderRemoveCredit($param)
    {
        $id = $param['id']??0;
        $adminId = get_admin_id();

        $RefundRecordModel = new RefundRecordModel();

        $this->startTrans();
        try {
            // 验证订单ID
            $order = $this->lock(true)->find($id);
            if (empty($order) || $order['is_recycle']){
                throw new \Exception(lang('order_is_not_exist'));
            }
            if($order['is_refund'] == 1){
                throw new \Exception( lang('order_have_refund_record_untreated') );
            }
            if($order['status'] != 'Unpaid'){
                throw new \Exception( lang('order_status_changed_please_refresh_and_retry') );
            }

            // 是否有插件退款记录
            // $refund = $RefundRecordModel->isOrderAddonRefund($id);
            // if($refund){
            //     throw new \Exception( lang('order_have_refund_record_untreated') );
            // }

            if($param['amount']>$order['credit']){
                throw new \Exception(lang('remove_credit_not_enough'));
            }

            // if(in_array($order['status'], ['Paid', 'Refunded'])){
            //     $this->update([
            //         'credit'        => $order['credit']-$param['amount'],
            //         'status'        => 'Refunded',
            //         'refund_amount' => bcadd($order['refund_amount'], $param['amount'], 2),
            //     ], ['id' => $id]);

            //     update_credit([
            //         'type' => 'Refund',
            //         'amount' => $param['amount'],
            //         'notes' => lang('order_remove_credit', ['{id}' => $id]),
            //         'client_id' => $order['client_id'],
            //         'order_id' => $id,
            //         'host_id' => 0,
            //     ]);

            //     hook('after_order_refund',['id'=>$id]);
                
            // }else if(in_array($order['status'], ['WaitUpload','WaitReview','ReviewFail'])){
            //     // 不修改状态
            //     $this->update([
            //         'credit'        => $order['credit']-$param['amount'],
            //     ], ['id' => $id]);

            //     update_credit([
            //         'type' => 'Refund',
            //         'amount' => $param['amount'],
            //         'notes' => lang('order_remove_credit', ['{id}' => $id]),
            //         'client_id' => $order['client_id'],
            //         'order_id' => $id,
            //         'host_id' => 0,
            //     ]);
            // }else{
            $update = $this
                    ->where('id', $id)
                    ->where('status', 'Unpaid')
                    ->update([
                        'credit' => $order['credit']-$param['amount'],
                        'amount_unpaid' => $order['amount']-$order['credit']+$param['amount'],
                    ]);
            // if(!$update){
            //     throw new \Exception( lang('order_status_changed_please_refresh_and_retry') );
            // }
            // }

            $client = ClientModel::find($order['client_id']);
            if(empty($client)){
                $clientName = '#'.$order['client_id'];
            }else{
                $clientName = 'client#'.$client->id.'#'.$client->username.'#';
            }
            # 记录日志
            active_log(lang('admin_remove_credit_from_user_order', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id, '{amount}'=>$param['amount']]), 'order', $order->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2023-01-29
     * @title 修改订单支付方式
     * @desc 修改订单支付方式
     * @author theworld
     * @version v1
     * @param int param.id - 订单ID required
     * @param string param.gateway - 支付方式 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateGateway($param)
    {
        // 验证订单ID
        $order = $this->find($param['id']);
        if (empty($order) || $order['is_recycle']){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        $param['gateway'] = $param['gateway'] ?? '';

        // 获取支付接口名称
        $gateway = PluginModel::where('module', 'gateway')->where('name', $param['gateway'])->find();
        if(empty($gateway)){
            return ['status' => 400, 'msg' => lang('gateway_is_not_exist')];
        }
        $gateway['config'] = json_decode($gateway['config'],true);
        $gateway['title'] =  (isset($gateway['config']['module_name']) && !empty($gateway['config']['module_name']))?$gateway['config']['module_name']:$gateway['title'];

        $this->startTrans();
        try {
            // 修改订单支付方式
            $this->update([
                'gateway' => $param['gateway'], 
                'gateway_name' => $gateway['title'], 
                'update_time' => time()
            ], ['id' => $param['id']]);

            $client = ClientModel::find($order->client_id);
            if(empty($client)){
                $clientName = '#'.$order->client_id;
            }else{
                $clientName = 'client#'.$client->id.'#'.$client->username.'#';
            }
            # 记录日志
            active_log(lang('admin_adjust_user_order_gateway', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id, '{old}'=>$order['gateway_name'], '{new}'=>$gateway['title']]), 'order', $order->id);
            
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }

        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2023-01-29
     * @title 修改订单备注
     * @desc 修改订单备注
     * @author theworld
     * @version v1
     * @param int param.id - 订单ID required
     * @param string param.notes - 备注
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateNotes($param)
    {
        // 验证订单ID
        $order = $this->find($param['id']);
        if (empty($order)){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        $param['notes'] = $param['notes'] ?? '';

        $this->startTrans();
        try {
            // 修改订单备注
            $this->update([
                'notes' => $param['notes'], 
                'update_time' => time()
            ], ['id' => $param['id']]);

            $client = ClientModel::find($order->client_id);
            if(empty($client)){
                $clientName = '#'.$order->client_id;
            }else{
                $clientName = 'client#'.$client->id.'#'.$client->username.'#';
            }
            # 记录日志
            active_log(lang('admin_adjust_user_order_notes', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id, '{old}'=>$order['notes'], '{new}'=>$param['notes']]), 'order', $order->id);
            
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }

        return ['status' => 200, 'msg' => lang('update_success')];
    }

    # 更新上游订单利润
    public function updateUpstreamOrderProfit($id){
        $OrderItemModel = new OrderItemModel();
        $orderItems = $OrderItemModel->where('order_id', $id)->select()->toArray();
        $upstreamOrder = UpstreamOrderModel::where('order_id', $id)->select()->toArray();
        if(!empty($upstreamOrder)){
            $hostAmountArr = [];
            $hostReduceArr = [];
            $amount = 0;
            $reduce = 0;
            foreach ($orderItems as $key => $value) {
                if($value['amount']>=0){
                    $amount += $value['amount'];
                    if(!empty($value['host_id'])){
                        $hostAmountArr[$value['host_id']] = ($hostAmountArr[$value['host_id']] ?? 0) + $value['amount']; 
                    }
                }else{
                    if(!empty($value['host_id'])){
                        $hostReduceArr[$value['host_id']] = ($hostReduceArr[$value['host_id']] ?? 0) + $value['amount']; 
                    }else{
                        $reduce += $value['amount'];
                    }
                }
                
            }
            foreach ($hostAmountArr as $key => $value) {
                if($amount>0){
                    $value = bcadd($value + ($hostReduceArr[$key] ?? 0), ($value/$amount*$reduce), 2);
                }else{
                    $value = 0;
                }
                $hostAmountArr[$key] = $value;
            }
            foreach ($upstreamOrder as $key => $value) {
                $value['original_price'] = $value['amount'] - $value['profit'];
                $amount = $hostAmountArr[$value['host_id']] ?? 0;
                UpstreamOrderModel::update([
                    'amount' => $amount, 
                    'profit' => bcsub($amount, $value['original_price'], 2)
                ], ['id' => $value['id']]);
            }
        }
        return true;
    }

    # 处理已支付订单
    public function processPaidOrder($id)
    {
        $order = $this->find($id);

        if ($order->status != 'Paid'){
            return false;
        }

        $OrderItemModel = new OrderItemModel();
        $orderItems = $OrderItemModel->where('order_id',$id)->select();
		if(isset($orderItems[0]['type']) && $orderItems[0]['type'] == 'recharge'){
            system_notice([
                'name'                  => 'order_recharge',
                'email_description'     => lang('order_recharge_send_mail'),
                'sms_description'       => lang('order_recharge_send_sms'),
                'task_data' => [
                    'client_id' => $order->client_id,
                    'order_id'  => $id,
                ],
            ]);
		}else{
            if($order['type'] != 'on_demand'){
                system_notice([
                    'name'                  => 'order_pay',
                    'email_description'     => lang('order_pay_send_mail'),
                    'sms_description'       => lang('order_pay_send_sms'),
                    'task_data' => [
                        'client_id' => $order->client_id,
                        'order_id'  => $id,
                    ],
                ]);
            }
		}
        update_upstream_order_profit($id);
        foreach($orderItems as $orderItem){
            $type = $orderItem['type'];
			
            switch ($type){
                case 'host':
					
                    $this->hostOrderHandle($orderItem->rel_id);
                    break;
                case 'recharge':
                    $TransactionModel = new TransactionModel();
                    $transaction = $TransactionModel->where('order_id',$id)->find();
                    $transactionNumber = $transaction['transaction_number']??'';
                    update_credit([
                        'type' => 'Recharge',
                        'amount' => $orderItem->amount,
                        'notes' => lang('recharge')."#{$transactionNumber}",
                        'client_id' => $orderItem->client_id,
                        'order_id' => $id,
                        'host_id' => 0
                    ]);

                    $ClientModel = new ClientModel();
                    $client = $ClientModel->find($order['client_id']);
                    active_log(lang('log_client_recharge',['{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{transaction}'=>$transactionNumber,'{amount}'=>$orderItem->amount]),'client',$client['id']);
                    // 重置提醒
                    $client->save([
                        'credit_remind_send' => 0,
                        'update_time' => time(),
                    ]);
                    break;
                case 'upgrade':
                    $this->upgradeOrderHandle($orderItem->rel_id);
                    break;
                case 'on_demand':
                    $this->onDemandOrderHandle($orderItem->host_id);
                    break;
                case 'change_billing_cycle':
                    $this->changeBillingCycleOrderHandle($orderItem->rel_id);
                    break;
                case 'combine':
                    $this->combineOrderHandle($order, $orderItem);
                    break;
                default:
                    break;
            }
        }
        # 引入订单支付后钩子
        hook('order_paid',['id'=>$id]);

        return true;
    }

    # 产品订单处理
    public function hostOrderHandle($id)
    {
        $HostModel = new HostModel();
        $host = $HostModel->alias('h')
            ->field('h.id,h.order_id,h.client_id,h.status,h.billing_cycle,h.billing_cycle_name,h.name,h.billing_cycle_time,p.auto_setup,h.order_id,p.name as product_name,h.active_time,h.due_time,h.is_sub,h.product_id,p.natural_month_prepaid')
            ->leftjoin('product p','h.product_id=p.id')
            ->where('h.id',$id)
            ->where('h.is_delete', 0)
            ->find();
        if (empty($host)){
            return false;
        }
        
        // 判断是否为自然月预付费（通过商品的 natural_month_prepaid 字段判断）
        $isNaturalMonth = ($host['natural_month_prepaid'] == 1);
        
        if ($host['status']=='Unpaid'){
            // 自然月预付费不重新计算到期时间，使用创建时设置的到期时间
            if(!$isNaturalMonth){
                if(in_array($host->billing_cycle,['onetime'])){
                    $dueTime = 0;
                }else if(in_array($host->billing_cycle,['free']) && $host->billing_cycle_time==0){
                    $dueTime = 0;
                }else{
                    $dueTime = time() + intval($host->billing_cycle_time);
                }
            }else{
                $dueTime = $host['due_time'];
            }
        }else{
            $dueTime = $host['due_time'];
        }

        # 修改产品
        $HostModel->update([
            'status' => 'Pending',
            'due_time' => $dueTime,
        ],['id'=>$id]);

        # 更改新购订单子项描述
        $OrderItemModel = new OrderItemModel();
        $orderItem = $OrderItemModel->where('order_id',$host['order_id'])
            ->where('host_id',$id)
            ->where('type','host')
            ->find();
        if (!empty($orderItem)){
            if (in_array($host['billing_cycle'],['onetime','free'])){
                $desDueTime = '∞';
            }else{
                $desDueTime = date('Y/m/d',$dueTime);
            }
            $productName = $host['product_name'];
            $billingCycleName = $host['billing_cycle_name'];
            $multiLanguage = hook_one('multi_language', [
                'replace' => [
                    'product_name' => $productName,
                    'name'         => $billingCycleName,
                ],
            ]);
            if(isset($multiLanguage['product_name'])){
                $productName = $multiLanguage['product_name'];
            }
            if(isset($multiLanguage['name'])){
                $billingCycleName = $multiLanguage['name'];
            }

            $des = $productName . '(' .$host['name']. '),'.lang('purchase_duration').':'.$billingCycleName .'(' . date('Y/m/d',$host['active_time']) . '-'. $desDueTime .')';
            //$des = lang('order_description_append',['{product_name}'=>$host['product_name'],'{name}'=>$host['name'],'{billing_cycle_name}'=>$host['billing_cycle_name'] ,'{time}'=>date('Y/m/d H',$host['active_time']) . '-' . date('Y/m/d H',date('Y/m/d H',$dueTime))]);
            $orderItemDes = explode("\n",$orderItem['description'])??[];
            if (count($orderItemDes)>=2){
                array_pop($orderItemDes);
                array_push($orderItemDes,$des);
            }
            $orderItem->save([
                'description' => implode("\n",$orderItemDes)
            ]);
        }

        # 暂停时,付款后解除
        if ($host->status == 'Suspended'){
			add_task([
				'type' => 'host_unsuspend',
                'rel_id' => $id,
				'description' => '#'.$id.lang('host_unsuspend'),
				'task_data' => [
					'host_id'=>$id,//主机ID
				],		
			]);
            /*$unsuspend = $HostModel->unsuspendAccount($id);
            if ($unsuspend['status'] == 200){
                # 记录日志
				
                # 加任务队列

            }else{

            }*/
        }

        # 开通
        if($host->auto_setup==1 && $host['is_sub']==0){
            system_notice([
                'name'                  => 'host_pending',
                'email_description'     => lang('host_creating_send_mail'),
                'sms_description'       => lang('host_creating_send_sms'),
                'task_data' => [
                    'client_id' => $host['client_id'],
                    'host_id'   => $id,
                ],
            ]);
			
			add_task([
				'type' => 'host_create',
                'rel_id' => $host['id'],
				'description' => lang('client_host_create', ['{client_id}' => $host['client_id'], '{host_id}' => $host['id']]),
				'task_data' => [
					'host_id'=>$id,//主机ID
				],		
			]);
            /*$create = $HostModel->createAccount($id);
            if ($create['status'] == 200){
				
            }else{

            }*/
        }
        
        # 发送邮件短信

        return true;
    }

    # 升降级订单处理
    public function upgradeOrderHandle($id)
    {
		
        $upgrade = UpgradeModel::find($id);
        if (empty($upgrade)){
            return false;
        }
        # 修改状态
        UpgradeModel::update([
            'status' => 'Pending',
            'update_time' => time()
        ], ['id' => $id]);

        # 升降级
        if($upgrade['type']=='product'){
            // 获取接口
            $product = ProductModel::find($upgrade['rel_id']);
            if($product['type']=='server_group'){
                $server = ServerModel::where('server_group_id', $product['rel_id'])->where('status', 1)->find();
                $serverId = $server['id'] ?? 0;
            }else{
                $serverId = $product['rel_id'];
            }
            $HostModel = new HostModel();
            $host = $HostModel->find($upgrade['host_id']);
            // wyh 20210109 改 一次性/免费可升级后
            if($host['billing_cycle']=='onetime'){
                if ($product['pay_type']=='onetime'){
                    $hostDueTime = 0;
                }elseif ($product['pay_type']=='free' && $upgrade['billing_cycle_time']==0){
                    $hostDueTime = 0;
                }else{
                    $hostDueTime = time()+$upgrade['billing_cycle_time'];
                }
            }else if($host['billing_cycle']=='free' && $host['billing_cycle_time']==0){
                if ($product['pay_type']=='onetime'){
                    $hostDueTime = 0;
                }elseif ($product['pay_type']=='free' && $upgrade['billing_cycle_time']==0){
                    $hostDueTime = 0;
                }else{
                    $hostDueTime = time()+$upgrade['billing_cycle_time'];
                }
            }else{
                if ($product['pay_type']=='onetime'){
                    $hostDueTime = 0;
                }elseif ($product['pay_type']=='free' && $upgrade['billing_cycle_time']==0){
                    $hostDueTime = 0;
                }else{ # 周期变更
                    $hostDueTime = time()+$upgrade['billing_cycle_time'];//$host['due_time'];
                }
            }
            $upgrade['base_price'] = $upgrade['base_price']>0?$upgrade['base_price']:0;
            HostModel::update([
                'product_id' => $upgrade['rel_id'],
                'server_id' => $serverId,
                // TODO 更改为实际支付
                'first_payment_amount' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $upgrade['renew_price'] : ($product['pay_type']=='onetime'?$upgrade['price']:0),//$upgrade['price'],
                'renew_amount' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $upgrade['renew_price'] : 0,
                'billing_cycle' => $product['pay_type'],
                'billing_cycle_name' => $upgrade['billing_cycle_name'],
                'billing_cycle_time' => $upgrade['billing_cycle_time'],
                'due_time' => $hostDueTime,
                'base_price' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $upgrade['base_price'] : 0,
            ],['id' => $upgrade['host_id']]);
        }else if($upgrade['type']=='config_option'){
            $host = HostModel::find($upgrade['host_id']);
            $settleTime = time();
            // 按需处理
            if($host['billing_cycle'] == 'on_demand'){
                $update = [
                    'renew_amount'      => $upgrade['renew_price'],
                    'base_renew_amount' => $upgrade['base_renew_price'],
                ];
                if($upgrade['on_demand_flow_price'] >= 0){
                    $update['on_demand_flow_price'] = $upgrade['on_demand_flow_price'];
                }
                if($upgrade['keep_time_price'] >= 0){
                    $update['keep_time_price'] = $upgrade['keep_time_price'];
                }
                if($host['renew_use_current_client_level'] == 1){
                    $update['discount_renew_price'] = bcadd($host['discount_renew_price'], $upgrade['renew_price_difference_client_level_discount'], 4);
                    $update['discount_renew_price'] = $update['discount_renew_price'] > 0 ? $update['discount_renew_price'] : 0;
                }

                if($settleTime > $host['start_billing_time'] && $settleTime < $host['next_payment_time']){
                    $update['start_billing_time'] = $settleTime;

                    // 添加出账队列
                    $OnDemandPaymentQueueModel = new OnDemandPaymentQueueModel();
                    $OnDemandPaymentQueueModel->createOnDemandPaymentQueue([
                        'host'      => $host,
                        'type'      => 'upgrade',
                        'start_time'=> $host['start_billing_time'],
                        'end_time'  => $settleTime-1,
                    ]);
                }
                HostModel::update($update,['id' => $upgrade['host_id']]);
            }else{
                $upgrade['base_price'] = $upgrade['base_price']>0?$upgrade['base_price']:0;
                $upgrade['base_renew_price'] = $upgrade['base_renew_price']>0?$upgrade['base_renew_price']:0;

                $update = [
                    'first_payment_amount' => ($host['billing_cycle']=='recurring_postpaid' || $host['billing_cycle']=='recurring_prepayment') ? $upgrade['renew_price'] : ($host['billing_cycle']=='onetime'?$upgrade['price']:0),//$upgrade['price'],
                    'renew_amount' => ($host['billing_cycle']=='recurring_postpaid' || $host['billing_cycle']=='recurring_prepayment') ? $upgrade['renew_price'] : 0,
                    'base_price' => ($host['billing_cycle']=='recurring_postpaid' || $host['billing_cycle']=='recurring_prepayment') ? $upgrade['base_price'] : ($host['billing_cycle']=='onetime'?$upgrade['base_price']:0),
                    'base_renew_amount' => ($host['billing_cycle']=='recurring_postpaid' || $host['billing_cycle']=='recurring_prepayment') ? $upgrade['base_renew_price'] : 0,
                ];
                if($host['renew_use_current_client_level'] == 1){
                    $update['discount_renew_price'] = bcadd($host['discount_renew_price'], $upgrade['renew_price_difference_client_level_discount'], 4);
                    $update['discount_renew_price'] = $update['discount_renew_price'] > 0 ? $update['discount_renew_price'] : 0;
                }
                HostModel::update($update,['id' => $upgrade['host_id']]);
            }
        }
        
		# 添加到定时任务
		add_task([
			'type' => 'host_upgrade',
            'rel_id' => $upgrade['host_id'],
			'description' => lang('client_host_upgrade', ['{client_id}' => $upgrade['client_id'], '{host_id}' => $upgrade['host_id']]),
			'task_data' => [
				'upgrade_id'=>$id,//upgrade ID
			],		
		]);
        

        return true;
    }

    /**
     * 时间 2023-06-08
     * @title 订单列表导出EXCEL
     * @desc 订单列表导出EXCEL
     * @author theworld
     * @version v1
     * @param string param.keywords - 关键字,搜索范围:订单ID
     * @param string param.type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @param string param.status - 状态Unpaid未付款Paid已付款
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,type,create_time,amount,status
     * @param string param.sort - 升/降序 asc,desc
     */
    public function exportExcel($param){
        $data = $this->orderList($param);
        $data = $data['list'];
        foreach ($data as $key => $value) {
            $data[$key] = [
                'id' => $value['id'],
                'product_name' => !empty($value['product_names']) ? implode('', $value['product_names']) : '',
                'amount' => configuration('currency_prefix').$value['amount'],
                'gateway' => $value['credit']>0 ? ($value['amount']==$value['credit'] ? lang('order_credit') : (lang('order_credit').'+'.$value['gateway'])) : $value['gateway'],
                'create_time' => date("Y-m-d H:i", $value['create_time']),
                'status' => lang('order_status_'.$value['status']),
            ];
        }
        $field = [
            'id' => 'ID',
            'product_name' => lang('order_product_name'),
            'amount' => lang('order_amount'),
            'gateway' => lang('order_gateway'),
            'create_time' => lang('order_create_time'),
            'status' => lang('order_status'),
        ];

        return export_excel('order'.time(), $field, $data);
    }

    /**
     * 时间 2024-03-18
     * @title 锁定订单
     * @desc  锁定订单
     * @author hh
     * @version v1
     * @param   array param.id - 订单ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function lockOrder($param)
    {
        if(!isset($param['id']) || !is_array($param['id']) || empty($param['id'])){
            return ['status'=>400, 'msg'=>lang('id_error')];
        }
        $orderId = $this->whereIn('id', $param['id'])->column('id');
        if(count($orderId) != count($param['id'])){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        $this->whereIn('id', $orderId)->update([
            'is_lock'       => 1,
            'update_time'   => time(),
        ]);

        $description = lang('log_order_lock_success', [
            '{id}'  => implode(',', $orderId),
        ]);
        active_log($description, 'order');

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
        ];
        return $result;
    }

    /**
     * 时间 2024-03-18
     * @title 取消锁定订单
     * @desc  取消锁定订单
     * @author hh
     * @version v1
     * @param   array param.id - 订单ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function unlockOrder($param)
    {
        if(!isset($param['id']) || !is_array($param['id']) || empty($param['id'])){
            return ['status'=>400, 'msg'=>lang('id_error')];
        }
        $orderId = $this->whereIn('id', $param['id'])->column('id');
        if(count($orderId) != count($param['id'])){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        $this->whereIn('id', $orderId)->update([
            'is_lock'       => 0,
            'update_time'   => time(),
        ]);

        $description = lang('log_order_unlock_success', [
            '{id}'  => implode(',', $orderId),
        ]);
        active_log($description, 'order');

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
        ];
        return $result;
    }

    /**
     * 时间 2024-03-18
     * @title 回收订单
     * @desc  回收订单
     * @author hh
     * @version v1
     * @param   array param.id - 订单ID require
     * @param   int param.delete_host 1 是否删除产品:0否1是
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function recycleOrder($param)
    {
        $deleteHost = $param['delete_host'] ?? 1;
        $saveDays = configuration('order_recycle_bin_save_days');
        $time = time();
        $willDeleteTime = $saveDays === '0' ? 0 : $time + ($saveDays ?: 30)*24*3600;

        $orders = $this
                ->field('id')
                ->whereIn('id', $param['id'])
                ->select();

        $failMsg = [];
        foreach ($orders as $value) {
            // 未支付的按需订单不能删除
            if ($value['status'] == 'Unpaid' && $value['type'] == 'on_demand'){
                $failMsg[ $value['id'] ] = lang('unpaid_on_demand_order_cannot_delete');
                continue;
            }
            $hookRes = hook('before_order_recycle',['id'=>$value['id'] ]);
            foreach($hookRes as $v){
                if(isset($v['status']) && $v['status'] == 400){
                    $failMsg[ $value['id'] ] = $v['msg'] ?? '';
                    // return $v;
                }
            }
        }
        if(count($orders) == count($failMsg)){
            return ['status'=>400, 'msg'=>implode(';', array_filter(array_unique(array_values($failMsg)))) ?: lang('delete_fail')];
        }

        $hostIds = [];
        $this->startTrans();
        try{
            foreach($orders as $order){
                $orderId = $order['id'];
                if(isset($failMsg[$orderId])){
                    continue;
                }
                $change = $this->where('id', $orderId)->where('is_recycle', 0)->update([
                    'is_recycle'        => 1,
                    'recycle_time'      => $time,
                    'will_delete_time'  => $willDeleteTime,
                ]);
                if(!$change){
                    continue;
                }

                if($deleteHost == 1){
                    $hostIds = HostModel::where('order_id', $orderId)->where('status', '<>', 'Active')->where('is_delete', 0)->column('id');
                    if(!empty($hostIds)){
                        HostModel::where('order_id', $orderId)->where('status', '<>', 'Active')->update([
                            'is_delete'     => 1,
                            'delete_time'   => $time,
                        ]);
                    }
                }
                // 日志
                active_log(lang('log_order_recycle_success', ['{admin}'=>request()->admin_name, '{order}'=>'#'.$orderId]), 'order', $orderId);
            }
            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        foreach($hostIds as $hostId){
            hook('after_host_soft_delete', ['id'=>$hostId]);
        }

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
        ];
        return $result;
    }

    /**
     * 时间 2024-03-18
     * @title 恢复订单
     * @desc  恢复订单
     * @author hh
     * @version v1
     * @param   array param.id - 订单ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function recoverOrder($param)
    {
        if(!isset($param['id']) || !is_array($param['id']) || empty($param['id'])){
            return ['status'=>400, 'msg'=>lang('id_error')];
        }
        $orders = $this
                ->field('id')
                ->whereIn('id', $param['id'])
                ->where('is_recycle', 1)
                ->select()
                ->toArray();
        if(count($orders) != count($param['id'])){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        $this->startTrans();
        try{
            foreach($orders as $order){
                $orderId = $order['id'];
                $change = $this->where('id', $orderId)->where('is_recycle', 1)->update([
                    'is_recycle'        => 0,
                    'recycle_time'      => 0,
                    'will_delete_time'  => 0,
                ]);
                if(!$change){
                    continue;
                }
                // 还原产品
                HostModel::where('order_id', $orderId)->where('is_delete', 1)->update([
                    'is_delete'     => 0,
                    'delete_time'   => 0,
                    'update_time'   => time(),
                ]);

                // 日志
                active_log(lang('log_order_recover_from_recycle_bin_success', ['{admin}'=>request()->admin_name, '{order}'=>'#'.$orderId]), 'order', $orderId);
            }
            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
        ];
        return $result;
    }

    /**
     * @时间 2024-07-18
     * @title 银行转账提交申请
     * @desc  银行转账提交申请
     * @author hh
     * @version v1
     * @param   int param.id - 订单ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function submitApplication($param)
    {
        $clientId = get_client_id();
        $orderId = $param['id'];
        $gateway = 'UserCustom';

        // 是否启用了银行转账
        $plugin = PluginModel::where('name', $gateway)->where('module','gateway')->find();
        if(empty($plugin) || $plugin['status'] != 1){
            return ['status'=>400, 'msg'=>lang('order_cannot_use_this_gateway')];
        }
        $plugin['config'] = json_decode($plugin['config'],true);
        $plugin['title'] =  (isset($plugin['config']['module_name']) && !empty($plugin['config']['module_name']))?$plugin['config']['module_name']:$plugin['title'];

        $this->startTrans();
        try{
            $order = $this
                ->where('id', $orderId)
                ->where('client_id', $clientId)
                ->lock(true)
                ->find();
            if(empty($order)){
                return ['status'=>400, 'msg'=>lang('order_is_not_exist') ];
            }
            if($order['status'] != 'Unpaid'){
                return ['status'=>400, 'msg'=>lang('order_status_changed_please_refresh_and_retry')];
            }
            if($order['amount_unpaid'] == 0){
                return ['status'=>400, 'msg'=>lang('order_free_cannot_submit_application')];
            }
            // 订单使用了余额
            if($order['credit'] > 0){
                $credit = ClientModel::where('id', $clientId)->lock(true)->value('credit');
                // 余额不足
                if($credit < $order['credit']){
                    return ['status'=>400, 'msg'=>lang('insufficient_credit_deduction_failed')];
                }
                // 变更用户余额
                update_credit([
                    'type'      => 'Applied',
                    'amount'    => -$order->credit,
                    'notes'     => lang('order_apply_credit')."#{$order->id}",
                    'client_id' => $order->client_id,
                    'order_id'  => $order->id,
                    'host_id'   => 0,
                ]);
                // ClientModel::where('id', $clientId)->update('credit', bcsub($credit, $order['credit'], 2));
            }
            $this->where('id', $order['id'])->update([
                'status'                    => 'WaitUpload',
                'gateway'                   => $gateway,
                'gateway_name'              => $plugin['title'],
                'submit_application_time'   => time(),
            ]);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('fail_message')];
        }

        $description = lang('log_order_submit_application_success', [
            '{order}'=>'#'.$orderId
        ]);
        active_log($description, 'order', $orderId);

        return ['status'=>200, 'msg'=>lang('success_message')];
    }

    /**
     * @时间 2024-07-18
     * @title 上传凭证
     * @desc  上传凭证
     * @author hh
     * @version v1
     * @param   int param.id - 订单ID require
     * @param   array param.voucher - 上传的凭证 require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function uploadOrderVoucher($param)
    {
        $orderId = $param['id'];

        $order = $this
                ->where('id', $orderId)
                ->find();
        if(empty($order)){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist') ];
        }
        // 后台管理员上传
        if(app('http')->getName() == 'admin'){
            $clientId = $order['client_id'];
        }else{
            $clientId = get_client_id();
        }
        if($order['client_id'] != $clientId){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist') ];
        }
        // 只能在状态待上传/待审核/审核失败
        if(!in_array($order['status'], ['WaitUpload','WaitReview','ReviewFail'])){
            return ['status'=>400, 'msg'=>lang('order_status_changed_please_refresh_and_retry')];
        }
        $order['voucher'] = !empty($order['voucher']) ? explode(',', $order['voucher']) : [];

        // 记录凭证
        $voucher = [];

        // 先移动文件
        $UploadLogic = new UploadLogic(WEB_ROOT . 'upload/common/order/');
        foreach($param['voucher'] as $file){
            if(!in_array($file, $order['voucher'])){
                $upload = $UploadLogic->moveTo($file);
                if (isset($upload['error'])){
                    return ['status'=>400, 'msg'=>$upload['error'] ];
                }
            }
            $voucher[] = $file;
        }
        if(empty($voucher)){
            return ['status'=>400, 'msg'=>lang('please_upload_order_voucher')];
        }

        $update = $this
                ->where('id', $orderId)
                ->whereIn('status', ['WaitUpload','WaitReview','ReviewFail'])
                ->update([
                    'status'    => 'WaitReview',
                    'voucher'   => implode(',', $voucher),
                ]);

        // 前台才通知
        if(app('http')->getName() != 'admin'){
            system_notice([
                'name'                  => 'offline_payment_application',
                'email_description'     => lang('offline_payment_application_send_mail'),
                'sms_description'       => lang('offline_payment_application_send_sms'),
                'task_data' => [
                    'client_id' => $clientId,
                    'order_id'  => $orderId,
                ],
            ]);
        }

        $description = lang('log_order_upload_voucher_success', [
            '{order}'=>'#'.$orderId
        ]);
        active_log($description, 'order', $orderId);

        return ['status'=>200, 'msg'=>lang('success_message') ];
    }

    /**
     * @时间 2024-07-18
     * @title 变更支付方式
     * @desc  变更支付方式
     * @author hh
     * @version v1
     * @param   int param.id - 订单ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function changeGateway($param)
    {
        $clientId = get_client_id();
        $orderId = $param['id'];
        
        $order = $this
            ->where('id', $orderId)
            ->find();
        if(empty($order) || $order['client_id'] != $clientId){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist') ];
        }
        if($order['gateway'] != 'UserCustom' || !in_array($order['status'], ['WaitUpload','WaitReview','ReviewFail'])){
            return ['status'=>400, 'msg'=>lang('order_status_changed_please_refresh_and_retry')];
        }
        
        $this->startTrans();
        try{
            $update = $this
                    ->where('id', $orderId)
                    ->whereIn('status', ['WaitUpload','WaitReview','ReviewFail'])
                    ->update([
                        'status'        => 'Unpaid',
                        'gateway'       => '',
                        'gateway_name'  => '',
                        'submit_application_time' => 0,
                        'voucher'       => '',
                    ]);
            if(!$update){
                return ['status'=>400, 'msg'=>lang('order_status_changed_please_refresh_and_retry')];
            }
            // 退钱
            if($order['credit'] > 0){
                update_credit([
                    'type'      => 'Refund',
                    'amount'    => $order->credit,
                    'notes'     => lang('order_change_gateway_refund_credit')."#{$order->id}",
                    'client_id' => $order->client_id,
                    'order_id'  => $order->id,
                    'host_id'   => 0,
                ]);
            }

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('fail_message')];
        }

        $description = lang('log_order_change_gateway_success', [
            '{order}'=>'#'.$orderId
        ]);
        active_log($description, 'order', $orderId);

        return ['status'=>200, 'msg'=>lang('success_message')];
    }

    /**
     * @时间 2024-07-18
     * @title 审核订单
     * @desc  审核订单
     * @author hh
     * @version v1
     * @param   int param.id - 订单ID require
     * @param   int param.pass - 审核状态(0=不通过,1=通过) require
     * @param   string param.review_fail_reason - 审核失败原因
     * @param   string param.transaction_number - 交易流水号
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function reviewOrder($param)
    {
        $orderId = $param['id'];

        $order = $this
                ->where('id', $orderId)
                ->find();
        if(empty($order)){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist') ];
        }
        // 只能在状态待审核
        if(!in_array($order['status'], ['WaitReview'])){
            return ['status'=>400, 'msg'=>lang('order_status_changed_please_refresh_and_retry')];
        }

        if($param['pass'] == 1){
            $this->startTrans();
            try{
                $update = $this->where('id', $orderId)->whereIn('status', ['WaitReview'])->update([
                    'status'    => 'Paid',
                    'pay_time'  => time(),
                ]);
                if(!$update){
                    throw new \Exception(lang('order_status_changed_please_refresh_and_retry'));
                }
                if($order['amount_unpaid']>0){
                    // 创建交易流水
                    TransactionModel::create([
                        'order_id'              => $order['id'],
                        'amount'                => $order['amount_unpaid'],
                        'gateway'               => $order['gateway'],
                        'gateway_name'          => $order['gateway_name'],
                        'transaction_number'    => $param['transaction_number'] ?? '',
                        'client_id'             => $order['client_id'],
                        'create_time'           => time(),
                    ]);
                }

                $this->processPaidOrder($orderId);
                
                $this->commit();
            }catch(\Exception $e){
                $this->rollback();
                return ['status'=>400, 'msg'=>$e->getMessage()];
            }

            $description = lang('log_order_review_pass', [
                '{order}'=>'#'.$orderId
            ]);
        }else{
            // 审核失败
            $update = $this->where('id', $orderId)->whereIn('status', ['WaitReview'])->update([
                'status'    => 'ReviewFail',
                'review_fail_reason' => $param['review_fail_reason'],
            ]);

            $description = lang('log_order_review_not_pass', [
                '{order}'   => '#'.$orderId,
                '{reason}'  => $param['review_fail_reason'],
            ]);
        }
        if($update){
            // 通过
            if($param['pass'] == 1){
                system_notice([
                    'name'                  => 'order_review_pass',
                    'email_description'     => lang('order_review_pass_send_email'),
                    'sms_description'       => lang('order_review_pass_send_sms'),
                    'task_data' => [
                        'client_id' => $order['client_id'],
                        'order_id'  => $orderId,
                        'template_param'=>[
                            'order_review_time' => date('Y-m-d H:i:s'),
                        ],
                    ],
                ]);
            }else{
                system_notice([
                    'name'                  => 'order_review_reject',
                    'email_description'     => lang('order_review_reject_send_email'),
                    'sms_description'       => lang('order_review_reject_send_sms'),
                    'task_data' => [
                        'client_id' => $order['client_id'],
                        'order_id'  => $orderId,
                        'template_param'=>[
                            'order_review_time' => date('Y-m-d H:i:s'),
                        ],
                    ],
                ]);
            }

            active_log($description, 'order', $orderId);

            $result = [
                'status' => 200,
                'msg'    => lang('success_message'),
            ];
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang('order_status_changed_please_refresh_and_retry'),
            ];
        }
        return $result;
    }

    /**
     * @时间 2024-07-22
     * @title 线下转账订单取消退款
     * @desc  线下转账订单取消退款
     * @author hh
     * @version v1
     * @param   int|array id - 订单ID require
     */
    public function cancelUserCustomOrder($id)
    {
        if(!is_array($id)){
            $id = [$id];
        }
        $useCreditOrder = $this
                        ->whereIn('id', $id)
                        ->whereIn('status',['WaitUpload','WaitReview','ReviewFail'])
                        ->where('gateway', 'UserCustom')
                        ->where('credit', '>', 0)
                        // ->where('is_recycle', 0)
                        ->select();
        foreach($useCreditOrder as $order){
            // 退钱
            update_credit([
                'type'      => 'Refund',
                'amount'    => $order->credit,
                'notes'     => lang('order_cancel_refund_credit')."#{$order->id}",
                'client_id' => $order->client_id,
                'order_id'  => $order->id,
                'host_id'   => 0,
            ]);
        }
    }

    /**
     * @时间 2024-11-25
     * @title 计算订单可退金额
     * @desc  计算订单可退金额
     * @author hh
     * @version v1
     * @param   int|OrderModel param.order - 订单ID|订单模型实例 require
     * @param   int param.host_id - 产品ID
     * @param   int param.addon_refund_host_id - 正在插件退款的产品ID(credit还没减掉)
     * @return  string gateway - 支付方式标识
     * @return  string gateway_name - 支付方式名称
     * @return  string refund_credit - 已退余额部分
     * @return  string refund_gateway - 已退渠道部分
     * @return  string refund_addon - 已退插件余额部分
     * @return  string leave_total - 剩余可退
     * @return  string leave_credit - 剩余可退余额
     * @return  string leave_gateway - 剩余可退渠道
     * @return  string leave_host_amount - 产品剩余可退
     * @return  string gateway_to_credit - 渠道退到余额部分,记录正值
     * @return  int host_order_item[].id - 订单子项ID
     * @return  string host_order_item[].product_name - 商品名称
     * @return  string host_order_item[].name - 产品标识
     * @return  string host_order_item[].status - Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @return  string host_order_item[].amount - 金额
     * @return  string host_order_item[].description - 描述
     */
    public function orderRefundIndex($param)
    {
        // 单个订单产品
        $param['host_id'] = $param['host_id'] ?? 0;
        $param['addon_refund_host_id'] = $param['addon_refund_host_id'] ?? 0;

        $order = $param['order'];
        if(is_numeric($order)){
            $order = $this->find($order);
        }
        if(empty($order)){
            return [];
        }
        $clientId = $order['client_id'];

        // 已退余额/原渠道
        $refundCredit = 0;
        $refundGateway = 0;
        $refundAddon = 0; // 退款插件部分

        // 剩余可退金额,这里已经减去了已退款的余额
        $leaveCredit = $order['credit'];
        if($order['refund_gateway_to_credit'] > 0){
            $leaveCredit = bcsub($leaveCredit, $order['refund_gateway_to_credit'], 2);
        }
        if($order['gateway'] == 'credit_limit'){
            // 信用额支付的
            $leaveGateway = $order['amount'];
        }else{
            // 这里只能用流水,流水已经减去了退款部分
            $leaveGateway = TransactionModel::where('order_id', $order->id)->sum('amount') ?? 0;
        }

        // 关联的产品
        $OrderItemModel = new OrderItemModel();
        $orderItem = $OrderItemModel
                    ->field('id,host_id,type,amount,product_id,description')
                    ->where('order_id', $order->id)
                    ->where('host_id', '>', 0)
                    ->select()
                    ->toArray();

        $hostAmount = [];
        $hostOrderItem = [];
        foreach($orderItem as $v){
            if(!isset($hostAmount[ $v['host_id'] ])){
                $hostAmount[ $v['host_id'] ] = 0;
            }
            $hostAmount[ $v['host_id'] ] = bcadd($hostAmount[$v['host_id']], $v['amount'], 2);

            if($param['host_id'] > 0 && $v['host_id'] == $param['host_id']){
                $isHost = true;
                $productName = '';
                if(in_array($v['type'], ['addon_promo_code', 'addon_idcsmart_promo_code', 'addon_idcsmart_client_level', 'addon_event_promotion'])){
                    $isHost = false;
                }else if( $v['amount'] >= 0 ){
                    $host = HostModel::find($v['host_id']);
                    $productName = ProductModel::where('id', $v['product_id'])->value('name');
                }else{
                    $isHost = false;
                }

                $hostOrderItem[] = [
                    'id'            => $v['id'],
                    'product_name'  => $isHost ? $productName : $v['description'],
                    'name'          => $isHost && !empty($host) ? $host['name'] : '',
                    'status'        => $isHost && !empty($host) ? $host['status'] : '',
                    'amount'        => $v['amount'],
                    'description'   => $isHost ? $v['description'] : '',
                ];
            }
        }

        // 产品可退金额
        $leaveHostAmount = 0;
        if($param['host_id'] > 0){
            if(!isset($hostAmount[ $param['host_id'] ])){
                return [];
            }
            // 产品可退金额
            $leaveHostAmount = $hostAmount[ $param['host_id'] ];
        }

        $hostIds = array_keys($hostAmount);
        
        // 获取订单已退金额
        $RefundRecordModel = new RefundRecordModel();
        $refundRecord = $RefundRecordModel
                        ->where('order_id', $order->id)
                        // ->where('type', '<>', 'transaction')
                        ->whereIn('status', ['Refunded'])
                        ->where('refund_type', 'order')
                        ->select()
                        ->toArray();

        foreach($refundRecord as $v){
            // 兼容原退款数据
            // if(in_array($v['type'], ['credit','transaction','original'])){
                // if(in_array($v['type'], ['transaction','original'])){
                    // $refundGateway = bcadd($refundGateway, $v['amount'], 2);
                // }else{
                    // $refundCredit = bcadd($refundCredit, $v['amount'], 2);
                // }
            // }else{
                // 新的数据
                $refundCredit = bcadd($refundCredit, $v['refund_credit'], 2);
                $refundGateway = bcadd($refundGateway, $v['refund_gateway'], 2);
            // }
            if($param['host_id'] > 0 && $v['host_id'] == $param['host_id']){
                $leaveHostAmount = bcsub($leaveHostAmount, $v['amount'], 2);
            }
        }

        // 获取退款记录中的插件退款
        if(!empty($hostIds)){
            $refundRecord = $RefundRecordModel
                            ->field('id,host_id')
                            ->where('client_id', $clientId)
                            ->whereIn('host_id', $hostIds)
                            ->where('refund_type', 'addon')
                            ->where('status', 'Refunded')
                            ->select()
                            ->toArray();

            if(!empty($refundRecord)){
                $refundRecord = array_column($refundRecord, 'id', 'host_id') ?? [];

                // 是否有插件退款
                foreach($hostAmount as $k=>$v){
                    if(isset($refundRecord[ $k ]) && $refundRecord[ $k ] > 0){
                        $refundCredit = bcadd($refundCredit, max($v, 0), 2);
                        $refundAddon = bcadd($refundAddon, max($v, 0), 2);

                        // 当前产品已插件退款
                        if($param['host_id'] > 0 && $k == $param['host_id']){
                            $leaveHostAmount = 0;
                        }
                        // 计算当前插件退款产品
                        if($param['addon_refund_host_id'] > 0 && $k == $param['addon_refund_host_id']){
                            $leaveCredit = bcsub($leaveCredit, max($v, 0), 2);
                        }
                    }
                }
            }
        }

        // 信用额支付
        if($order['gateway'] == 'credit_limit'){
            $leaveGateway = bcsub($leaveGateway, $refundGateway, 2);
        }

        // 流水转到余额部分
        $gatewayToCredit = 0;
        // 判断可退金额
        if($leaveCredit < 0 && $leaveGateway < 0){
            $gatewayToCredit = abs($leaveCredit);
            $leaveCredit = 0;
            $leaveGateway = 0;
        }else if($leaveGateway < 0){
            $leaveCredit = bcadd($leaveCredit, $leaveGateway, 2);
            $leaveCredit = max($leaveCredit, 0);
            $leaveGateway = 0;
        }else if($leaveCredit < 0){
            $gatewayToCredit = abs($leaveCredit);
            $leaveGateway = bcadd($leaveGateway, $leaveCredit, 2);
            $leaveGateway = max($leaveGateway, 0);
            $leaveCredit = 0;
        }
        $leaveTotal = bcadd($leaveCredit, $leaveGateway, 2);
        // $gatewayToCredit = bcadd($order['refund_gateway_to_credit'], $gatewayToCredit, 2); // 加上原订单的

        // 有产品可退金额时
        if($leaveHostAmount > 0){
            if($leaveHostAmount > $leaveTotal){
                $leaveHostAmount = $leaveTotal;
            }
        }
        $leaveHostAmount = max(0, $leaveHostAmount);

        $data = [
            'gateway'           => $order['gateway'],
            'gateway_name'      => $order['gateway_name'],
            'refund_credit'     => amount_format($refundCredit),
            'refund_gateway'    => amount_format($refundGateway),
            'refund_addon'      => amount_format($refundAddon),
            'leave_total'       => amount_format($leaveTotal),
            'leave_credit'      => amount_format($leaveCredit),
            'leave_gateway'     => amount_format($leaveGateway),
            'leave_host_amount' => amount_format($leaveHostAmount),
            'gateway_to_credit' => amount_format($gatewayToCredit),
            'host_order_item'   => $hostOrderItem,
            'host_id'           => $hostIds,
        ];

        return $data;
    }

    /**
     * @时间 2024-12-03
     * @title 前台订单交易记录
     * @desc  前台订单交易记录
     * @author hh
     * @version v1
     * @param   int param.id - 订单ID require
     * @return  int list[].create_time - 交易时间
     * @return  int list[].host_id - 产品ID
     * @return  string list[].host_name - 产品标识
     * @return  int list[].product_id - 商品ID
     * @return  string list[].product_name - 商品名称
     * @return  string list[].description - 描述
     * @return  string list[].amount - 金额
     */
    public function orderTransactionRecord($param)
    {
        $clientId = get_client_id();
        $orderId = $param['id'] ?? 0;
        $order = $this
                ->where('id', $orderId)
                ->where('client_id', $clientId)
                ->find();

        $list = [];
        if(empty($order) || $order['is_recycle'] == 1){
            return ['list'=>$list ];
        }

        // 信用额
        if($order['gateway'] == 'credit_limit'){
            // 获取退款记录
            $hostId = OrderItemModel::where('order_id', $orderId)->where('host_id', '>', 0)->column('host_id');

            $list = RefundRecordModel::alias('rr')
                    ->field('rr.refund_time create_time,rr.host_id,h.name host_name,h.product_id,p.name product_name,rr.notes description,rr.amount')
                    ->leftJoin('host h', 'rr.host_id=h.id')
                    ->leftJoin('product p', 'h.product_id=p.id')
                    ->where('rr.client_id', $clientId)
                    ->where('rr.status', 'Refunded')
                    ->where(function($query) use ($orderId, $hostId){
                        $query->whereOr('rr.order_id', $orderId)
                              ->whereOr(function($query) use ($hostId) {
                                $query->where('rr.refund_type', '=', 'addon')
                                      ->where('rr.host_id', 'IN', $hostId);
                              });
                    })
                    ->withAttr('description', function($val){
                        return lang('order_transaction_refund_to_credit_limit');
                    })
                    ->order('rr.id', 'desc')
                    ->select()
                    ->toArray();
        }else{
            // 获取交易流水
            $transaction = TransactionModel::alias('t')
                    ->field('t.create_time,t.host_id,h.name host_name,h.product_id,p.name product_name,t.transaction_number description,t.amount')
                    ->leftJoin('host h', 't.host_id=h.id')
                    ->leftJoin('product p', 'h.product_id=p.id')
                    ->where('t.order_id', $orderId)
                    ->where('t.client_id', $clientId)
                    ->select()
                    ->toArray();

            if($order['type'] != 'recharge'){
                // 用户余额记录
                $credit = ClientCreditModel::alias('cc')
                    ->field('cc.create_time,cc.host_id,h.name host_name,h.product_id,p.name product_name,cc.type description,cc.amount')
                    ->leftJoin('host h', 'cc.host_id=h.id')
                    ->leftJoin('product p', 'h.product_id=p.id')
                    ->where('cc.order_id', $orderId)
                    ->where('cc.client_id', $clientId)
                    ->withAttr('description', function($val){
                        if($val == 'Applied'){
                            $val = lang('order_transaction_credit_apply_to_order');
                        }else if($val == 'Overpayment' || $val == 'Underpayment'){
                            $val = lang('order_transaction_credit_recharge');
                        }else if($val == 'Refund'){
                            $val = lang('order_transaction_credit_refund');
                        }
                        return $val;
                    })
                    ->select()
                    ->toArray();

                $list = array_merge($transaction, $credit);
            }else{
                $list = $transaction;
            }

            // 按时间排序
            usort($list, function ($a, $b) {
                return $b['create_time'] > $a['create_time'] ? 1 : -1;
            });
        }
        
        foreach($list as $k=>$v){
            $list[$k]['host_name'] = $v['host_name'] ?? '';
            $list[$k]['product_id'] = $v['product_id'] ?? 0;
            $list[$k]['product_name'] = $v['product_name'] ?? '';
            $list[$k]['amount'] = amount_format( abs($v['amount']) ); // 都显示正值
        }

        return ['list'=>$list ];
    }

    /**
     * @时间 2025-03-31
     * @title 按需订单支付处理
     * @desc  按需订单支付处理
     * @author hh
     * @version v1
     * @param  int id - 产品ID require
     * @return bool
     */
    public function onDemandOrderHandle($id): bool
    {
        $host = HostModel::find($id);
        if(empty($host)){
            return false;
        }
        // 判断产品状态,宽限期
        if($host['status'] == 'Grace'){
            // 是否还有其他未支付的订单
            $unpaid = $this
                    ->alias('o')
                    ->leftJoin('order_item oi', 'o.id=oi.order_id')
                    ->where('o.type', 'on_demand')
                    ->where('o.status', 'Unpaid')
                    ->where('o.is_recycle', 0)
                    ->where('oi.host_id', $id)
                    ->value('o.id');
            if(empty($unpaid)){
                // 退出宽限期
                $host->exitGracePeriod($host);
            }
        }else if($host['status'] == 'Keep'){
            // 是否还有其他未支付的订单
            $unpaid = $this
                    ->alias('o')
                    ->leftJoin('order_item oi', 'o.id=oi.order_id')
                    ->where('o.type', 'on_demand')
                    ->where('o.status', 'Unpaid')
                    ->where('o.is_recycle', 0)
                    ->where('oi.host_id', $id)
                    ->value('o.id');
            if(empty($unpaid)){
                // 退出保留期
                $host->exitKeepPeriod($host);
            }
        }
        return true;
    }

    /**
     * @时间 2025-04-07
     * @title 变更计费周期订单支付处理
     * @desc  变更计费周期订单支付处理
     * @author hh
     * @version v1
     * @param  int id - 变更计费方式ID require
     * @return bool
     */
    public function changeBillingCycleOrderHandle($id)
    {
        $ChangeBillingCycleModel = new ChangeBillingCycleModel();
        $changeBillingCycle = $ChangeBillingCycleModel->find($id);
        if (empty($changeBillingCycle)){
            return false;
        }
        $hostData = json_decode($changeBillingCycle['host_data'], true);
        # 修改状态
        $update = $ChangeBillingCycleModel
                ->where('id', $id)
                ->where('status', 'Unpaid')
                ->update([
                    'status'        => 'Pending',
                    'update_time'   => time(),
                ]);
        if(empty($update)){
            return false;
        }
        $HostModel = new HostModel();
        $host = $HostModel->find($changeBillingCycle['host_id']);

        // 添加出账队列,按需转包年包月
        if($changeBillingCycle['old_billing_cycle'] == 'on_demand' && $changeBillingCycle['new_billing_cycle'] == 'recurring_prepayment'){
            $settleTime = time();
            $OnDemandPaymentQueueModel = new OnDemandPaymentQueueModel();
            $res = $OnDemandPaymentQueueModel->createOnDemandPaymentQueue([
                'host'      => $host,
                'type'      => 'on_demand_recurring_prepayment',
                'start_time'=> $host['start_billing_time'],
                'end_time'  => $settleTime,
            ]);
            // 重新计算到期时间
            $hostData['due_time'] = $hostData['billing_cycle_time'] + $settleTime;
        }else if($changeBillingCycle['old_billing_cycle'] == 'recurring_prepayment' && $changeBillingCycle['new_billing_cycle'] == 'on_demand'){
            // 重新计算出账时间
            $hostData['start_billing_time'] = $host['due_time'];
            $hostData['next_payment_time'] = $HostModel->calNextPaymentTime($hostData, $host['due_time']);
        }
        if(!empty($hostData)){
            $host->save($hostData);
        }
        
		# 添加到定时任务
		add_task([
			'type'      => 'host_change_billing_cycle',
            'rel_id'    => $changeBillingCycle['host_id'],
			'description' => lang('client_host_change_billing_cycle', ['{client_id}' => $changeBillingCycle['client_id'], '{host_id}' => $changeBillingCycle['host_id']]),
			'task_data' => [
				'change_billing_cycle_id' => $id,
			],		
		]);
        return true;
    }

    /**
     * @时间 2025-04-11
     * @title 合并订单支付处理
     * @desc  合并订单支付处理
     * @author hh
     * @version v1
     * @param  OrderModel order - 订单模型实例 require
     * @param  OrderItemModel orderItem - 订单子项模型实例 require
     * @return bool
     */
    public function combineOrderHandle($order, $orderItem)
    {
        $update = [
            'gateway'=>$order['gateway'],
            'gateway_name'=>$order['gateway_name'],
            'status'=>'Paid',
            'pay_time'=>time(),
            'update_time'=>time()
        ];

        if ($order['credit']>0 && $order['amount']>0){
            $ratio = bcdiv($orderItem['amount'],$order['amount'],2);

            $update['credit'] = bcmul($order['credit'],$ratio,2);
        }

        # 更改子订单为已支付
        $this->where('id',$orderItem['rel_id'])->update($update);

        // 更新订单子项支付方式
        $OrderItemModel = new OrderItemModel();
        $OrderItemModel->where('order_id',$orderItem['rel_id'])->update([
            'gateway'=>$order['gateway'],
            'gateway_name'=>$order['gateway_name'],
        ]);

        $this->processPaidOrder($orderItem['rel_id']);
    }

    /**
     * @时间 2025-04-07
     * @title 创建产品计费周期变更订单
     * @desc  创建产品计费周期变更订单,该方法不验证用户和产品状态,需调用前自行验证
     * @author hh
     * @version v1
     * @throws \Exception
     * @param   array param - 参数
     * @param   int param.host_id - 产品ID require
     * @param   string param.type - 订单类型(change_billing_cycle) require
     * @param   string param.amount - 订单金额(可折扣) require
     * @param   string param.description - 订单描述 require
     * @param   array param.customfield - 自定义参数
     * @param   array param.config_options - 自定义参数,留给模块处理
     * @param   array param.host_data - 变更后产品信息 require
     * @param   array param.order_item - 追加订单子项
     * @param   float param.discount - 用户等级折扣
     * @param   float param.discount_order_price - 购买可用用户等级部分
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function createChangeBillingCycleOrder($param): array
    {
        $hostId = $param['host_id'] ?? 0;
        $host = HostModel::find($hostId);
        if(empty($host) || $host['is_delete']){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        if(empty($param['host_data'])){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        $param['config_options'] = $param['config_options'] ?? [];
        $time = time();

        try{
            // hh 20240410 产品升降级时 判断是否有正在执行的升降级，有则拦截订单 TAPD-ID1005869
            $task = TaskModel::where('rel_id', $hostId)->where('type', 'host_upgrade')->whereRaw("(status='Wait' OR (status='Exec' AND start_time>=".($time-600).'))')->find();
            if(!empty($task)){
                throw new \Exception(lang('order_host_is_upgrade_please_wait_and_retry'));
            }
            // wyh 20230508 删除未支付升降级订单
            $this->deleteHostUnpaidUpgradeOrder($hostId);
        }catch(\Exception $e){
            return ['status'=>400, 'msg'=>$e->getMessage() ];
        }

        // 金额
        $amount = $param['amount'];
        $discount = $param['discount'] ?? 0; // 用户等级折扣     
        $param['upgrade_refund'] = $param['upgrade_refund'] ?? 0; // 是否退款,默认不退款

        $this->startTrans();
        try{
            // 创建订单
            $order = $this->create([
                'client_id' => $host['client_id'],
                'type'      => 'change_billing_cycle',
                'status'    => $amount>0 ? 'Unpaid' : 'Paid',
                'amount'    => $amount,
                'credit'    => 0,
                'amount_unpaid'  => $amount>0 ? $amount : 0,
                'upgrade_refund' => $param['upgrade_refund'],
                'pay_time' => $amount>0 ? 0 : $time,
                'create_time' => $time,
                'admin_id' => get_admin_id(),
            ]);
            
            // 创建订单子项
            if (isset($param['description']) && is_array($param['description'])){
                $param['description'] = implode("\n",$param['description']);
            }

            // 添加变更计费记录
            $ChangeBillingCycleModel = new ChangeBillingCycleModel();
            $changeBillingCycle = $ChangeBillingCycleModel->create([
                'client_id' => $host['client_id'],
                'host_id'   => $host['id'],
                'order_id'  => $order->id,
                'old_billing_cycle' => !empty($host['is_ontrial']) ? config('idcsmart.pay_ontrial') : $host['billing_cycle'],
                'new_billing_cycle' => $param['host_data']['billing_cycle'],
                'host_data' => json_encode($param['host_data']),
                'data'      => json_encode($param['config_options']),
                'status'    => 'Unpaid',
                'create_time'       => $time,
            ]);

            $orderItem = OrderItemModel::create([
                'order_id' => $order->id,
                'client_id' => $host['client_id'],
                'host_id' => $host['id'],
                'product_id' => $host['product_id'],
                'type' => 'change_billing_cycle',
                'rel_id' => $changeBillingCycle->id,
                'description' => $param['description'] ?? '',
                'amount' => bcadd($amount, $discount, 2),
                'notes' => '',
                'create_time' => $time,
            ]);

            $clientLevel = $this->getClientLevel([
                'product_id'    => $host['product_id'],
                'client_id'     => $host['client_id'],
            ]);
            if(!empty($clientLevel) && !empty($param['discount_order_price']) ){
                $discount = bcdiv($param['discount_order_price']*$clientLevel['discount_percent'], 100, 2);

                $orderItem = [];
                $orderItem[] = [
                    'order_id'      => $order->id,
                    'client_id'     => $host['client_id'],
                    'host_id'       => $host['id'],
                    'product_id'    => $host['product_id'],
                    'type'          => 'addon_idcsmart_client_level',
                    'rel_id'        => $clientLevel['id'],
                    'amount'        => -$discount,
                    'description'   => lang_plugins('mf_cloud_client_level', [
                        '{name}'    => $clientLevel['name'],
                        '{value}'   => $clientLevel['discount_percent'],
                    ]),
                    'create_time'   => time(),
                ];
                $OrderItemModel = new OrderItemModel();
                $OrderItemModel->saveAll($orderItem);

                // 修改订单金额
                $this->where('id', $order->id)->update([
                    'amount'    => bcsub($amount, $discount, 2),
                    'amount_unpaid'    => bcsub($amount, $discount, 2),
                ]);
            }

            // if(!empty($appendOrderItem)){
            //     foreach($appendOrderItem as $k=>$v){
            //         $appendOrderItem[$k]['order_id'] = $order->id;
            //         $appendOrderItem[$k]['client_id'] = $host['client_id'];
            //         $appendOrderItem[$k]['host_id'] = $host['id'];
            //         $appendOrderItem[$k]['product_id'] = $host['product_id'];
            //         $appendOrderItem[$k]['create_time'] = $time;
            //     }
            //     $OrderItemModel = new OrderItemModel();
            //     $OrderItemModel->saveAll($appendOrderItem);
            // }

            hook('after_order_create',['id'=>$order->id,'customfield'=>$param['customfield']??[]]);

            // 代理不能执行该方法
            // update_upstream_order_profit($order->id);

            # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
            $amount = $this->where('id',$order->id)->value('amount');

            $discountPromo = 0;
            $excludeWithClientLevel = 0;
            $hookPromoCodeResults = hook('apply_promo_code', [
                'host_id'   => $host->id,
                'price'     => $param['host_data']['renew_amount'],
                'scene'     => 'change_billing_cycle',
                'duration'  => $param['host_data']['billing_cycle_time'],
                'old_billing_cycle' => $host['billing_cycle'],
                'new_billing_cycle' => $param['host_data']['billing_cycle'],
                'promo_code'    => $param['customfield']['promo_code'] ?? '',
            ]);
            foreach ($hookPromoCodeResults as $hookPromoCodeResult){
                if ($hookPromoCodeResult['status']==200){
                    if (isset($hookPromoCodeResult['data']['loop']) && $hookPromoCodeResult['data']['loop']){
                        $discountPromo = $hookPromoCodeResult['data']['discount']??0;
                        $excludeWithClientLevel = $hookPromoCodeResult['data']['exclude_with_client_level']??0;
                    }
                }
            }
            $renewPrice = $param['host_data']['renew_amount'] - $discountPromo;

            if(!$excludeWithClientLevel){
                if(!empty($param['host_data']['renew_use_current_client_level']) && !empty($param['host_data']['discount_renew_price'])){
                    $hookDiscountResults = hook("client_discount_by_amount",['client_id'=>$host['client_id'],'product_id'=>$host['product_id'],'amount'=>$param['host_data']['discount_renew_price'], 'id'=>$order->id ]);
                    foreach ($hookDiscountResults as $hookDiscountResult){
                        if ($hookDiscountResult['status']==200){
                            $discountClient = $hookDiscountResult['data']['discount']??0;
                            $renewPrice = bcsub($renewPrice, $discountClient, 4);
                        }
                    }
                }else{
                    $hookDiscountResults = hook("client_discount_by_amount",['client_id'=>$host['client_id'],'product_id'=>$host['product_id'],'amount'=>$param['host_data']['renew_amount'], 'id'=>$order->id ]);
                    foreach ($hookDiscountResults as $hookDiscountResult){
                        if ($hookDiscountResult['status']==200){
                            $discountClient = $hookDiscountResult['data']['discount']??0;
                            $renewPrice = bcsub($renewPrice, $discountClient, 4);
                        }
                    }
                }
            }
            
            $renewPrice = $renewPrice>0?$renewPrice:0;
            $renewPrice = in_array($param['host_data']['billing_cycle'], ['recurring_postpaid','recurring_prepayment','on_demand']) ? $renewPrice : 0;
            
            // 覆盖下
            $param['host_data']['first_payment_amount'] = $amount;
            $param['host_data']['renew_amount'] = amount_format($renewPrice);

            // 重新保存续费金额
            $changeBillingCycle->save([
                'host_data' => json_encode($param['host_data']),
            ]);

            $product = (new ProductModel())->find($host['product_id']);
            if (in_array($param['host_data']['billing_cycle'],['onetime','free','on_demand'])){
                $desDueTime = '∞';
            }else{
                $desDueTime = date('Y/m/d',$host['due_time']);
            }

            $billingCycleName = multi_language_replace($host['billing_cycle_name']);
            
            // 非按需才添加
            if($host['billing_cycle'] != 'on_demand'){
                $des = lang('order_description_append',['{product_name}'=>$product->name,'{name}'=>$host['name'],'{billing_cycle_name}'=>$billingCycleName,'{time}'=>date('Y/m/d',time()) . '-' . $desDueTime]);
                $newOrderItem = OrderItemModel::find($orderItem['id']);
                $newOrderItem->save([
                    'description' => $newOrderItem['description'] . "\n" . $des
                ]);
            }

            if($amount<=0){
                $this->processPaidOrder($order->id);
                
                // 目前转换不存在退款
                // if($amount<0 && $param['upgrade_refund']==1){

                //     $result = update_credit([
                //         'type' => 'Refund',
                //         'amount' => -$amount,
                //         'notes' => lang('upgrade_refund'),
                //         'client_id' => $host['client_id'],
                //         'order_id' => $order->id,
                //         'host_id' => $host['id']
                //     ]);
                //     if(!$result){
                //         throw new \Exception(lang('fail_message'));           
                //     }
                // }else if($amount<0 && $param['upgrade_refund']!=1){
                //     OrderItemModel::create([
                //         'type' => 'manual',
                //         'order_id' => $order->id,
                //         'client_id' => $host['client_id'],
                //         'description' => lang('update_amount'),
                //         'amount' => -$amount,
                //         'create_time' => $time
                //     ]);
                //     $this->update([
                //         'amount' => 0,
                //     ], ['id' => $order->id]);
                // }
            }
            
            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
        
        $client = ClientModel::find($host['client_id']);
        # 记录日志
        active_log(lang('admin_create_upgrade_order', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$client->username.'#', '{order}'=>'#'.$order->id]), 'order', $order->id);

        // wyh 20240402 新增 支付后跳转地址
        $domain = configuration('website_url');
        $returnUrl = "{$domain}/productdetail.htm?id=".$hostId;
        $order->save([
            'return_url' => $returnUrl,
        ]);

        return ['status' => 200, 'msg' => lang('success_message'), 'data' => ['id' => $order->id,'amount'=>$amount??0] ];
    }

    /**
     * 时间 2025-04-09
     * @title 是否有未付款的按需订单
     * @desc  是否有未付款的按需订单
     * @author hh
     * @version v1
     * @param  int clientId - 用户ID require
     * @return bool
     */
    public function haveUnpaidOnDemandOrder($clientId)
    {
        $where = function($query) use ($clientId) {
            $query->where('client_id', $clientId);
            $query->where('status', 'Unpaid');
            $query->where('type', 'on_demand');
            $query->where('is_recycle', 0);
            // $query->where('create_time', '<=', time()-60);
        };

        $order = $this
                ->where($where)
                ->find();
        return !empty($order);
    }

    /**
     * 时间 2025-04-09
     * @title 是否有未付款的变更计费方式订单
     * @desc  是否有未付款的变更计费方式订单
     * @author hh
     * @version v1
     * @param  int hostId - 产品ID require
     * @return bool
     */
    public function haveUnpaidChangeBillingCycleOrder($hostId)
    {
        $where = function($query) use ($hostId) {
            $query->where('o.status', 'Unpaid');
            $query->where('o.type', 'change_billing_cycle');
            $query->where('o.is_recycle', 0);
            $query->where('oi.host_id', $hostId);
        };

        $order = $this
                ->alias('o')
                ->leftJoin('order_item oi', 'oi.order_id=o.id')
                ->where($where)
                ->find();
        return !empty($order);
    }

    /**
     * 时间 2025-04-11
     * @title 合并按需订单
     * @desc  合并按需订单
     * @author hh
     * @version v1
     * @param  array param.ids - 订单ID,数组 required
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     * @return string code - 是否支付(Paid=已支付,Unpaid=未支付)
     * @return int data.id - 合并后的订单ID
     * @return string data.amount - 合并后的订单金额
     */
    public function combineOnDemandOrder($param)
    {   
        $ids = $param['ids'];
        $clientId = get_client_id();
        
        $where = function (Query $query) use ($ids,$clientId){
            $query->where('status', 'Unpaid')
                ->where('client_id', $clientId)
                ->where('type', 'on_demand')
                ->where('id', 'IN', $ids)
                ->where('is_recycle', 0);
        };

        $orders = $this
            ->where($where)
            ->select()
            ->toArray();

        if(empty($orders)){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist') ];
        }

        $amount = $credit = 0;
        $orderItems = [];

        foreach ($orders as $order){
            $amount = bcadd($amount,$order['amount'],2);
            $credit = bcadd($credit,$order['credit'],2);

            $orderItems[] = [
                'host_id' => 0,
                'product_id' => 0,
                'type' => 'combine',
                'rel_id' => $order['id'],
                'amount' => $order['amount'],
                'description' => lang('order_combine',['{order_id}'=>'order#'.$order['id']]),
            ];
        }

        # 创建订单
        $orderData = [
            'type' => 'combine',
            'amount' => $amount,
            'credit' => $credit,
            'gateway' => $orders[0]['gateway'],
            'client_id' => $clientId,
            'items' => $orderItems
        ];

        $this->startTrans();
        try{
            $orderId = $this->createOrderBase($orderData);

            hook('after_order_create',['id'=>$orderId,'customfield'=>$param['customfield']??[]]);

            // wyh 20240402 新增 支付后跳转地址
            $domain = configuration('website_url');
            $returnUrl = "{$domain}/finance.htm";
            $this->update([
                'return_url' => $returnUrl,
            ],['id'=>$orderId]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
        $amount = $this->where('id',$orderId)->value('amount');

        if ($amount==0){
            $this->processPaidOrder($orderId);
            return ['status'=>200,'msg'=>lang('success_message'),'code'=>'Paid','data'=>['id' => $orderId, 'amount' => $amount]];
        }else{
            return ['status'=>200,'msg'=>lang('success_message'),'code'=>'Unpaid','data'=>['id' => $orderId, 'amount' => $amount]];
        }
    }

     /**
     * 时间 2024-02-18
     * @title 获取用户等级
     * @desc  获取用户等级
     * @author hh
     * @version v1
     * @param   int param.client_id - 用户ID require
     * @param   int param.product_id - 商品ID require
     * @return  int id - 用户等级ID
     * @return  string name - 用户等级名称
     * @return  int product_id - 商品ID
     * @return  float discount_percent - 等级折扣
     */
    public function getClientLevel($param)
    {
        $PluginModel = new PluginModel();
        $plugin = $PluginModel->where('status',1)->where('name','IdcsmartClientLevel')->find();
        $discount = [];
        if(!empty($plugin) && class_exists('addon\idcsmart_client_level\model\IdcsmartClientLevelClientLinkModel')){
            try{
                if(class_exists('addon\idcsmart_client_level\model\IdcsmartClientLevelProductGroupModel')){
                    $IdcsmartClientLevelModel = new \addon\idcsmart_client_level\model\IdcsmartClientLevelModel();
                    $discount = $IdcsmartClientLevelModel->clientDiscount(['client_id' => $param['client_id'], 'product_id' => $param['product_id']]);
                }else{
                    $discount = \addon\idcsmart_client_level\model\IdcsmartClientLevelClientLinkModel::alias('aiclcl')
                        ->field('aicl.id,aicl.name,aiclpl.product_id,aiclpl.discount_percent')
                        ->leftJoin('addon_idcsmart_client_level aicl', 'aiclcl.addon_idcsmart_client_level_id=aicl.id')
                        ->leftJoin('addon_idcsmart_client_level_product_link aiclpl', 'aiclpl.addon_idcsmart_client_level_id=aicl.id')
                        ->where('aiclcl.client_id', $param['client_id'])
                        ->where('aiclpl.product_id', $param['product_id'])
                        ->where('aicl.discount_status', 1)
                        ->find();
                }
            }catch(\Exception $e){
                
            }
        }
        return $discount;
    }

}
