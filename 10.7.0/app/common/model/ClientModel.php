<?php
namespace app\common\model;

use app\common\logic\SecurityVerifyLogic;
use app\home\controller\AccountController;
use think\facade\Cache;
use think\Model;
use think\db\Query;
use app\home\model\OauthModel;
use app\admin\model\AdminViewModel;
use app\admin\model\PluginModel;

/**
 * @title 用户模型
 * @desc 用户模型
 * @use app\common\model\ClientModel
 */
class ClientModel extends Model
{
	protected $name = 'client';

    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'username'        => 'string',
        'status'          => 'int',
        'email'           => 'string',
        'phone_code'      => 'int',
        'phone'           => 'string',
        'password'        => 'string',
        'credit'          => 'float',
        'company'         => 'string',
        'address'         => 'string',
        'language'        => 'string',
        'notes'           => 'string',
        'client_notes'    => 'string',
        'last_login_time' => 'int',
        'last_login_ip'   => 'string',
        'last_action_time'=> 'int',
        'create_time'     => 'int',
        'update_time'     => 'int',
        'operate_password'=> 'string',
        'notice_open'     => 'int',
        'notice_method'   => 'string',
        'country_id'      => 'int',
        'receive_sms'     => 'int',
        'receive_email'   => 'int',
        'freeze_credit'   => 'float',
        'credit_remind'   => 'int',
        'credit_remind_amount' => 'float',
        'credit_remind_send'   => 'int',
    ];

	/**
     * 时间 2022-05-10
     * @title 用户列表
     * @desc 用户列表
     * @author theworld
     * @version v1
     * @param object param.custom_field - 自定义字段,key为自定义字段名称,value为自定义字段的值
     * @param string param.type - 关键字类型,id用户ID,username姓名,phone手机号,email邮箱,company公司
     * @param string param.keywords - 关键字,搜索范围随关键字类型变化，默认搜索范围:用户ID,姓名,邮箱,手机号,公司
     * @param int param.client_id - 用户ID
     * @param array param.client_ids - 用户ID(多个)
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby id 排序(id,reg_time,host_active_num,host_num,credit,cost_price,refund_price,withdraw_price)
     * @param string param.sort - 升/降序 asc,desc
     * @param int param.show_sub_client - 显示子账户(0=隐藏,1=显示)
     * @return array list - 用户
     * @return int list[].id - 用户ID 
     * @return string list[].username - 姓名 
     * @return string list[].email - 邮箱 
     * @return int list[].phone_code - 国际电话区号 
     * @return string list[].phone - 手机号 
     * @return int list[].status - 状态;0:禁用,1:正常 
     * @return int list[].reg_time - 注册时间
     * @return string list[].country - 国家
     * @return string list[].address - 地址
     * @return string list[].company - 公司 
     * @return string list[].language - 语言
     * @return string list[].notes - 备注
     * @return string list[].credit - 余额
     * @return int list[].host_num - 产品数量 
     * @return int list[].host_active_num - 已激活产品数量
     * @return array list[].custom_field - 自定义字段
     * @return string list[].custom_field[].name - 名称
     * @return string list[].custom_field[].value - 值
     * @return string list[].cost_price - 消费金额
     * @return bool list[].certification 是否实名认证true是false否
     * @return string list[].certification_type 实名类型person个人company企业
     * @return string list[].client_level - 用户等级(显示字段有client_level返回)
     * @return string list[].client_level_color - 用户等级颜色(显示字段有client_level返回)
     * @return string list[].sale - 销售(显示字段有sale返回)
     * @return array list[].oauth - 关联的三方登录类型
     * @return int list[].mp_weixin_notice - 微信公众号关注状态(0=未关注1=已关注)
     * @return string list[].refund_price - 退款金额(显示字段有refund_price返回)
     * @return string list[].withdraw_price - 提现金额(显示字段有withdraw_price返回)
     * @return string list[].addon_client_custom_field_[id] - 用户自定义字段(显示字段有addon_client_custom_field_[id]返回,[id]为用户自定义字段ID)
     * @return int count - 用户总数
     * @return string total_credit - 总余额
     * @return string page_total_credit - 当前页总余额
     */
    public function clientList($param)
    {
        // 获取当前应用
        $app = app('http')->getName();

        // 获取当前显示字段
        $AdminViewModel = new AdminViewModel();
        $adminView = $AdminViewModel->adminViewIndex(['id' => $param['view_id'] ?? 0, 'view'=>'client']);
        $selectField = $adminView['select_field'];

        // 可排序字段
        $enableOrderBy = ['id','reg_time','host_active_num','host_num','credit','cost_price','refund_price','withdraw_price'];
        // 排序字段映射,都是client表的字段
        $orderReal = [
            'id'        => 'c.id',
            'reg_time'  => 'c.create_time',
            'credit'    => 'c.credit',
        ];

        $param['custom_field'] = $param['custom_field'] ?? [];
        $param['type'] = $param['type'] ?? '';
        $param['keywords'] = $param['keywords'] ?? '';
        $param['client_id'] = intval($param['client_id'] ?? 0);
        $param['client_ids'] = $param['client_ids'] ?? [];
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], $enableOrderBy) ? $param['orderby'] : 'id';
        $param['sort'] = isset($param['sort']) && in_array($param['sort'], ['asc','desc']) ? $param['sort'] : 'desc';
        // 选择字段转为关联数组
        $selectField = array_flip($selectField);

        $where = function (Query $query) use($param, $app) {
            // like模糊搜索较慢，待优化
            if(!empty($param['keywords']) && !empty($param['type']) && in_array($param['type'], ['id', 'username', 'phone', 'email', 'company'])){
                if($param['type']=='id'){
                    $query->where('c.id', $param['keywords']);
                }else{
                    $query->where('c.'.$param['type'], 'like', "%{$param['keywords']}%"); 
                }
            }else if(!empty($param['keywords'])){
                $query->where('c.id|c.username|c.email|c.phone|c.company', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['client_id'])){
                $query->where('c.id', $param['client_id']);
            }
            if(!empty($param['client_ids'])){
                $query->whereIn('c.id', $param['client_ids']);
            }

            hook('client_list_where_query_append', ['param'=>$param, 'app'=>$app, 'query'=>$query]);
        };
        $PluginModel = new PluginModel();
        $activePluginList = $PluginModel->activePluginList();
        $plugin = array_column($activePluginList['list'], 'id', 'name');

        // 大数据量分页优化
        // wyh 20240219优化 覆盖索引+子查询，减少回表
        // 1、处理总数问题，考虑到idcsmart_client_custom_field自定义字段数据量较小且为查询条件(缩小查询范围)，可以分别处理
        if (!empty($param['custom_field'])){
            $customFieldWhere = function (Query $query) use ($param){
                $where = [];
                foreach ($param['custom_field'] as $key => $value) {
                    if(!empty($value) && $key=='IdcsmartClientLevel_level'){
                        $where[] = "(ccf.name='{$key}' AND ccf.value='{$value}')";
                    }
                }
                if(!empty($where)){
                    $query->whereRaw(implode(' AND ', $where));
                }
            };
            $count = $this->alias('c')
                ->field('c.id')
                ->leftJoin('client_custom_field ccf', 'ccf.client_id=c.id')
                ->where($where)
                ->where($customFieldWhere)
                ->count();

            $ids = $this->alias('c')
                ->leftJoin('client_custom_field ccf', 'ccf.client_id=c.id')
                ->where($where)
                ->where($customFieldWhere)
                ->limit($param['limit'])
                ->page($param['page'])
                ->order($orderReal[$param['orderby']] ?? 'c.id', $param['sort'])
                ->column("id");

            $totalCredit = $this->alias('c')
                ->leftJoin('client_custom_field ccf', 'ccf.client_id=c.id')
                ->where($where)
                ->where($customFieldWhere)
                ->sum('c.credit');
            $totalCredit = amount_format($totalCredit);
            
            // 已经按条件查找了可用ID,条件覆盖为ID
            if(!empty($ids)){
                $where = function (Query $query) use($ids) {
                    $query->whereIn('c.id', $ids);
                };
            }else{
                $where = ['c.id'=>0];
            }
        }else{
            $count = $this->alias('c')
                ->field("c.id")
                ->where($where)
                ->count();
            $totalCredit = $this->alias('c')
                ->where($where)
                ->sum('c.credit');
            $totalCredit = amount_format($totalCredit);
        }
        
        // TODO 排序优化 'host_active_num','host_num','cost_price','refund_price','withdraw_price'
        if($param['orderby'] == 'host_active_num'){
            $hostActiveNum = $this
                ->field('c.id,count(h.id) host_active_num')
                ->alias('c')
                ->where($where)
                ->leftJoin('host h', 'c.id=h.client_id AND h.status="Active" AND h.is_delete=0 AND h.is_sub=0')
                ->limit($param['limit'])
                ->page($param['page'])
                ->order($param['orderby'], $param['sort'])
                ->group('c.id')
                ->select()
                ->toArray();
            $ids = array_column($hostActiveNum, 'id') ?? [];
            $hostActiveNum = array_column($hostActiveNum, 'host_active_num', 'id');
        }else if($param['orderby'] == 'host_num'){
            $hostNum = $this
                ->field('c.id,count(h.id) host_num')
                ->alias('c')
                ->where($where)
                ->leftJoin('host h', 'c.id=h.client_id AND h.is_delete=0 AND h.is_sub=0')
                ->limit($param['limit'])
                ->page($param['page'])
                ->order($param['orderby'], $param['sort'])
                ->group('c.id')
                ->select()
                ->toArray();
            $ids = array_column($hostNum, 'id') ?? [];
            $hostNum = array_column($hostNum, 'host_num', 'id');
        }else if($param['orderby'] == 'cost_price'){
            $costPrice = $this
                ->field('c.id,sum(t.amount) cost_price')
                ->alias('c')
                ->where($where)
                ->leftJoin('transaction t', 'c.id=t.client_id AND t.amount>0')
                ->limit($param['limit'])
                ->page($param['page'])
                ->order($param['orderby'], $param['sort'])
                ->group('c.id')
                ->select()
                ->toArray();
            $ids = array_column($costPrice, 'id') ?? [];
            $costPrice = array_column($costPrice, 'cost_price', 'id');
        }else if($param['orderby'] == 'refund_price'){
            if(isset($plugin['IdcsmartRefund']) && class_exists('addon\idcsmart_refund\model\IdcsmartRefundModel')){
                $refundPrice = $this
                    ->field('c.id,sum(air.amount) refund_price')
                    ->alias('c')
                    ->where($where)
                    ->leftJoin('addon_idcsmart_refund air', 'c.id=air.client_id AND air.status="Refund"')
                    ->limit($param['limit'])
                    ->page($param['page'])
                    ->order($param['orderby'], $param['sort'])
                    ->group('c.id')
                    ->select()
                    ->toArray();
                $ids = array_column($refundPrice, 'id') ?? [];
                $refundPrice = array_column($refundPrice, 'refund_price', 'id');
            }
        }else if($param['orderby'] == 'withdraw_price'){
            if(isset($plugin['IdcsmartWithdraw']) && class_exists('addon\idcsmart_withdraw\model\IdcsmartWithdrawModel')){
                $withdrawPrice = $this
                    ->field('c.id,sum(aiw.amount) withdraw_price')
                    ->alias('c')
                    ->where($where)
                    ->leftJoin('addon_idcsmart_withdraw aiw', 'c.id=aiw.client_id AND aiw.status=3')
                    ->limit($param['limit'])
                    ->page($param['page'])
                    ->order($param['orderby'], $param['sort'])
                    ->group('c.id')
                    ->select()
                    ->toArray();
                $ids = array_column($withdrawPrice, 'id') ?? [];
                $withdrawPrice = array_column($withdrawPrice, 'withdraw_price', 'id');
            }
        }
        if(!isset($ids)){
            $ids = $this->alias('c')
                ->where($where)
                ->limit($param['limit'])
                ->page($param['page'])
                ->order($orderReal[$param['orderby']] ?? 'c.id', $param['sort'])
                ->column("c.id");
        }
        
        if(isset($ids) && !empty($ids)){
            $app = app('http')->getName();
            if($app == 'home'){
                $language = get_client_lang();
            }else{
                $language = get_system_lang(true);
            }
            $countryField = ['en-us'=> 'nicename'];
            $countryName = $countryField[ $language ] ?? 'name_zh';

            

            $clients = $this
                ->alias('c')
                ->field('c.id,c.username,c.email,c.phone_code,c.phone,c.status,c.create_time reg_time,co.'.$countryName.' country,c.address,c.company,c.language,c.notes,c.credit')
                ->leftJoin('country co', 'co.id=c.country_id')
                ->whereIn('c.id', $ids)
                ->orderRaw("field(c.id, ".implode(',', $ids).")")
                ->select()
                ->toArray();

            $clientId = array_column($clients, 'id');

            $pageTotalCredit = amount_format(array_sum(array_column($clients, 'credit')));

            // 实名结果和以前保持一致
            $certificationHookResult = hook_one('get_certification_list');

            // 获取用户等级
            if(isset($selectField['client_level'])){
                $clientLevel = hook_one('get_client_level_list', ['client_id'=>$clientId]);
            }

            // 获取销售
            if(isset($selectField['sale'])){
                $sale = hook_one('get_sale_list', ['client_id'=>$clientId]);
            }

            // 获取三方登录状态
            if(isset($selectField['oauth'])){
                $PluginModel = new PluginModel();
                $oauthList = $PluginModel->oauthList();
                $oauthImg = array_column($oauthList['list'], 'img', 'name');

                $OauthModel = new OauthModel();
                $oauthList = $OauthModel->field('client_id,type')->whereIn('client_id', $clientId)->group('type')->order('id','desc')->select()->toArray();
                $oauth = [];
                foreach($oauthList as $k=>$v){
                    if(isset($oauthImg[$v['type']])){
                        $oauth[$v['client_id']][] = $oauthImg[$v['type']];
                    }
                }
            }

            // 获取微信公众号关注状态
            if(isset($selectField['mp_weixin_notice'])){
                $mpWeixinNotice = hook_one('get_mp_weixin_notice_list', ['client_id'=>$clientId]);
            }

            // 开发者
            $developer = hook_one('get_developer_list', ['client_id'=>$clientId]);

            // 获取子账户
            $subAccountHookResult = hook_one('get_sub_account_list', ['client_id'=>$clientId]);

            if(!isset($hostActiveNum)){
                $hostActiveNum = HostModel::field('COUNT(id) num,client_id')->where('status', 'Active')->whereIn('client_id', $clientId)->where('is_delete', 0)->where('is_sub',0)->group('client_id')->select()->toArray();
                $hostActiveNum = array_column($hostActiveNum, 'num', 'client_id');
            }
            if(!isset($hostNum)){
                $hostNum = HostModel::field('COUNT(id) num,client_id')->whereIn('client_id', $clientId)->where('is_delete', 0)->where('is_sub',0)->group('client_id')->select()->toArray();
                $hostNum = array_column($hostNum, 'num', 'client_id');
            }
            if(!isset($costPrice)){
                $costPrice = TransactionModel::field('sum(amount) cost_price,client_id')->whereIn('client_id', $clientId)->where('amount', '>', 0)->group('client_id')->select()->toArray();
                $costPrice = array_column($costPrice, 'cost_price', 'client_id');
            }
            // 退款金额
            if(isset($selectField['refund_price'])){
                if(!isset($refundPrice) && isset($plugin['IdcsmartRefund']) && class_exists('addon\idcsmart_refund\model\IdcsmartRefundModel')){
                    $refundPrice = \addon\idcsmart_refund\model\IdcsmartRefundModel::field('sum(amount) refund_price,client_id')->whereIn('client_id', $clientId)->where('status', 'Refund')->group('client_id')->select()->toArray();
                    $refundPrice = array_column($refundPrice, 'refund_price', 'client_id');
                }
            }
            // 提现金额
            if(isset($selectField['withdraw_price'])){
                if(!isset($withdrawPrice) && isset($plugin['IdcsmartWithdraw']) && class_exists('addon\idcsmart_withdraw\model\IdcsmartWithdrawModel')){
                    $withdrawPrice = \addon\idcsmart_withdraw\model\IdcsmartWithdrawModel::field('sum(amount) withdraw_price,client_id')->whereIn('client_id', $clientId)->where('status', 3)->group('client_id')->select()->toArray();
                    $withdrawPrice = array_column($withdrawPrice, 'withdraw_price', 'id');
                }   
            }

            // 获取用户自定义字段
            $clientCustomFieldIdArr = [];
            foreach($selectField as $k=>$v){
                if(stripos($k, 'addon_client_custom_field_') === 0){
                    $clientCustomFieldId = (int)str_replace('addon_client_custom_field_', '', $k);
                    $clientCustomFieldIdArr[ $clientCustomFieldId ] = 1;
                }
            }
            if(!empty($clientCustomFieldIdArr)){
                $clientCustomField = hook_one('get_client_custom_field_list', ['client_id'=>$clientId]);
            }

            $ClientCustomFieldModel = new ClientCustomFieldModel();
            $customField = $ClientCustomFieldModel->whereIn('client_id', $clientId)->select()->toArray();

            $customFieldArr = [];
            foreach ($customField as $key => $value) {
                $customFieldArr[$value['client_id']][] = ['name' => $value['name'], 'value' => $value['value']];
            }

            foreach ($clients as $key => $client) {
                $clients[$key]['host_num'] = $hostNum[$client['id']] ?? 0; // 产品数量
                $clients[$key]['host_active_num'] = $hostActiveNum[$client['id']] ?? 0; // 已激活产品数量
                $clients[$key]['custom_field'] = $customFieldArr[$client['id']] ?? []; // 自定义字段
                $clients[$key]['cost_price'] = $costPrice[$client['id']] ?? '0.00'; // 消费金额

                // 实名认证字段
                $clients[$key]['certification'] = isset($certificationHookResult[$client['id']]) && $certificationHookResult[$client['id']]?true:false;
                $clients[$key]['certification_type'] = $certificationHookResult[$client['id']]??'person';

                // 开发者
                $clients[$key]['developer_type'] = $developer[$client['id']]['type']??0;

                // 用户等级字段
                if(isset($selectField['client_level'])){
                    $clients[$key]['client_level'] = $clientLevel[ $client['id'] ]['name'] ?? '';
                    $clients[$key]['client_level_color'] = $clientLevel[ $client['id'] ]['background_color'] ?? '';
                }
                // 销售字段
                if(isset($selectField['sale'])){
                    $clients[$key]['sale'] = $sale[ $client['id'] ]['name'] ?? '';
                }
                // 微信公众号关注状态
                if(isset($selectField['oauth'])){
                    $clients[$key]['oauth'] = $oauth[ $client['id'] ] ?? [];
                }
                // 微信公众号关注状态
                if(isset($selectField['mp_weixin_notice'])){
                    $clients[$key]['mp_weixin_notice'] = $mpWeixinNotice[ $client['id'] ]['is_subscribe'] ?? 0;
                }
                // 退款金额
                if(isset($selectField['refund_price'])){
                    $clients[$key]['refund_price'] = $refundPrice[ $client['id'] ] ?? '0.00';
                }
                // 退款金额
                if(isset($selectField['withdraw_price'])){
                    $clients[$key]['withdraw_price'] = $withdrawPrice[ $client['id'] ] ?? '0.00';
                }
                // 用户自定义字段
                if(!empty($clientCustomFieldIdArr)){
                    foreach($clientCustomFieldIdArr as $kk=>$vv){
                        $clients[$key]['addon_client_custom_field_'.$kk] = $clientCustomField[$client['id']][$kk] ?? '';
                    }
                }
            }
        }else{
            $clients = [];
        }
    	return ['list' => $clients, 'count' => $count, 'total_credit' => $totalCredit, 'page_total_credit' => $pageTotalCredit ?? '0.00'];
    }

    /**
     * 时间 2022-05-10
     * @title 用户详情
     * @desc 用户详情
     * @author theworld
     * @version v1
     * @param int id - 用户ID required
     * @return int id - 用户ID 
     * @return string username - 姓名 
     * @return string email - 邮箱 
     * @return int phone_code - 国际电话区号 
     * @return string phone - 手机号 
     * @return string company - 公司 
     * @return int country_id - 国家ID 
     * @return string address - 地址 
     * @return string language - 语言 
     * @return string notes - 备注
     * @return int status - 状态;0:禁用,1:正常,后台使用 
     * @return int register_time - 注册时间,后台使用 
     * @return int last_login_time - 上次登录时间,后台使用 
     * @return string last_login_ip - 上次登录IP,后台使用
     * @return string credit - 余额 
     * @return string consume - 消费,后台使用 
     * @return string refund - 退款,后台使用 
     * @return string withdraw - 提现,后台使用 
     * @return int host_num - 产品数量,后台使用 
     * @return int host_active_num - 已激活产品数量,后台使用
     * @return array login_logs - 登录记录,后台使用
     * @return string login_logs[].ip - IP
     * @return int login_logs[].login_time - 登录时间
     * @return int login_logs[].register_time - 注册时间
     * @return boolean certification 是否实名认证,后台使用
     * @return object certification_detail 实名认证详情(当certification==true时,才会有此字段),后台使用
     * @return object certification_detail.company 企业实名认证详情
     * @return string client.certification_detail.company.card_name - 认证姓名
     * @return int client.certification_detail.company.card_type - 证件类型:1身份证、2港澳通行证、3台湾通行证、4港澳居住证、5台湾居住证、6海外护照、7中国以外驾照、8其他
     * @return string client.certification_detail.company.card_number - 证件号
     * @return string client.certification_detail.company.phone - 手机号
     * @return int client.certification_detail.company.status - 状态1已认证，2未通过，3待审核，4已提交资料
     * @return string client.certification_detail.company.company - 公司名称
     * @return string client.certification_detail.company.company_organ_code - 公司代码
     * @return string client.certification_detail.company.img_one - 身份证正面
     * @return string client.certification_detail.company.img_two - 身份证反面
     * @return string client.certification_detail.company.img_three - 营业执照
     * @return string client.certification_detail.company.auth_fail - 失败原因
     * @return object certification_detail.person 个人实名认证详情
     * @return string client.certification_detail.person.card_name - 认证姓名
     * @return int client.certification_detail.person.card_type - 证件类型:1身份证、2港澳通行证、3台湾通行证、4港澳居住证、5台湾居住证、6海外护照、7中国以外驾照、8其他
     * @return string client.certification_detail.person.card_number - 证件号
     * @return string client.certification_detail.person.phone - 手机号
     * @return int client.certification_detail.person.status - 状态1已认证，2未通过，3待审核，4已提交资料
     * @return string client.certification_detail.person.img_one - 身份证正面
     * @return string client.certification_detail.person.img_two - 身份证反面
     * @return string client.certification_detail.person.img_three - 营业执照
     * @return string client.certification_detail.person.auth_fail - 失败原因
     * @return object client.customfield - 自定义字段
     * @return string client.currency_prefix - 货币符号,前台使用
     * @return array client.oauth - 三方登录,前台使用
     * @return string client.oauth[].name - 标识
     * @return string client.oauth[].title  - 名称
     * @return string client.oauth[].url  - 跳转链接 
     * @return bool client.oauth[].link  - 是否绑定true是false否
     * @return bool client.set_operate_password - 是否设置了操作密码
     * @return int client.notice_open  - 是否接收短信、邮件通知，1是默认0否
     * @return string client.notice_method  - 通知方式：all所有，email邮件，sms短信
     * @return int client.receive_sms - 接收短信(0=关闭1=开启)
     * @return int client.receive_email - 接收邮件(0=关闭1=开启)
     * @return int client.mp_weixin_notice - 微信公众号关注状态(0=未关注1=已关注)
     * @return int client.credit_remind - 余额提醒：0关闭默认，1开启
     * @return float client.credit_remind_amount - 阈值
     */
    public function indexClient($id)
    {
        // 获取当前应用
        $app = app('http')->getName();

        $client = $this->field('id,username,email,phone_code,phone,company,country_id,address,language,notes,client_notes,status,create_time register_time,last_login_time,last_login_ip,credit,operate_password,notice_open,notice_method,receive_sms,receive_email,freeze_credit,credit_remind,credit_remind_amount')->find($id);
        if (empty($client)){
            return (object)[]; // 转换为对象
        }

        // 插件用户限制,限制可查看的用户数据
        $res = hook('plugin_check_client_limit', ['client_id' => $client['id']]);
        foreach ($res as $value){
            if (isset($value['status']) && $value['status']==400){
                return (object)[]; // 转换为对象
            }
        }
        $client['total_credit'] = amount_format($client['credit'] + $client['freeze_credit']); // 账户余额
        $client['credit'] = amount_format($client['credit']); // 余额
        $client['freeze_credit'] = amount_format($client['freeze_credit']); // 冻结余额
        $client['country_id'] = $client['country_id'] ? $client['country_id'] : '';
        if($app=='admin'){
            $client['consume'] = amount_format(TransactionModel::where('client_id', $id)->where('amount', '>', 0)->sum('amount')); // 消费
            $client['refund'] = amount_format(TransactionModel::where('client_id', $id)->where('amount', '<', 0)->sum('amount')); // 退款
            $client['withdraw'] = amount_format(-ClientCreditModel::where('client_id', $id)->where('type', 'Withdraw')->sum('amount')); // 提现
            $client['host_num'] = HostModel::where('client_id', $id)->where('is_delete', 0)->where('is_sub',0)->count();  // 产品数量
            $client['host_active_num'] = HostModel::where('status', 'Active')->where('client_id', $id)->where('is_delete', 0)->where('is_sub',0)->count(); // 已激活产品数量
            $client['login_logs'] = SystemLogModel::field('ip,create_time login_time')->where('type', 'login')->where('user_type', 'client')->where('user_id', $id)->limit(5)->order('id', 'desc')->select()->toArray();

            $client['certification'] = check_certification($client['id']);

            if ($client['certification']){
                $client['certification_detail'] = hook_one('certification_detail',['client_id'=>$id]);
            }

            $client['customfield'] = [];
            $hookRes = hook('admin_client_index', ['id'=>$id]);
            foreach($hookRes as $v){
                if(isset($v['status']) && $v['status'] == 200){
                    $client['customfield'] = array_merge($client['customfield'], $v['data'] ?? []);
                }
            }

            $PluginModel = new PluginModel();
            $oauthList = $PluginModel->oauthList();

            $OauthModel = new OauthModel();
            $oauth = $OauthModel->where('client_id', $id)->column('type');
            $oauthImg = [];
            foreach($oauthList['list'] as $k=>$v){
                if(in_array($v['name'], $oauth)){
                    $oauthImg[] = $v['img'];
                }else if(isset($v['img_unbound'])){
                    $oauthImg[] = $v['img_unbound'];
                }
            }

            $client['oauth'] = $oauthImg;

            $mpWeixinNotice = hook_one('get_mp_weixin_notice_list', ['client_id'=>[$id]]);
            $client['mp_weixin_notice'] = $mpWeixinNotice[ $id ]['is_subscribe'] ?? 0;

            unset($client['client_notes']);
        }else if($app=='home'){
            $client['notes'] = $client['client_notes'];
            $client['customfield'] = [
                'is_sub_account' => get_client_id()!=get_client_id(false) ? 1 : 0
            ];
            $client['currency_prefix'] = configuration("currency_prefix");
            // 前台接口去除字段
            unset($client['client_notes'], $client['last_login_time'], $client['last_login_ip'], $client['receive_sms'], $client['receive_email']);

            $OauthModel = new OauthModel();
            $oauthList = $OauthModel->clientOauth();

            $client['oauth'] = $oauthList['list'] ?? [];

            $hookRes = hook('home_client_index', ['id'=>$id]);
            foreach($hookRes as $v){
                if(isset($v['status']) && $v['status'] == 200){
                    $client['customfield'] = array_merge($client['customfield'], $v['data'] ?? []);
                }
            }
        }
        $client['set_operate_password'] = !empty($client['operate_password']);
        unset($client['operate_password']);

        hook('after_client_index', ['id' => $id]);
        
        return $client;
    }

    /**
     * 时间 2022-10-13
     * @title 会员中心首页
     * @desc 会员中心首页
     * @author theworld
     * @version v1
     * @param int id - 用户ID required
     * @return string username - 姓名 
     * @return string email - 邮箱 
     * @return int phone_code - 国际电话区号 
     * @return string phone - 手机号 
     * @return string credit - 余额 
     * @return string host_num - 产品数量 
     * @return string host_active_num - 激活产品数量
     * @return int expiring_count - 即将到期产品数量
     * @return string unpaid_order - 未支付订单 
     * @return string consume - 总消费金额 
     * @return string this_month_consume - 本月消费
     * @return string this_month_consume_percent - 本月消费对比上月增长百分比 
     */
    public function indexClient2($id)
    {
        // 获取当前应用
        $app = app('http')->getName();

        $client = $this->field('id,username,email,phone_code,phone,credit,credit_remind,credit_remind_amount')->find($id);
        if (empty($client)){
            return (object)[]; // 转换为对象
        }

        $client['credit'] = amount_format($client['credit']); // 余额
        $where = [];
        $where[] = ['h.is_sub', '=', 0];
        $where[] = ['h.is_delete', '=', 0];
        $where[] = ['h.client_id', '=', $id];
        $where[] = ['h.status', '<>', 'Cancelled'];
        // 前台是否展示已删除产品
        $homeShowDeletedHost = configuration('home_show_deleted_host');
        if($homeShowDeletedHost!=1){
            $where[] = ['h.status', '<>', 'Deleted'];
        }
        $res = hook('get_client_host_id', ['client_id' => get_client_id(false)]);
        $res = array_values(array_filter($res ?? []));
        foreach ($res as $key => $value) {
            if(isset($value['status']) && $value['status']==200){
                $hostId = $value['data']['host'];
            }
        }
        if(isset($hostId) && !empty($hostId)){
            $where[] = ['h.id', 'in', $hostId];
        }

        $HostModel = new HostModel();
        $client['host_num'] = $HostModel->allCount($where);
        $client['host_active_num'] = $HostModel->usingCount($where);
        $client['expiring_count'] = $HostModel->expiringCount($where);
        $client['unpaid_order'] = OrderModel::where('client_id', $id)->whereIn('status', ['Unpaid','WaitUpload','WaitReview','ReviewFail'])->where('is_recycle', 0)->count(); // 未支付订单
        $client['consume'] = amount_format(TransactionModel::where('client_id', $id)->where('amount', '>', 0)->sum('amount')); // 消费

        // 获取本月消费
        $start = mktime(0,0,0,date("m"),1,date("Y"));
        $end = time();
        $client['this_month_consume'] = amount_format(TransactionModel::where('client_id', $id)->where('amount', '>', 0)->where('create_time', '>=', $start)->where('create_time', '<', $end)->sum('amount')); // 本月消费

        # 获取上月销售额， 截止到上月的昨天同日期
        if(date("m")==1){
            $start = mktime(0,0,0,12,1,date("Y")-1);
        }else{
            $start = mktime(0,0,0,date("m")-1,1,date("Y"));
        }
        $end = mktime(0,0,0,date("m"),1,date("Y"));
        
        $prevMonthAmount = TransactionModel::where('client_id', $id)->where('amount', '>', 0)->where('create_time', '>=', $start)->where('create_time', '<', $end)->sum('amount');

        $thisMonthAmountPercent = $prevMonthAmount>0 ? bcmul(($client['this_month_consume']-$prevMonthAmount)/$prevMonthAmount, 100, 1) : 100;

        $client['this_month_consume_percent'] = $thisMonthAmountPercent;

        $client['customfield'] = [];
        $hookRes = hook('home_client_index', ['id'=>$id]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 200){
                $client['customfield'] = array_merge($client['customfield'], $v['data'] ?? []);
            }
        }
        
        return $client;
    }

    /**
     * 时间 2022-05-10
     * @title 新建用户
     * @desc 新建用户
     * @author theworld
     * @version v1
     * @param string param.username - 姓名
     * @param string param.email - 邮箱 邮箱手机号两者至少输入一个
     * @param int param.phone_code - 国际电话区号 邮箱手机号两者至少输入一个
     * @param string param.phone - 手机号 邮箱手机号两者至少输入一个
     * @param string param.password - 密码 required
     * @param string param.repassword - 重复密码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return object data - 返回数据
     * @return int data.id - 用户ID,成功时返回
     */
    public function createClient($param)
    {
	    $this->startTrans();
		try {
	    	$client = $this->create([
	    		'username' => (isset($param['username']) && !empty($param['username']))?$param['username']:((isset($param['email']) && !empty($param['email']))?explode('@',$param['email'])[0]:((isset($param['phone']) && !empty($param['phone']))?$param['phone']:'')),
	    		'email' => $param['email']  ?? '',
	    		'phone_code' => $param['phone_code'] ?? 44,
	    		'phone' => $param['phone'] ?? '',
	    		'password' => idcsmart_password($param['password']), // 密码加密
                'language' => configuration('lang_home')??'zh-cn',
                'create_time' => time()
	    	]);

            # 记录日志
            active_log(lang('admin_create_new_user', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$param['username'].'#']), 'client', $client->id);

	        $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang('create_fail')];
		}

		hook('after_client_register',['id'=>$client->id,'customfield'=>$param['customfield']??[]]);

    	return ['status' => 200, 'msg' => lang('create_success'), 'data' => ['id' => $client->id]];
    }

    /**
     * 时间 2022-05-10
     * @title 修改用户
     * @desc 修改用户
     * @author theworld
     * @version v1
     * @param int param.id - 用户ID required
     * @param string param.username - 姓名
     * @param string param.email - 邮箱 邮箱手机号两者至少输入一个
     * @param int param.phone_code - 国际电话区号 邮箱手机号两者至少输入一个
     * @param string param.phone - 手机号 邮箱手机号两者至少输入一个
     * @param string param.company - 公司
     * @param int param.country_id - 国家ID
     * @param string param.address - 地址
     * @param string param.language - 语言
     * @param string param.notes - 备注
     * @param string param.password - 密码 为空代表不修改
     * @param string param.operate_password - 操作密码 后台时可以修改,为空代表不修改
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateClient($param)
    {
        // 获取当前应用
        $app = app('http')->getName();
        if($app=='home'){
            $param['id'] = get_client_id(false);
        }


        // 验证用户ID
    	$client = $this->find($param['id']);
        if($app=='home'){
            if (empty($client)){
                return ['status'=>400, 'msg'=>lang('fail_message')];
            }
            $param['email'] = $client['email'];
            $param['phone_code'] = $client['phone_code'];
            $param['phone'] = $client['phone'];
            $param['client_notes'] = $param['notes'] ?? '';
            $param['notes'] = $client['notes'];
            $param['password'] = '';  // 前台不能直接修改
            $param['operate_password'] = ''; // 前台不能直接修改
            $param['username'] = $param['username'] ?? '';
            $param['company'] = $param['company'] ?? '';
            $param['country_id'] = $param['country_id'] ?? 0;
            $param['address'] = $param['address'] ?? '';
            $param['language'] = $param['language'] ?? '';
        }else{
            if (empty($client)){
                return ['status'=>400, 'msg'=>lang('client_is_not_exist')];
            }
            $param['username'] = $param['username'] ?? '';
            $param['company'] = $param['company'] ?? '';
            $param['country_id'] = $param['country_id'] ?? 0;
            $param['address'] = $param['address'] ?? '';
            $param['language'] = $param['language'] ?? '';
            $param['notes'] = $param['notes'] ?? '';
            $param['client_notes'] = $client['client_notes'];
        }
        $param['password'] = $param['password'] ?? '';
        $param['operate_password'] = $param['operate_password'] ?? '';
        // wyh 20240605 新增通知开关
        $param['notice_open'] = $param['notice_open']??$client['notice_open'];
        $param['notice_method'] = $param['notice_method']??$client['notice_method'];

        if(isset($param['language'])){
            $langAdmin = lang_list('admin');
            $langHome = lang_list('home');
            $lang = array_merge(array_column($langAdmin, 'display_lang'), array_column($langHome, 'display_lang'));
            if(!in_array($param['language'], $lang)){
                return ['status'=>400, 'msg'=>lang('param_error')];
            }
        }

        if(!empty($param['country_id'])){
            $country = CountryModel::where('id', $param['country_id'])->find();
            if(empty($country)){
                return ['status'=>400, 'msg'=>lang('param_error')];
            }
        }

        if($app=='admin'){
            # 日志详情
            $description = [];
            if ($client['username'] != $param['username']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_username').$client['username'], '{new}'=>$param['username']]);
            }
            if ($client['email'] != $param['email']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_email').$client['email'], '{new}'=>$param['email']]);
            }
            if ($client['phone_code'] != $param['phone_code']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_phone_code').$client['phone_code'], '{new}'=>$param['phone_code']]);
            }
            if ($client['phone'] != $param['phone']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_phone').$client['phone'], '{new}'=>$param['phone']]);
            }
            if ($client['company'] != $param['company']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_company').$client['company'], '{new}'=>$param['company']]);
            }
            if ($client['country_id'] != $param['country_id']){
                $language = get_system_lang(true);
                $countryField = ['en-us'=> 'nicename'];
                $countryName = $countryField[ $language ] ?? 'name_zh';

                $old = CountryModel::where('id', $client['country_id'])->value($countryName);
                $new = CountryModel::where('id', $param['country_id'])->value($countryName);
                $description[] = lang('old_to_new',['{old}'=>lang('client_country').$old, '{new}'=>$new]);
            }
            if ($client['address'] != $param['address']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_address').$client['address'], '{new}'=>$param['address']]);
            }
            if ($client['language'] != $param['language']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_language').$client['language'], '{new}'=>$param['language']]);
            }
            if ($client['notes'] != $param['notes']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_notes').$client['notes'], '{new}'=>$param['notes']]);
            }
            if(!empty($param['password'])){
                $description[] = lang('log_change_password');
            }
            if(!empty($param['operate_password'])){
                $description[] = lang('log_change_operate_password');
            }
            $description = implode(',', $description);
        }else if($app=='home'){
            # 日志详情
            $description = [];
            if ($client['username'] != $param['username']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_username').$client['username'], '{new}'=>$param['username']]);
            }
            if ($client['company'] != $param['company']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_company').$client['company'], '{new}'=>$param['company']]);
            }
            if ($client['country_id'] != $param['country_id']){
                $language = get_client_lang();
                $countryField = ['en-us'=> 'nicename'];
                $countryName = $countryField[ $language ] ?? 'name_zh';

                $old = CountryModel::where('id', $client['country_id'])->value($countryName);
                $new = CountryModel::where('id', $param['country_id'])->value($countryName);
                $description[] = lang('old_to_new',['{old}'=>lang('client_country').$old, '{new}'=>$new]);
            }
            if ($client['address'] != $param['address']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_address').$client['address'], '{new}'=>$param['address']]);
            }
            if ($client['language'] != $param['language']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_language').$client['language'], '{new}'=>$param['language']]);
            }
            if ($client['client_notes'] != $param['client_notes']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_notes').$client['client_notes'], '{new}'=>$param['client_notes']]);
            }
            if ($client['notice_open'] != $param['notice_open']){
                if ($client['notice_open']==1){
                    $desNoticeOpen = lang('notice_open_1');
                }else{
                    $desNoticeOpen = lang('notice_open_0');
                }
                if ($param['notice_open']==1){
                    $desNoticeOpen2 = lang('notice_open_1');
                }else{
                    $desNoticeOpen2 = lang('notice_open_0');
                }
                $description[] = lang('old_to_new',['{old}'=>lang('notice_open').$desNoticeOpen, '{new}'=>$desNoticeOpen2]);
            }
            if ($client['notice_method'] != $param['notice_method']){
                $description[] = lang('old_to_new',['{old}'=>lang('notice_method').$client['notice_method'], '{new}'=>$param['notice_method']]);
            }
            $description = implode(',', $description);
        }
    	
        $hookRes = hook('before_client_edit',['id'=>$param['id'],'customfield'=>$param['customfield']??[]]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }

    	$this->startTrans();
		try {
            $this->update([
                'username' => $param['username'] ?? '',
                'email' => $param['email'] ?? '',
                'phone_code' => $param['phone_code'] ?? 44,
                'phone' => $param['phone'] ?? '',
                'password' => !empty($param['password']) ? idcsmart_password($param['password']) : $client['password'], // 密码加密
                'company' => $param['company'] ?? '',
                'country_id' => $param['country_id'] ?? 0,
                'address' => $param['address'] ?? '',
                'language' => $param['language'] ?? '',
                'notes' => $param['notes'] ?? '',
                'client_notes' => $param['client_notes'] ?? '',
                'update_time' => time(),
                'operate_password' => $param['operate_password'] !== '' ? idcsmart_password($param['operate_password']) : $client['operate_password'],
                'notice_open' => $param['notice_open'],
                'notice_method' => $param['notice_method'],
            ], ['id' => $param['id']]);

            if($param['language']!=$client['language']){
                lang_plugins('success_message', [], true);
            }

            if($app=='admin' && !empty($description)){
                # 记录日志
                active_log(lang('admin_modify_user_profile', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$param['username'].'#', '{description}'=>$description]), 'client', $client->id);
            }else if($app=='home' && !empty($description)){
                # 记录日志
                active_log(lang('modify_profile', ['{client}'=>'client#'.$client->id.'#'.request()->client_name.'#', '{description}'=>$description]), 'client', $client->id);
            }

		    $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang('update_fail')];
		}

		hook('after_client_edit',['id'=>$param['id'],'customfield'=>$param['customfield']??[]]);

    	return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-05-10
     * @title 删除用户
     * @desc 删除用户
     * @author theworld
     * @version v1
     * @param int param.id - 用户ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteClient($param)
    {
        $id = $param['id']??0;
        // 验证用户ID
    	$client = $this->find($id);
    	if (empty($client)){
            return ['status'=>400, 'msg'=>lang('client_is_not_exist')];
        }
    	$this->startTrans();
		try {
            # 记录日志
            active_log(lang('admin_delete_user', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$client['username'].'#']), 'client', $client->id);

			$this->destroy($id);
            // 删除用户余额记录
            ClientCreditModel::destroy(function($query) use($id){
                $query->where('client_id', $id);
            });
            // 删除用户订单
            OrderModel::destroy(function($query) use($id){
                $query->where('client_id', $id);
            });
            // 删除用户订单子项
            OrderItemModel::destroy(function($query) use($id){
                $query->where('client_id', $id);
            });
            // 删除用户流水
            TransactionModel::destroy(function($query) use($id){
                $query->where('client_id', $id);
            });
            // 删除用户产品
            HostModel::destroy(function($query) use($id){
                $query->where('client_id', $id);
            });
            OauthModel::destroy(function($query) use($id){
                $query->where('client_id', $id);
            });
            ClientTrafficWarningModel::destroy(function($query) use($id){
                $query->where('client_id', $id);
            });
		    $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang('delete_fail')];
		}

        hook('after_client_delete',['id'=>$id]);

    	return ['status' => 200, 'msg' => lang('delete_success')];
    }

    /**
     * 时间 2022-5-26
     * @title 用户状态切换
     * @desc 用户状态切换
     * @author theworld
     * @version v1
     * @param int param.id - 用户ID required
     * @param int param.status 1 状态:0禁用,1启用 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateClientStatus($param)
    {
        // 验证用户ID
        $client = $this->find($param['id']);
        if (empty($client)){
            return ['status' => 400, 'msg' => lang('client_is_not_exist')];
        }

        $status = intval($param['status']);

        if ($client['status'] == $status){
            return ['status' => 400, 'msg' => lang('cannot_repeat_opreate')];
        }
        $this->startTrans();
        try{
            $this->update([
                'status' => $status,
                'update_time' => time(),
            ],['id' => $param['id']]);

            # 记录日志
            if($status==1){
                active_log(lang('admin_enable_user', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$client['username'].'#']), 'client', $client->id);
            }else{
                active_log(lang('admin_disable_user', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$client['username'].'#']), 'client', $client->id);
            }
            

            $this->commit();
        }catch (\Exception $e){
            // 回滚事务
            $this->rollback();
            if ($status == 0){
                return ['status' => 400, 'msg' => lang('disable_fail')];
            }else{
                return ['status' => 400, 'msg' => lang('enable_fail')];
            }
        }

        if ($status == 0){

            system_notice([
                'name'  => 'disable_client',
                'email_description' => lang('disable_client_send_email'),
                'sms_description' => lang('disable_client_send_sms'),
                'task_data' => [
                    'client_id' => $client['id'],
                ],
            ]);

            return ['status' => 200, 'msg' => lang('disable_success')];
        }else{
            return ['status' => 200, 'msg' => lang('enable_success')];
        }

    }

    /**
     * 时间 2022-5-26
     * @title 修改用户接收短信
     * @desc 修改用户接收短信
     * @author theworld
     * @version v1
     * @param int param.id - 用户ID required
     * @param int param.receive_sms 1 接收短信:0禁用,1启用 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateClientReceiveSms($param)
    {
        // 验证用户ID
        $client = $this->find($param['id']);
        if (empty($client)){
            return ['status' => 400, 'msg' => lang('client_is_not_exist')];
        }

        $receiveSms = intval($param['receive_sms']);

        if ($client['receive_sms'] == $receiveSms){
            return ['status' => 400, 'msg' => lang('cannot_repeat_opreate')];
        }
        $this->startTrans();
        try{
            $this->update([
                'receive_sms' => $receiveSms,
                'update_time' => time(),
            ],['id' => $param['id']]);

            # 记录日志
            if($receiveSms==1){
                active_log(lang('admin_enable_user_receive_sms', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$client['username'].'#']), 'client', $client->id);
            }else{
                active_log(lang('admin_disable_user_receive_sms', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$client['username'].'#']), 'client', $client->id);
            }
            

            $this->commit();
        }catch (\Exception $e){
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }

        return ['status' => 200, 'msg' => lang('update_success')];

    }

    /**
     * 时间 2022-5-26
     * @title 修改用户接收邮件
     * @desc 修改用户接收邮件
     * @author theworld
     * @version v1
     * @param int param.id - 用户ID required
     * @param int param.receive_email 1 接收邮件:0禁用,1启用 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateClientReceiveEmail($param)
    {
        // 验证用户ID
        $client = $this->find($param['id']);
        if (empty($client)){
            return ['status' => 400, 'msg' => lang('client_is_not_exist')];
        }

        $receiveEmail = intval($param['receive_email']);

        if ($client['receive_email'] == $receiveEmail){
            return ['status' => 400, 'msg' => lang('cannot_repeat_opreate')];
        }
        $this->startTrans();
        try{
            $this->update([
                'receive_email' => $receiveEmail,
                'update_time' => time(),
            ],['id' => $param['id']]);

            # 记录日志
            if($receiveEmail==1){
                active_log(lang('admin_enable_user_receive_email', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$client['username'].'#']), 'client', $client->id);
            }else{
                active_log(lang('admin_disable_user_receive_email', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$client['username'].'#']), 'client', $client->id);
            }
            

            $this->commit();
        }catch (\Exception $e){
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }

        return ['status' => 200, 'msg' => lang('update_success')];

    }

    /**
     * 时间 2022-05-16
     * @title 搜索用户
     * @desc 搜索用户
     * @author theworld
     * @version v1
     * @param string param.keywords - 关键字,搜索范围:用户ID,姓名,邮箱,手机号
     * @param string type - 搜索类型:global全局搜索
     * @return array list - 用户
     * @return int list[].id - 用户ID 
     * @return string list[].username - 姓名
     * @return string list[].company - 公司
     * @return string list[].email - 邮箱
     * @return string list[].phone_code - 国际电话区号
     * @return string list[].phone - 手机号
     */
    public function searchClient($param, $type = '')
    {
        $param['keywords'] = $param['keywords'] ?? '';
        $param['client_id'] = intval($param['client_id'] ?? 0);

        if($type=='global'){
            $resultHook = hook('before_search_client', ['keywords' => $param['keywords']]);
            $resultHook = array_values(array_filter($resultHook ?? []));
            $clientIdArr = [];
            foreach ($resultHook as $key => $value) {
                if(isset($value['client_id']) && !empty($value['client_id']) && is_array($value['client_id'])){
                    $clientIdArr = array_merge($clientIdArr, $value['client_id']);
                }
            }
            $clientIdArr = array_unique($clientIdArr);
            //全局搜索
            $clients = $this->field('id,username,company,email,phone_code,phone')
                ->where(function ($query) use($param, $clientIdArr) {
                    if(!empty($param['keywords'])){
                        $query->where('username|company|email|phone|notes|client_notes', 'like', "%{$param['keywords']}%");
                    }
                    if(!empty($param['client_id'])){
                        $query->where('id', 'like', $param['client_id']);
                    }
                })
                ->select()
                ->toArray();
            if(!empty($clientIdArr)){
                $clientIdArr = array_merge($clientIdArr, array_column($clients, 'id'));
                $clientIdArr = array_unique($clientIdArr);
                $clients = $this->field('id,username,company,email,phone_code,phone')
                    ->whereIn('id', $clientIdArr)
                    ->select()
                    ->toArray();
            }
        }else{
            //搜索20条数据
            $clients = $this->field('id,username')
                ->where(function ($query) use($param) {
                    if(!empty($param['keywords'])){
                        $query->where('id|username|email|phone', 'like', "%{$param['keywords']}%");
                    }
                    if(!empty($param['client_id'])){
                        $query->where('id', 'like', $param['client_id']);
                    }
                })
                ->limit(20)
                ->select()
                ->toArray();
        }

        return ['list' => $clients];
    }

    /**
     * 时间 2022-05-19
     * @title 验证原手机
     * @desc 验证原手机
     * @author theworld
     * @version v1
     * @param string param.code - 验证码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function verifyOldPhone($param)
    {
        // 获取登录用户ID
        $id = get_client_id(false);
        $client = $this->find($id);
        if (empty($client)){
            return ['status'=>400, 'msg'=>lang('fail_message')];
        }
        if(empty($client['phone'])){
            return ['status'=>400, 'msg'=>lang('user_not_bind_phone')];
        }

        // 验证码验证
        $code = Cache::get('verification_code_verify_'.$client['phone_code'].'_'.$client['phone']);
        if(empty($code)){
            return ['status' => 400, 'msg' => lang('please_get_verification_code')];
        }

        if($code!=$param['code']){
            return ['status' => 400, 'msg' => lang('verification_code_error')];
        }
        Cache::delete('verification_code_verify_'.$client['phone_code'].'_'.$client['phone']); // 验证通过,删除验证码缓存
        Cache::set('verification_code_verify_'.$client['phone_code'].'_'.$client['phone'].'_success', 1, 300); // 验证成功结果保存5分钟

        return ['status' => 200, 'msg' => lang('success_message')];
    }


    /**
     * 时间 2022-05-19
     * @title 修改手机
     * @desc 修改手机
     * @author theworld
     * @version v1
     * @param int param.phone_code - 国际电话区号 required
     * @param string param.phone - 手机号 required
     * @param string param.code - 验证码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateClientPhone($param)
    {
        // 获取登录用户ID
        $id = get_client_id(false);
        $client = $this->find($id);
        if (empty($client)){
            return ['status'=>400, 'msg'=>lang('fail_message')];
        }

        if(!empty($client['phone']) && in_array('phone', array_filter(explode(',', configuration('prohibit_user_information_changes'))))){
            return ['status'=>400, 'msg'=>lang('cannot_update_phone')];
        }

        // 如果已有手机则需要验证原手机
        if(!empty($client['phone'])){
            $verifyResult = Cache::get('verification_code_verify_'.$client['phone_code'].'_'.$client['phone'].'_success'); // 获取验证原手机结果
            if(empty($verifyResult)){
                return ['status'=>400, 'msg'=>lang('please_verify_old_phone')];
            }
        }

        // 验证码验证
        $code = Cache::get('verification_code_update_'.$param['phone_code'].'_'.$param['phone']);
        if(empty($code)){
            return ['status' => 400, 'msg' => lang('please_get_verification_code')];
        }

        if($code!=$param['code']){
            return ['status' => 400, 'msg' => lang('verification_code_error')];
        }
        Cache::delete('verification_code_update_'.$param['phone_code'].'_'.$param['phone']); // 验证通过,删除验证码缓存

        // 修改手机
        $this->startTrans();
        try {
            $this->update([
                'phone_code' => $param['phone_code'],
                'phone' => $param['phone'],
                'update_time' => time()
            ], ['id' => $id]);
			
            # 记录日志
            if(!empty($client['phone'])){
                active_log(lang('change_bound_mobile', ['{client}'=>'client#'.$id.'#'.request()->client_name.'#', '{phone}'=>$param['phone'], '{old_phone}'=>$client['phone']]), 'client', $id);
            }else{
                active_log(lang('bound_mobile', ['{client}'=>'client#'.$id.'#'.request()->client_name.'#', '{phone}'=>$param['phone']]), 'client', $id);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }

        system_notice([
            'name'                  => 'client_change_phone',
            'sms_description'       => lang('client_change_phone_send_sms'),
            'task_data' => [
                'phone_code' => $param['phone_code'],
                'phone'      => $param['phone'],
                'client_id'  => $client['id'],
            ],
        ]);

        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-05-19
     * @title 验证原邮箱
     * @desc 验证原邮箱
     * @author theworld
     * @version v1
     * @param string param.code - 验证码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function verifyOldEmail($param)
    {
        // 获取登录用户ID
        $id = get_client_id(false);
        $client = $this->find($id);
        if (empty($client)){
            return ['status'=>400, 'msg'=>lang('fail_message')];
        }
        if(empty($client['email'])){
            return ['status'=>400, 'msg'=>lang('user_not_bind_email')];
        }

        // 验证码验证
        $code = Cache::get('verification_code_verify_'.$client['email']);
        if(empty($code)){
            return ['status' => 400, 'msg' => lang('please_get_verification_code')];
        }

        if($code!=$param['code']){
            return ['status' => 400, 'msg' => lang('verification_code_error')];
        }

        Cache::delete('verification_code_verify_'.$client['email']); // 验证通过,删除验证码缓存
        Cache::set('verification_code_verify_'.$client['email'].'_success', 1, 300); // 验证成功结果保存5分钟

        return ['status' => 200, 'msg' => lang('success_message')];
    }


    /**
     * 时间 2022-05-19
     * @title 修改邮箱
     * @desc 修改邮箱
     * @author theworld
     * @version v1
     * @param string param.email - 邮箱 required
     * @param string param.code - 验证码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateClientEmail($param)
    {
        // 获取登录用户ID
        $id = get_client_id(false);
        $client = $this->find($id);
        if (empty($client)){
            return ['status'=>400, 'msg'=>lang('fail_message')];
        }

        if(!empty($client['email']) && in_array('email', array_filter(explode(',', configuration('prohibit_user_information_changes'))))){
            return ['status'=>400, 'msg'=>lang('cannot_update_email')];
        }

        # 验证邮箱后缀
        if (configuration('limit_email_suffix')){
            $emailSuffix = configuration('email_suffix');
            $emailSuffix = explode(',', $emailSuffix);
            if(!in_array(substr($param['email'], strpos($param['email'],'@')), $emailSuffix)){
                return ['status'=>400,'msg'=>lang('limit_email_suffix_cannot_register')];
            }
        }

        // 如果已有邮箱则需要验证原邮箱
        if(!empty($client['email'])){
            $verifyResult = Cache::get('verification_code_verify_'.$client['email'].'_success'); // 获取验证原邮箱结果
            if(empty($verifyResult)){
                return ['status'=>400, 'msg'=>lang('please_verify_old_email')];
            }
        }

        // 验证码验证
        $code = Cache::get('verification_code_update_'.$param['email']);
        if(empty($code)){
            return ['status' => 400, 'msg' => lang('please_get_verification_code')];
        }

        if($code!=$param['code']){
            return ['status' => 400, 'msg' => lang('verification_code_error')];
        }
        Cache::delete('verification_code_update_'.$param['email']); // 验证通过,删除验证码缓存

        // 修改邮箱
        $this->startTrans();
        try {
            $this->update([
                'email' => $param['email'],
                'update_time' => time()
            ], ['id' => $id]);
			
            # 记录日志
            if(!empty($client['phone'])){
                active_log(lang('change_bound_email', ['{client}'=>'client#'.$id.'#'.request()->client_name.'#', '{email}'=>$param['email'], '{old_email}'=>$client['email']]), 'client', $id);
            }else{
                active_log(lang('bound_email', ['{client}'=>'client#'.$id.'#'.request()->client_name.'#', '{email}'=>$param['email']]), 'client', $id);
            }
            

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }

        system_notice([
            'name'                  => 'client_change_email',
            'email_description'     => lang('client_change_email_send_mail'),
            'task_data' => [
                'client_id' => $client['id'],
                'email'     => $param['email'],
            ],
        ]);

        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-05-19
     * @title 修改密码
     * @desc 修改密码
     * @author theworld
     * @version v1
     * @param string param.old_password - 旧密码 required
     * @param string param.new_password - 新密码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateClientPassword($param)
    {
        // 获取登录用户ID
        $id = get_client_id(false);
        $client = $this->find($id);
        if (empty($client)){
            return ['status'=>400, 'msg'=>lang('fail_message')];
        }

        if(in_array('password', array_filter(explode(',', configuration('prohibit_user_information_changes'))))){
            return ['status'=>400, 'msg'=>lang('cannot_update_passowrd')];
        }

        // 验证密码
        if(!idcsmart_password_compare($param['old_password'], $client['password'])){
            return ['status'=>400, 'msg'=>lang('old_password_error')];
        }

        // 安全验证处理
        $securityResult = handle_security_verify($id, $param, 'update_password');
        if ($securityResult['status'] !== 200) {
            return $securityResult;
        }

        // 修改密码
        $this->startTrans();
        try {
            $this->update([
                'password' => idcsmart_password($param['new_password']), // 密码加密
                'update_time' => time()
            ], ['id' => $id]);

            Cache::set('home_update_password_'.$id,time(),3600*24*7); # wyh增 修改密码 退出登录 7天未操作接口,就可以不退出
			
            # 记录日志
            active_log(lang('change_password', ['{client}'=>'client#'.$id.'#'.request()->client_name.'#']), 'client', $id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }

        system_notice([
            'name'                  => 'client_change_password',
            'email_description'     => lang('client_change_password_send_mail'),
            'sms_description'       => lang('client_change_password_send_sms'),
            'task_data' => [
                'client_id' => $client['id'],
                'template_param'      => [
                    'client_password' => $param['new_password'],
                ],
            ],
        ]);

        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-08-16
     * @title 验证码修改密码
     * @desc 验证码修改密码
     * @author theworld
     * @version v1
     * @param string param.type phone 验证类型:phone手机,email邮箱 required
     * @param string param.code 1234 验证码 required
     * @param string param.password 123456 密码 required
     * @param string param.re_password 1 重复密码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function codeUpdatePassword($param)
    {
        if(in_array('password', array_filter(explode(',', configuration('prohibit_user_information_changes'))))){
            return ['status'=>400, 'msg'=>lang('cannot_update_passowrd')];
        }
        
        if (!isset($param['type'])){
            return ['status'=>400,'msg'=>lang('verify_type_is_required')];
        }

        if (!in_array($param['type'],['phone','email'])){
            return ['status'=>400,'msg'=>lang('verify_type_only_phone_or_email')];
        }

        $param['id'] = get_client_id(false);
        $type = $param['type'];
        if ($type == 'phone'){
            return $this->phonePasswordUpdate($param);
        }else{
            return $this->emailPasswordUpdate($param);
        }
    }

    /**
     * 时间 2022-05-20
     * @title 登录
     * @desc 登录
     * @author wyh
     * @version v1
     * @param string type code 登录类型:code验证码登录,password密码登录 required
     * @param string account 18423467948 手机号或邮箱 required
     * @param string phone_code 86 国家区号(手机号登录时需要传此参数)
     * @param string code 1234 验证码(登录类型为验证码登录code时需要传此参数)
     * @param string password 123456 密码(登录类型为密码登录password时需要传此参数)
     * @param string remember_password 1 记住密码(登录类型为密码登录password时需要传此参数,1是,0否)
     * @param string captcha 1234 图形验证码(开启登录图形验证码且为密码登录时或者同一ip地址登录失败3次后需要传此参数)
     * @param string token fd5adaf7267a5b2996cc113e45b38f05 图形验证码唯一识别码(开启登录图形验证码且为密码登录时或者同一ip地址登录失败3次后需要传此参数)
     * @param string client_operate_password - 操作密码
     * @return array
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return string data.jwt - jwt:登录后放在请求头Authorization里,拼接成如下格式:Bearer+空格+jwt
     * @return array data.ip_exception_verify - 状态码400,有该字段,根据返回的验证方式验证,用户异常登录验证方式(operate_password=操作密码)
     */
    public function login($param)
    {
        # 参数验证
        if (!isset($param['type'])){
            return ['status'=>400,'msg'=>lang('login_type_is_required')];
        }

        if (!in_array($param['type'],['code','password'])){
            return ['status'=>400,'msg'=>lang('login_type_only_code_or_password')];
        }

        if (isset($param['password'])){
            $param['password'] = password_decrypt($param['password']);
        }

        $type = $param['type'];
        if ($type == 'code'){
            return $this->codeLogin($param);
        }else{
            return $this->passwordLogin($param);
        }
    }

    /**
     * 时间 2022-05-23
     * @title 注册
     * @desc 注册
     * @author wyh
     * @version v1
     * @param string type phone 登录类型:phone手机注册,email邮箱注册 required
     * @param string account 18423467948 手机号或邮箱 required
     * @param string phone_code 86 国家区号(登录类型为手机注册时需要传此参数)
     * @param string username wyh 姓名
     * @param string code 1234 验证码 required
     * @param string password 123456 密码 required
     * @param string re_password 1 重复密码 required
     * @param object customfield {} 自定义字段,格式:{"field1":'test',"field2":'test2'}
     * @return string data.jwt - jwt:注册后放在请求头Authorization里,拼接成如下格式:Bearer+空格+yJ0eX.test.ste
     */
    public function register($param)
    {
        if (!isset($param['type'])){
            return ['status'=>400,'msg'=>lang('register_type_is_required')];
        }

        if (!in_array($param['type'],['phone','email'])){
            return ['status'=>400,'msg'=>lang('register_type_only_phone_or_email')];
        }
        $hookRes = hook('before_client_register', $param);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }
        # 图形验证码
        /*if (configuration('captcha_client_register')){
            if (!isset($param['captcha']) || empty($param['captcha'])){
                return ['status'=>400,'msg'=>lang('login_captcha')];
            }
            if (!isset($param['token']) || empty($param['token'])){
                return ['status'=>400,'msg'=>lang('login_captcha_token')];
            }
            $token = $param['token'];
            if (!stripe($param['captcha'],$token)){
                return ['status'=>400,'msg'=>lang('login_captcha_error')];
            }
        }*/

        $type = $param['type'];
        if ($type == 'phone'){
            return $this->phoneRegister($param);
        }else{
            return $this->emailRegister($param);
        }
    }

    /**
     * 时间 2022-05-23
     * @title 忘记密码
     * @desc 忘记密码
     * @author wyh
     * @version v1
     * @param string type phone 注册类型:phone手机注册,email邮箱注册 required
     * @param string account 18423467948 手机号或邮箱 required
     * @param string phone_code 86 国家区号(注册类型为手机注册时需要传此参数)
     * @param string code 1234 验证码 required
     * @param string password 123456 密码 required
     * @param string re_password 1 重复密码 required
     */
    public function passwordReset($param)
    {
        if (!isset($param['type'])){
            return ['status'=>400,'msg'=>lang('register_type_is_required')];
        }

        if (!in_array($param['type'],['phone','email'])){
            return ['status'=>400,'msg'=>lang('register_type_only_phone_or_email')];
        }

        $type = $param['type'];
        if ($type == 'phone'){
            return $this->phonePasswordReset($param);
        }else{
            return $this->emailPasswordReset($param);
        }
    }

    /**
     * 时间 2022-5-23
     * @title 注销
     * @desc 注销
     * @author wyh
     * @version v1
     */
    public function logout($param)
    {
        $clientId = get_client_id(false);

        $client = $this->find($clientId);
        if (empty($client)){
            return ['status'=>400,'msg'=>lang('client_is_not_exist')];
        }

        $jwt = get_header_jwt();

        Cache::set('login_token_'.$jwt,null);
        cookie('idcsmart_jwt', null);

        active_log(lang('log_client_logout',['{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'login',$client->id); # 特殊类型

        hook('after_client_logout',['id'=>$clientId,'customfield'=>$param['customfield']??[]]);

        // 清空该用户的操作密码缓存
        idcsmart_cache('CLIENT_OPERATE_PASSWORD_'.$clientId, NULL);

        return ['status'=>200,'msg'=>lang('logout_success')];

    }

    # 手机号+验证码登录
    private function codeLogin($param)
    {
        # 是否开启手机验证码登录
        if (!configuration('login_phone_verify')){
            return ['status'=>400,'msg'=>lang('login_phone_verify_is_not_open')];
        }
        # 区号未填
        if (empty($param['phone_code'])){
            return ['status'=>400,'msg'=>lang('login_phone_code_require')];
        }
        $CountryModel = new CountryModel();
        # 区号错误
        if (!$CountryModel->checkPhoneCode($param['phone_code'])){
            return ['status'=>400,'msg'=>lang('login_phone_code_error')];
        }
        # 手机号未填
        if (empty($param['account'])){
            return ['status'=>400,'msg'=>lang('login_phone_require')];
        }
        # 手机号错误
        if (!check_mobile($param['phone_code']. '-' .$param['account'])){
            return ['status'=>400,'msg'=>lang('login_phone_is_not_right')];
        }
        # 手机号未注册
        if (empty($client = $this->checkPhoneRegister($param['account'],$param['phone_code']))){
            active_log(lang('log_client_login_account_not_register',['{client}'=>$param['account']]),'login',0);
            return ['status'=>400,'msg'=>lang('login_phone_is_not_register')];
        }
        # 登录限制

        # 验证码验证
        if (empty($param['code'])){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        $code = $this->getPhoneVerificationCode($param['account'],$param['phone_code'],'login');
        if (empty($code)){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        if ($code != $param['code']){
            active_log(lang('log_client_login_code_error',['{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'login',$client->id);
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        $this->clearPhoneVerificationCode($param['account'],$param['phone_code'],'login');
        # 账号被禁用
        if ($client['status'] != 1){
            active_log(lang('log_client_login_status_disabled',['{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'login',$client->id);
            return ['status'=>400,'msg'=>lang('login_client_is_disabled')];
        }




        $this->startTrans();

        try{
            $update = [
                'last_login_time' => time(),
                'last_login_ip' => get_client_ip(),
                'last_action_time' => time()
            ];

            $header = request()->header();
            $langAdmin = lang_list('admin');
            $langHome = lang_list('home');
            $lang = array_merge(array_column($langAdmin, 'display_lang'), array_column($langHome, 'display_lang'));
            if (!empty(configuration('lang_home_open'))){
                if(isset($header['language']) && !empty($header['language']) && in_array($header['language'], $lang)){
                    $update['language'] = $header['language'];
                }
            }

            $client->save($update);

            $ClientLoginModel = new ClientLoginModel();
            $ClientLoginModel->clientLogin($client->id);

            # 登录提醒
            # 记录日志
            # 赋值,方便记日志
            $request = request();
            $request->client_id = $client->id;
            $request->client_name = $client['username'];
            active_log(lang('log_client_login',['{client}'=>'client#'.$client->id.'#'.$client->username.'#','{type}'=>lang('login_type_phone_code')]),'login',$client->id); # 特殊类型
			
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('login_fail') . ':' . $e->getMessage()];
        }

        system_notice([
            'name'                  => 'client_login_success',
            'sms_description'       => lang('client_phone_code_login_success_send_sms'),
            'task_data' => [
                'client_id' => $client->id,
                'template_param' => [
                    'login_device'    => get_request_device(),
                ],
            ],
        ]);

        $info = [
            'id' => $client->id,
            'name' => $client->username,
            'remember_password' => 0
        ];

        if(!empty(configuration('home_login_expire_time'))){
            $expired = configuration('home_login_expire_time')*60;
        }else{
            $expired = 3600*24*1; # 最多1天退出
        }

        $jwt = create_jwt($info,$expired);

        $data = [
            'jwt' => $jwt
        ];

        if ($rootDomain = get_root_domain(request()->domain())){
            cookie("idcsmart_jwt",$jwt,['domain'=>$rootDomain]);
            $accountInfo = [
                'id' => $client['id']??0,
                'name' => $client['username']??'',
                'credit' => $client['credit']??0,
            ];
            cookie("idcsmart_account",json_encode($accountInfo,JSON_UNESCAPED_UNICODE),['domain'=>$rootDomain]);
        }else{
            cookie("idcsmart_jwt",$jwt);
        }

        hook('after_client_login',['id'=>$client->id,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('login_success'),'data'=>$data];
    }

    # 密码登录
    private function passwordLogin($param)
    {
        # 验证账号
        if (empty($param['account'])){
            return ['status'=>400,'msg'=>lang('login_account_require')];
        }
        # 登录3次失败,开启图形验证码,且2个小时内操作有效
        $ip = get_client_ip();
        $key = "password_login_times_{$param['account']}_{$ip}";
        Cache::set($key,intval(Cache::get($key))+1,3600*2);

        // wyh 20240529 临时处理，开启安全验证并输入了安全码，不验证图形验证码(导致开了操作密码验证，可绕过图形验证码)
        $homeLoginIpExceptionVerify = configuration(['home_login_ip_exception_verify']);
        $homeLoginIpExceptionVerify = !empty($homeLoginIpExceptionVerify) ? explode(',', $homeLoginIpExceptionVerify) : [];
        $verifyOperatePassword = !empty($homeLoginIpExceptionVerify)
            && !empty($param['security_verify_method'])
            && in_array($param['security_verify_method'], $homeLoginIpExceptionVerify)
            && !empty($param['security_verify_value'])
            && !empty($param['security_verify_token']);

        # 图形验证码
        $verifyCaptcha = (configuration('captcha_client_login') && empty(configuration('captcha_client_login_error'))) || (configuration('captcha_client_login') && configuration('captcha_client_login_error') && Cache::get($key)>3);
        if ($verifyCaptcha && !$verifyOperatePassword){
            if (!isset($param['captcha']) || empty($param['captcha'])){
                return ['status'=>400,'msg'=>lang('login_captcha'),'data'=>['captcha'=>1]];
            }
            if (!isset($param['token']) || empty($param['token'])){
                return ['status'=>400,'msg'=>lang('login_captcha_token'),'data'=>['captcha'=>1]];
            }
            $token = $param['token'];
            if (!check_captcha($param['captcha'],$token)){
                return ['status'=>400,'msg'=>lang('login_captcha_error'),'data'=>['captcha'=>1]];
            }
        }
        # 邮箱登录
        if (strpos($param['account'],'@')>0){
            $result = $this->emailLogin($param);
        }else{ # 手机号登录
            $result = $this->phoneLogin($param);
        }
        # 登录成功后操作
        if ($result['status'] == 200){
            Cache::delete($key);
//            if ($verifyCaptcha){
//                // wyh 20240529 再调一次，清除缓存
//                check_captcha($param['captcha'],$token);
//            }
        }

        return $result;
    }

    # 邮箱+密码登录
    private function emailLogin($param)
    {
        # 验证是否开启电子邮箱密码登录
        if (empty(configuration('login_email_password'))){
            return ['status'=>400,'msg'=>lang('login_email_password_close')];
        }
        # 验证邮箱账号
        if (empty($param['account'])){
            return ['status'=>400,'msg'=>lang('login_account_require')];
        }
        if (strpos($param['account'],'@')===false){
            return ['status'=>400,'msg'=>lang('login_email_error')];
        }
        # 验证记住密码
        if (!isset($param['remember_password']) || !in_array($param['remember_password'],[0,1])){
            return ['status'=>400,'msg'=>lang('login_remember_password_is_0_or_1')];
        }
        # 验证密码
        if (empty($param['password'])){
            return ['status'=>400,'msg'=>lang('login_password_require')];
        }
        # 验证账号
        $email = $param['account'];
        if (empty($client = $this->checkEmailRegister($email))){
            active_log(lang('log_client_login_account_not_register',['{client}'=>$param['account']]),'login',0);
            return ['status'=>400,'msg'=>lang('login_email_is_not_register')];
        }
        # 验证密码是否相等
        if (!idcsmart_password_compare($param['password'],$client->password)){
            active_log(lang('log_client_login_password_error',['{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'login',$client->id);
            return ['status'=>400,'msg'=>lang('login_password_error')];
        }
        # 账号被禁用
        if ($client['status'] != 1){
            active_log(lang('log_client_login_status_disabled',['{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'login',$client->id);
            return ['status'=>400,'msg'=>lang('login_client_is_disabled')];
        }
        // 正常登录日志
        $log = lang('log_client_login',['{client}'=>'client#'.$client->id.'#'.$client->username.'#','{type}'=>lang('login_type_email_password')]);

        // 安全验证结果
        $securityVerifyResult = $this->handleSecurityVerify($client, $param);
        if ($securityVerifyResult['status'] !== 200) {
            return $securityVerifyResult;
        }
        // 如果验证通过，更新日志信息
        if (!empty($securityVerifyResult['log'])) {
            $log = $securityVerifyResult['log'];
        }

        $this->startTrans();

        try{
            $update = [
                'last_login_time' => time(),
                'last_login_ip' => get_client_ip(),
                'last_action_time' => time()
            ];

            $header = request()->header();
            $langAdmin = lang_list('admin');
            $langHome = lang_list('home');
            $lang = array_merge(array_column($langAdmin, 'display_lang'), array_column($langHome, 'display_lang'));
            if (!empty(configuration('lang_home_open'))){
                if(isset($header['language']) && !empty($header['language']) && in_array($header['language'], $lang)){
                    $update['language'] = $header['language'];
                }
            }

            $client->save($update);

            $ClientLoginModel = new ClientLoginModel();
            $ClientLoginModel->clientLogin($client->id);
            # 登录提醒
            # 记录日志
            # 赋值,方便记日志
            $request = request();
            $request->client_id = $client->id;
            $request->client_name = $client['username'];
            active_log($log,'login',$client->id);
			
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('login_fail') . ':' . $e->getMessage()];
        }

        system_notice([
            'name'                  => 'client_login_success',
            'email_description'     => lang('client_email_password_login_success_send_mail'),
            'task_data' => [
                'client_id' => $client->id,
                'template_param' => [
                    'login_device'    => get_request_device(),
                ],
            ],
        ]);

        $info = [
            'id' => $client->id,
            'name' => $client->username,
            'remember_password' => intval($param['remember_password'])
        ];

        if(!empty(configuration('home_login_expire_time'))){
            $expired = configuration('home_login_expire_time')*60;
        }else{
            # 记住密码,保持7天登录状态;否则,2个小时内无操作退出登录
            $expired = $param['remember_password']?3600*24*7:3600*24*1;
        }

        $jwt = create_jwt($info,$expired);

        $data = [
            'jwt' => $jwt
        ];

        if ($rootDomain = get_root_domain(request()->domain())){
            cookie("idcsmart_jwt",$jwt,['domain'=>$rootDomain]);
            $accountInfo = [
                'id' => $client['id']??0,
                'name' => $client['username']??'',
                'credit' => $client['credit']??0,
            ];
            cookie("idcsmart_account",json_encode($accountInfo,JSON_UNESCAPED_UNICODE),['domain'=>$rootDomain]);
        }else{
            cookie("idcsmart_jwt",$jwt);
        }

        // 清除安全验证token
        SecurityVerifyLogic::clearSecurityVerifyToken($client->id);

        hook('after_client_login',['id'=>$client->id,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('login_success'),'data'=>$data];
    }

    # 手机+密码登录
    private function phoneLogin($param)
    {
        # 未开启手机注册
        if (!configuration('login_phone_password')){
            return ['status'=>400,'msg'=>lang('login_phone_is_not_open')];
        }
        # 区号未填
        if (empty($param['phone_code'])){
            return ['status'=>400,'msg'=>lang('login_phone_code_require')];
        }
        $CountryModel = new CountryModel();
        # 区号错误
        if (!$CountryModel->checkPhoneCode($param['phone_code'])){
            return ['status'=>400,'msg'=>lang('login_phone_code_error')];
        }
        # 验证账号
        if (empty($param['account'])){
            return ['status'=>400,'msg'=>lang('login_account_require')];
        }
        # 验证记住密码
        if (!isset($param['remember_password']) || !in_array($param['remember_password'],[0,1])){
            return ['status'=>400,'msg'=>lang('login_remember_password_is_0_or_1')];
        }
        # 验证密码
        if (empty($param['password'])){
            return ['status'=>400,'msg'=>lang('login_password_require')];
        }
        # 手机号错误
        if (!check_mobile($param['phone_code']. '-' .$param['account'])){
            return ['status'=>400,'msg'=>lang('login_phone_is_not_right')];
        }
        # 手机号未注册
        if (empty($client = $this->checkPhoneRegister($param['account'],$param['phone_code']))){
            active_log(lang('log_client_login_account_not_register',['{client}'=>$param['account']]),'login',0);
            return ['status'=>400,'msg'=>lang('login_phone_is_not_register')];
        }
        # 验证密码是否相等
        if (!idcsmart_password_compare($param['password'],$client->password)){
            active_log(lang('log_client_login_password_error',['{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'login',$client->id);
            return ['status'=>400,'msg'=>lang('login_password_error')];
        }
        # 账号被禁用
        if ($client['status'] != 1){
            active_log(lang('log_client_login_status_disabled',['{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'login',$client->id);
            return ['status'=>400,'msg'=>lang('login_client_is_disabled')];
        }
         // 正常登录日志
        $log = lang('log_client_login',['{client}'=>'client#'.$client->id.'#'.$client->username.'#','{type}'=>lang('login_type_phone_password')]);

        // 安全验证结果
        $securityVerifyResult = $this->handleSecurityVerify($client, $param);
        if ($securityVerifyResult['status'] !== 200) {
            return $securityVerifyResult;
        }
        // 如果验证通过，更新日志信息
        if (!empty($securityVerifyResult['log'])) {
            $log = $securityVerifyResult['log'];
        }

        $this->startTrans();

        try{
            $update = [
                'last_login_time' => time(),
                'last_login_ip' => get_client_ip(),
                'last_action_time' => time()
            ];

            $header = request()->header();
            $langAdmin = lang_list('admin');
            $langHome = lang_list('home');
            $lang = array_merge(array_column($langAdmin, 'display_lang'), array_column($langHome, 'display_lang'));

            if (!empty(configuration('lang_home_open'))){
                if(isset($header['language']) && !empty($header['language']) && in_array($header['language'], $lang)){
                    $update['language'] = $header['language'];
                }
            }

            $client->save($update);

            $ClientLoginModel = new ClientLoginModel();
            $ClientLoginModel->clientLogin($client->id);
            # 登录提醒
            # 记录日志
            # 赋值,方便记日志
            $request = request();
            $request->client_id = $client->id;
            $request->client_name = $client['username'];
            active_log($log,'login',$client->id);
			
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('login_fail') . ':' . $e->getMessage()];
        }

        system_notice([
            'name'                  => 'client_login_success',
            'sms_description'       => lang('client_phone_password_login_success_send_sms'),
            'task_data' => [
                'client_id' => $client->id,
                'template_param' => [
                    'login_device'    => get_request_device(),
                ],
            ],
        ]);

        $info = [
            'id' => $client->id,
            'name' => $client->username,
            'remember_password' => intval($param['remember_password'])
        ];

        if(!empty(configuration('home_login_expire_time'))){
            $expired = configuration('home_login_expire_time')*60;
        }else{
            # 记住密码,保持7天登录状态;否则,2个小时内无操作退出登录
            $expired = $param['remember_password']?3600*24*7:3600*24*1;
        }

        $jwt = create_jwt($info,$expired);

        $data = [
            'jwt' => $jwt
        ];

        if ($rootDomain = get_root_domain(request()->domain())){
            cookie("idcsmart_jwt",$jwt,['domain'=>$rootDomain]);
            $accountInfo = [
                'id' => $client['id']??0,
                'name' => $client['username']??'',
                'credit' => $client['credit']??0,
            ];
            cookie("idcsmart_account",json_encode($accountInfo,JSON_UNESCAPED_UNICODE),['domain'=>$rootDomain]);
        }else{
            cookie("idcsmart_jwt",$jwt);
        }

        // 清除安全验证token
        SecurityVerifyLogic::clearSecurityVerifyToken($client->id);

        hook('after_client_login',['id'=>$client->id,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('login_success'),'data'=>$data];
    }

    # 检查手机注册用户是否存在并返回用户数据
    private function checkPhoneRegister($phone,$phone_code)
    {
        $client = $this->where('phone',$phone)
            ->where('phone_code',$phone_code)
            ->find();
        return $client;
    }

    # 检查邮箱注册用户是否存在并返回用户数据
    private function checkEmailRegister($email)
    {
        $client = $this->where('email',$email)->find();
        return $client;
    }

    # 获取手机验证码
    public function getPhoneVerificationCode($phone,$phone_code,$action='login')
    {
        return Cache::get('verification_code_'.$action . '_' . $phone_code. '_' . $phone);
    }

    # 获取邮箱验证码
    public function getEmailVerificationCode($email,$action='register')
    {
        return Cache::get('verification_code_'.$action.'_'.$email);
    }

    # 清除手机验证码
    private function clearPhoneVerificationCode($phone,$phone_code,$action='login')
    {
        return Cache::delete('verification_code_'.$action . '_' . $phone_code. '_' . $phone);
    }

    # 清除邮箱验证码
    private function clearEmailVerificationCode($email,$action='register')
    {
        return Cache::delete('verification_code_'.$action.'_'.$email);
    }

    # 手机号注册
    public function phoneRegister($param, $is_oauth = false)
    {
        # 未开启手机注册
        if (!configuration('register_phone')){
            return ['status'=>400,'msg'=>$is_oauth ? lang('account_is_not_exist') : lang('register_phone_is_not_open')];
        }
        # 验证手机
        if (!check_mobile($param['phone_code'] . '-' .$param['account'])){
            return ['status'=>400,'msg'=>lang('please_enter_vaild_phone')];
        }
        # 区号未填
        if (empty($param['phone_code'])){
            return ['status'=>400,'msg'=>lang('login_phone_code_require')];
        }
        $CountryModel = new CountryModel();
        # 区号错误
        if (!$CountryModel->checkPhoneCode($param['phone_code'])){
            return ['status'=>400,'msg'=>lang('login_phone_code_error')];
        }
        # 验证用户名
        if (strlen($param['username'])>20){
            return ['status'=>400,'msg'=>lang('client_name_cannot_exceed_20_chars')];
        }
        # 验证码
        if (!$is_oauth && configuration('code_client_phone_register')){
            if (!isset($param['code']) || empty($param['code'])){
                return ['status'=>400,'msg'=>lang('verification_code_error')];
            }
            $code = $this->getPhoneVerificationCode($param['account'],$param['phone_code'],'register');
            if (empty($code)){
                return ['status'=>400,'msg'=>lang('verification_code_error')];
            }
            if ($param['code'] != $code){
                return ['status'=>400,'msg'=>lang('verification_code_error')];
            }
        }

        # 验证密码
        if (empty($param['password']) || empty($param['re_password'])){
            return ['status'=>400,'msg'=>lang('login_password_require')];
        }
        if (strlen($param['password'])<6 || strlen($param['password'])>32){
            return ['status'=>400,'msg'=>lang('login_password_len')];
        }
        if ($param['password'] != $param['re_password']){
            return ['status'=>400,'msg'=>lang('passwords_not_match')];
        }
        # 账号是否已注册
        if (!empty($this->checkPhoneRegister($param['account'],$param['phone_code']))){
            return ['status'=>400,'msg'=>lang('phone_has_been_registered')];
        }

        $this->startTrans();

        try{
            $time = time();
            $client = $this->create([
                'username' => $param['username']?:$param['account'],
                'phone_code' => $param['phone_code'],
                'phone' => $param['account'],
                'password' => idcsmart_password($param['password']),
                'last_login_time' => $time,
                'last_login_ip' => get_client_ip(),
                'last_action_time' => $time,
                'language' => configuration('lang_home')??'zh-cn',
                'country_id' => 44,
                'create_time' => $time
            ]);

            $this->clearPhoneVerificationCode($param['account'],$param['phone_code'],'register');

            $ClientLoginModel = new ClientLoginModel();
            $ClientLoginModel->clientLogin($client->id);

            # 发送邮件短信
            # 记录日志
            # 赋值,方便记日志
            $request = request();
            $request->client_id = $client->id;
            $request->client_name = $client['username'];
            active_log(lang('log_client_register',['{account}'=>$param['account']]),'client',$client->id);
			
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('register_fail') . ':' . $e->getMessage()];
        }

        system_notice([
            'name'                  => 'client_register_success',
            'sms_description'       => lang('client_sms_register_success_send_sms'),
            'task_data' => [
                'client_id' => $client->id,
            ],
        ]);

        $info = [
            'id' => $client->id,
            'name' => $client->username,
            'remember_password' => 0
        ];

        if(!empty(configuration('home_login_expire_time'))){
            $expired = configuration('home_login_expire_time')*60;
        }else{
            $expired = 3600*24*1;
        }

        $jwt = create_jwt($info,$expired);

        $data = [
            'jwt'   => $jwt
        ];
        if($is_oauth){
            $data['id'] = $client->id;
        }

        if ($rootDomain = get_root_domain(request()->domain())){
            cookie("idcsmart_jwt",$jwt,['domain'=>$rootDomain]);
            $accountInfo = [
                'id' => $client['id']??0,
                'name' => $client['username']??'',
                'credit' => $client['credit']??0,
            ];
            cookie("idcsmart_account",json_encode($accountInfo,JSON_UNESCAPED_UNICODE),['domain'=>$rootDomain]);
        }else{
            cookie("idcsmart_jwt",$jwt);
        }

        hook('after_client_register',['id'=>$client->id,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('register_success'),'data'=>$data];

    }

    # 邮箱注册
    public function emailRegister($param, $is_oauth = false)
    {
        # 未开启邮箱注册
        if (!configuration('register_email')){
            return ['status'=>400,'msg'=>$is_oauth ? lang('account_is_not_exist') : lang('register_email_is_not_open')];
        }
        # 验证邮箱
        if (strpos($param['account'],'@')===false){
            return ['status'=>400,'msg'=>lang('login_email_error')];
        }
        # 验证邮箱后缀
        if (configuration('limit_email_suffix')){
            $emailSuffix = configuration('email_suffix');
            $emailSuffix = explode(',', $emailSuffix);
            if(!in_array(substr($param['account'], strpos($param['account'],'@')), $emailSuffix)){
                return ['status'=>400,'msg'=>lang('limit_email_suffix_cannot_register')];
            }
        }
        # 验证用户名
        if (strlen($param['username'])>20){
            return ['status'=>400,'msg'=>lang('client_name_cannot_exceed_20_chars')];
        }
        # 验证码
        if (!$is_oauth && configuration('code_client_email_register')){
            if (!isset($param['code']) || empty($param['code'])){
                return ['status'=>400,'msg'=>lang('verification_code_error')];
            }
            $code = $this->getEmailVerificationCode($param['account']);
            if (empty($code)){
                return ['status'=>400,'msg'=>lang('verification_code_error')];
            }
            if ($param['code'] != $code){
                return ['status'=>400,'msg'=>lang('verification_code_error')];
            }
        }
        # 验证密码
        if (empty($param['password']) || empty($param['re_password'])){
            return ['status'=>400,'msg'=>lang('login_password_require')];
        }
        if (strlen($param['password'])<6 || strlen($param['password'])>32){
            return ['status'=>400,'msg'=>lang('login_password_len')];
        }
        if ($param['password'] != $param['re_password']){
            return ['status'=>400,'msg'=>lang('passwords_not_match')];
        }
        # 账号是否已注册
        if (!empty($this->checkEmailRegister($param['account']))){
            return ['status'=>400,'msg'=>lang('email_has_been_registered')];
        }

        $this->startTrans();

        try{
            $time = time();
            $client = $this->create([
                'username' => $param['username']?:(explode('@',$param['account'])[0]?:$param['account']),
                'email' => $param['account'],
                'password' => idcsmart_password($param['password']),
                'last_login_time' => $time,
                'last_login_ip' => get_client_ip(),
                'last_action_time' => $time,
                'language' => configuration('lang_home')??'zh-cn',
                'country_id' => 44,
                'create_time' => $time
            ]);

            $this->clearEmailVerificationCode($param['account']);

            $ClientLoginModel = new ClientLoginModel();
            $ClientLoginModel->clientLogin($client->id);

            # 发送邮件短信
            # 记录日志
            # 赋值,方便记日志
            $request = request();
            $request->client_id = $client->id;
            $request->client_name = $client['username'];
            active_log(lang('log_client_register',['{account}'=>$param['account']]),'client',$client->id);
			
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('register_fail') . ':' . $e->getMessage()];
        }

        system_notice([
            'name'                  => 'client_register_success',
            'email_description'     => lang('client_mail_register_success_send_mail'),
            'task_data' => [
                'client_id' => $client->id,
            ],
        ]);

        $info = [
            'id' => $client->id,
            'name' => $client->username,
            'remember_password' => 0
        ];

        if(!empty(configuration('home_login_expire_time'))){
            $expired = configuration('home_login_expire_time')*60;
        }else{
            $expired = 3600*24*1;
        }

        $jwt = create_jwt($info,$expired);

        $data = [
            'jwt' => $jwt
        ];
        if($is_oauth){
            $data['id'] = $client->id;
        }

        if ($rootDomain = get_root_domain(request()->domain())){
            cookie("idcsmart_jwt",$jwt,['domain'=>$rootDomain]);
            $accountInfo = [
                'id' => $client['id']??0,
                'name' => $client['username']??'',
                'credit' => $client['credit']??0,
            ];
            cookie("idcsmart_account",json_encode($accountInfo,JSON_UNESCAPED_UNICODE),['domain'=>$rootDomain]);
        }else{
            cookie("idcsmart_jwt",$jwt);
        }

        hook('after_client_register',['id'=>$client->id,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('register_success'),'data'=>$data];
    }

    # 手机重置密码
    private function phonePasswordReset($param)
    {
        # 未开启手机注册
        if (!configuration('register_phone')){
            return ['status'=>400,'msg'=>lang('register_phone_is_not_open')];
        }
        # 验证手机
        if ($param['phone_code']=='86'){
            $check = $param['account'];
        }else{
            $check = $param['phone_code'] . "-" .$param['account'];
        }

        if (!check_mobile($check)){
            return ['status'=>400,'msg'=>lang('please_enter_vaild_phone')];
        }
        # 区号未填
        if (empty($param['phone_code'])){
            return ['status'=>400,'msg'=>lang('login_phone_code_require')];
        }
        $CountryModel = new CountryModel();
        # 区号错误
        if (!$CountryModel->checkPhoneCode($param['phone_code'])){
            return ['status'=>400,'msg'=>lang('login_phone_code_error')];
        }
        # 验证码
        if (empty($param['code'])){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        $code = $this->getPhoneVerificationCode($param['account'],$param['phone_code'],'password_reset');
        if (empty($code)){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        if ($param['code'] != $code){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        # 验证密码
        if (empty($param['password']) || empty($param['re_password'])){
            return ['status'=>400,'msg'=>lang('login_password_require')];
        }
        if (strlen($param['password'])<6 || strlen($param['password'])>32){
            return ['status'=>400,'msg'=>lang('login_password_len')];
        }
        if ($param['password'] != $param['re_password']){
            return ['status'=>400,'msg'=>lang('passwords_not_match')];
        }
        # 账号是否已注册
        if (empty($client = $this->checkPhoneRegister($param['account'],$param['phone_code']))){
            return ['status'=>400,'msg'=>lang('login_phone_is_not_register')];
        }

        $this->startTrans();

        try{
            $client->save([
                'update_time' => time(),
                'password' => idcsmart_password($param['password'])
            ]);

            $this->clearPhoneVerificationCode($param['account'],$param['phone_code'],'password_reset');

            Cache::set('home_update_password_'.$client->id,time(),3600*24*365); # 365天未操作接口,就可以不退出

            # 发送邮件短信
            # 记录日志
            # 赋值,方便记日志
            $request = request();
            $request->client_id = $client->id;
            $request->client_name = $client['username'];
            active_log(lang('change_password',['{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'client',$client->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail') . ':' . $e->getMessage()];
        }

        hook('after_client_password_reset',['id'=>$client->id,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    # 邮箱重置密码
    private function emailPasswordReset($param)
    {
        # 验证邮箱
        if (strpos($param['account'],'@')===false){
            return ['status'=>400,'msg'=>lang('login_email_error')];
        }
        # 验证码
        if (empty($param['code'])){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        $code = $this->getEmailVerificationCode($param['account'],'password_reset');
        if (empty($code)){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        if ($param['code'] != $code){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        # 验证密码
        if (empty($param['password']) || empty($param['re_password'])){
            return ['status'=>400,'msg'=>lang('login_password_require')];
        }
        if (strlen($param['password'])<6 || strlen($param['password'])>32){
            return ['status'=>400,'msg'=>lang('login_password_len')];
        }
        if ($param['password'] != $param['re_password']){
            return ['status'=>400,'msg'=>lang('passwords_not_match')];
        }
        # 账号是否已注册
        if (empty($client = $this->checkEmailRegister($param['account']))){
            return ['status'=>400,'msg'=>lang('login_email_is_not_register')];
        }

        $this->startTrans();

        try{
            $client->save([
                'update_time' => time(),
                'password' => idcsmart_password($param['password'])
            ]);

            $this->clearEmailVerificationCode($param['account'],'password_reset');

            Cache::set('home_update_password_'.$client->id,time(),3600*24*365); # 365天未操作接口,就可以不退出

            # 发送邮件短信
            # 记录日志
            # 赋值,方便记日志
            $request = request();
            $request->client_id = $client->id;
            $request->client_name = $client['username'];
            active_log(lang('change_password',['{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'client',$client->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail') . ':' . $e->getMessage()];
        }

        hook('after_client_password_reset',['id'=>$client->id,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    # 手机重置密码
    private function phonePasswordUpdate($param)
    {
        # 未开启手机注册
        if (!configuration('register_phone')){
            return ['status'=>400,'msg'=>lang('register_phone_is_not_open')];
        }
        
        $client = $this->where('status', 1)->find($param['id']);
        if(empty($client)){
            return ['status'=>400,'msg'=>lang('fail_message')];
        }

        # 验证手机
        if(empty($client['phone'])){
            return ['status'=>400, 'msg'=>lang('user_not_bind_phone')];
        }

        # 验证码
        if (empty($param['code'])){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        $code = $this->getPhoneVerificationCode($client['account'],$client['phone_code'],'verify');
        if (empty($code)){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        if ($param['code'] != $code){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }

        $this->startTrans();

        try{
            $client->save([
                'update_time' => time(),
                'password' => idcsmart_password($param['password'])
            ]);

            $this->clearPhoneVerificationCode($client['phone'],$client['phone_code'],'verify');

            Cache::set('home_update_password_'.$client->id,time(),3600*24*365); # 365天未操作接口,就可以不退出

            # 记录日志
            active_log(lang('change_password',['{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'client',$client->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail') . ':' . $e->getMessage()];
        }

        system_notice([
            'name'                  => 'client_change_password',
            'email_description'     => lang('client_change_password_send_mail'),
            'sms_description'       => lang('client_change_password_send_sms'),
            'task_data' => [
                'client_id' => $client->id,
                'template_param'    => [
                    'client_password' => $param['password'],
                ],
            ],
        ]);

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    # 邮箱重置密码
    private function emailPasswordUpdate($param)
    {
        $client = $this->where('status', 1)->find($param['id']);
        if(empty($client)){
            return ['status'=>400,'msg'=>lang('fail_message')];
        }

        if(empty($client['email'])){
            return ['status'=>400, 'msg'=>lang('user_not_bind_email')];
        }

        # 验证码
        if (empty($param['code'])){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        $code = $this->getEmailVerificationCode($client['email'],'verify');
        if (empty($code)){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        if ($param['code'] != $code){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }

        $this->startTrans();

        try{
            $client->save([
                'update_time' => time(),
                'password' => idcsmart_password($param['password'])
            ]);

            $this->clearEmailVerificationCode($client['email'],'verify');

            Cache::set('home_update_password_'.$client->id,time(),3600*24*365); # 365天未操作接口,就可以不退出

            # 记录日志
            active_log(lang('change_password',['{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'client',$client->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail') . ':' . $e->getMessage()];
        }

        system_notice([
            'name'                  => 'client_change_password',
            'email_description'     => lang('client_change_password_send_mail'),
            'sms_description'       => lang('client_change_password_send_sms'),
            'task_data' => [
                'client_id' => $client->id,
                'template_param'=>[
                    'client_password' => $param['password'],
                ],
            ],
        ]);

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2022-05-30
     * @title 以用户登录
     * @desc 以用户登录
     * @author wyh
     * @version v1
     * @param int id - 用户ID
     * @return string data.jwt - jwt:获取后放在请求头Authorization里,拼接成如下格式:Bearer yJ0eX.test.ste
     */
    public function loginByClient($id)
    {
        $client = $this->find($id);
        if (empty($client)){
            return ['status'=>400,'msg'=>lang('client_is_not_exist')];
        }

        $info = [
            'id' => $id,
            'name' => $client->username,
            'remember_password' => 0
        ];

        if(!empty(configuration('home_login_expire_time'))){
            $expired = configuration('home_login_expire_time')*60;
        }else{
            $expired = 3600*24*1;
        }

        $jwt = create_jwt($info,$expired);

        $data = [
            'jwt' => $jwt
        ];

        if ($rootDomain = get_root_domain(request()->domain())){
            cookie("idcsmart_jwt",$jwt,['domain'=>$rootDomain]);
            $accountInfo = [
                'id' => $client['id']??0,
                'name' => $client['username']??'',
                'credit' => $client['credit']??0,
            ];
            cookie("idcsmart_account",json_encode($accountInfo,JSON_UNESCAPED_UNICODE),['domain'=>$rootDomain]);
        }else{
            cookie("idcsmart_jwt",$jwt);
        }

        $ClientLoginModel = new ClientLoginModel();
        $ClientLoginModel->clientLogin($id);

        # 记录日志
        active_log(lang('log_login_by_client',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'admin',get_admin_id());

        return ['status'=>200,'msg'=>lang('login_success'),'data'=>$data];
    }

    # 判断是否新客户
    public function newClient($id)
    {
        if (empty($id)){
            return false;
        }

        $client = $this->find($id);
        if (empty($client)){
            return false;
        }

        $HostModel = new HostModel();
        $host = $HostModel->alias('h')
            ->leftJoin('order_item oi','oi.host_id=h.id')
            ->leftJoin('order o','o.id=oi.order_id')
            ->where('h.client_id',$id)
            ->where('h.is_sub',0)
            ->where('oi.type','host')
            ->where('o.status','<>','Unpaid')
            ->find();

        if (!empty($host)){
            return false;
        }

        $OrderModel = new OrderModel();
        $total = $OrderModel->where('client_id',$id)
            ->where('status','Paid')
            ->whereNotIn('type',['recharge','artificial'])
            ->sum('amount');
        if ($total>0){
            return false;
        }

        return true;
    }


    /**
     * 时间 2022-09-16
     * @title 最近访问用户列表
     * @desc 最近访问用户列表
     * @author theworld
     * @version v1
     * @url /admin/v1/index/visit_client
     * @method  GET
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list - 用户列表
     * @return int list[].id - ID
     * @return int list[].username - 姓名
     * @return int list[].email - 邮箱
     * @return int list[].phone_code - 国际电话区号
     * @return int list[].phone - 手机号
     * @return int list[].company - 公司
     * @return int list[].visit_time - 访问时间
     * @return int count - 用户总数
     */
    public function visitClientList($param)
    {

        $clients = $this->field('id,username,email,phone_code,phone,company,last_action_time')
            ->where('status', 1)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order('last_action_time', 'desc')
            ->select()
            ->toArray();

        $count = $this->field('id')
            ->where('status', 1)
            ->count();

        $time = time();
        foreach ($clients as $key => $value) {
            $visitTime = $time - $value['last_action_time'];

            if($visitTime>365*24*3600){
                $clients[$key]['visit_time'] = lang('one_year_ago');
            }else{
                $day = floor($visitTime/(24*3600));
                $visitTime = $visitTime%(24*3600);
                $hour = floor($visitTime/3600);
                $visitTime = $visitTime%3600;
                $minute = floor($visitTime/60);

                $clients[$key]['visit_time'] = ($day>0 ? $day.lang('day') : '').($hour>0 ? $hour.lang('hour') : '').($minute>0 ? $minute.lang('minute') : '');
                $clients[$key]['visit_time'] = !empty($clients[$key]['visit_time']) ? $clients[$key]['visit_time'].lang('ago') : $minute.lang('minute').lang('ago');
            }
            unset($clients[$key]['last_action_time']);
        }

        return ['list'=>$clients, 'count'=>$count];
    }

    /**
     * 时间 2023-02-16
     * @title API鉴权登录
     * @desc API鉴权登录
     * @author wyh
     * @version v1
     * @url /api/v1/auth
     * @method  POST
     * @param string username - 用户名(用户注册时的邮箱或手机号)
     * @param string password - 密码(api信息的token)
     * @return string data.jwt - 登录标识
     */
    public function apiAuth($param)
    {
        $this->startTrans();

        try{
            $username = trim($param['username']);

            $password = trim($param['password']);

            if (strpos($username,'@') !== false){
                $client = $this->where('email',$username)->find();
            }else{
                $client = $this->where('phone',$username)->find();
            }

            if (empty($client)){
                throw new \Exception(lang('client_is_not_exist'));
            }

            $ApiModel = new ApiModel();

            $api = $ApiModel->where('client_id',$client['id'])->where('token',aes_password_encode($password))->find();

            if (empty($api)){
                throw new \Exception(lang('api_auth_fail'));
            }

            if ($api['status']==1 && !in_array(get_client_ip(),explode("\n",$api['ip']))){
                throw new \Exception(lang('api_auth_fail'));
            }

            /*if (aes_password_encode($password)!=$api['token']){
                throw new \Exception(lang('api_auth_fail'));
            }*/

            $upData = [
                'last_login_time' => time(),
                'last_login_ip' => get_client_ip()
            ];

            $client->save($upData);

            $info = [
                'id' => $client['id'],
                'name' => $client['username'],
                'remember_password' => 0,
                'is_api' => true,
                'api_id' => $api['id'],
                'api_name' => $api['name']
            ];

            // 修改产品关联的API
            if(isset($param['host_id']) && !empty($param['host_id']) && is_array($param['host_id'])){
                $HostModel = new HostModel();
                $host = $HostModel->field('id,downstream_info')->where('client_id', $client['id'])->whereIn('id', $param['host_id'])->select()->toArray();
                foreach ($host as $v) {
                    $downstreamInfo = json_decode($v['downstream_info'], true);
                    if(!empty($downstreamInfo)){
                        $downstreamInfo['api'] = $api['id'];
                        $HostModel->where('id', $v['id'])->update(['downstream_info' => json_encode($downstreamInfo)]); 
                    }
                }
            }

            active_log(lang('log_api_auth_login',['{client}'=>'client#'.$client['id'].'#'.$client['username'].'#']),'client',$client['id']);

            hook('client_api_login',['id'=>$client['id'],'username'=>$username,'password'=>$password]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang('success_message'),'data'=>['jwt'=>create_jwt($info)]];
    }

    /**
     * 时间 2024-05-20
     * @title 修改操作密码
     * @desc  修改操作密码
     * @author hh
     * @version v1
     * @param   string param.origin_operate_password - 原操作密码 已有操作密码必传
     * @param   string param.operate_password - 新操作密码
     * @param   string param.re_operate_password - 重复操作密码
     * @return  int status - 状态码,200成功,400失败
     * @return  string msg - 提示信息
     */
    public function updateOperatePassword($param)
    {
        $clientId = get_client_id();
        $client = $this->find($clientId);
        if (empty($client)){
            return ['status'=>400,'msg'=>lang('fail_message')];
        }
        // 安全验证处理
        $securityResult = handle_security_verify($clientId, $param, 'update_operate_password');
        if ($securityResult['status'] !== 200) {
            return $securityResult;
        }
        // 原密码不对
//        if(!empty($client['operate_password'])){
//            if(!isset($param['origin_operate_password']) || $param['origin_operate_password'] === ''){
//                return ['status'=>400, 'msg'=>lang('origin_operate_password_require')];
//            }
//            if(idcsmart_password($param['origin_operate_password']) !== $client['operate_password']){
//                return ['status'=>400, 'msg'=>lang('origin_operate_password_error')];
//            }
//        }

        # 日志详情
        $description = lang('log_origin_operate_password_update_success', [
            '{admin}'   => 'client#'.$client['id'].'#'.$client['username'].'#',
        ]);

        $this->startTrans();
        try{
            $update['operate_password'] = idcsmart_password($param['operate_password']);
            $this->update($update, ['id'=>$client->id]);
            # 记录日志
            active_log($description, 'client', $client->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail') . ':' . $e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2024-06-05
     * @title 是否通知
     * @desc  是否通知
     * @author wyh
     * @version v1
     * @param string type - 方式：email邮件，sms短信，
     * @param int client_id - 客户ID
     * @return bool -
     */
    public function clientNotice($param)
    {
        if (!in_array($param['type'],['email','sms'])){
            return true;
        }
        // 默认发送
        if (empty($param['client_id'])){
            return true;
        }

        $client = $this->field('notice_open,notice_method')->find($param['client_id']);

        // 默认发送
        if (empty($client)){
            return true;
        }

        if ($client['notice_open']==1){
            if ($client['notice_method']=='all' || $client['notice_method']==$param['type']){
                return true;
            }
        }

        return false;
    }

    /**
     * @时间 2025-04-08
     * @title 获取用户可用直接支付方式
     * @desc  获取用户可用直接支付方式
     * @author hh
     * @version v1
     * @param   array param - 参数
     * @param   int param.id - 用户ID require
     * @return  array
     * @return  string [].gateway - 支付方式标识
     * @return  float [].amount - 可用金额
     */
    public function getClientEnableGateway($id): array
    {
        $client = $this->find($id);
        $data = [];
        if(!empty($client)){
            $data[] = [
                'gateway'   => 'credit',
                'amount'    => $client['credit'],
            ];

            $hookRes = hook('client_enable_direct_gateway', ['id'=>$id]);
            foreach($hookRes as $v){
                if(!empty($v) && is_array($v) && !empty($v['status']) && $v['status'] == 200 && !empty($v['data']['gateway']) && isset($v['data']['amount'])){
                    $data[] = [
                        'gateway'   => $v['data']['gateway'],
                        'amount'    => $v['data']['amount'],
                    ];
                }
            }
        }
        return $data;
    }

    /**
     * 时间 2022-05-10
     * @title 修改语言
     * @desc 修改语言
     * @author theworld
     * @version v1
     * @param string param.language - 语言
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateLanguage($param)
    {
        $param['language'] = $param['language'] ?? '';

        $langAdmin = lang_list('admin');
        $langHome = lang_list('home');
        $lang = array_merge(array_column($langAdmin, 'display_lang'), array_column($langHome, 'display_lang'));
        if(!in_array($param['language'], $lang)){
            return ['status'=>400, 'msg'=>lang('param_error')];
        }

        $clientId = get_client_id();
        if(empty($clientId)){
            if ($rootDomain = get_root_domain(request()->domain())){
                cookie("web_language",$param['language'],['domain'=>$rootDomain]);
            }else{
                cookie("web_language",$param['language']);
            }

            lang_plugins('success_message', [], true);
            
            $result = [
                'status' => 200,
                'msg' => lang('success_message'),
            ];
        }else{
            $client = $this->find($clientId);
            # 日志详情
            $description = [];
            if ($client['language'] != $param['language']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_language').$client['language'], '{new}'=>$param['language']]);
            }
            $description = implode(',', $description);

            $this->startTrans();
            try {
                $this->update([
                    'language' => $param['language'] ?? '',
                    'update_time' => time(),
                ], ['id' => $clientId]);

                if($param['language']!=$client['language']){
                    lang_plugins('success_message', [], true);
                }

                if(!empty($description)){
                    # 记录日志
                    active_log(lang('modify_profile', ['{client}'=>'client#'.$client->id.'#'.request()->client_name.'#', '{description}'=>$description]), 'client', $client->id);
                }

                $this->commit();
            } catch (\Exception $e) {
                // 回滚事务
                $this->rollback();
                return ['status' => 400, 'msg' => lang('update_fail')];
            }

        }

        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2024-11-25
     * @title 处理安全验证
     * @desc 处理异常IP登录的安全验证
     * @author wyh
     * @version v1
     * @param object $client 用户对象
     * @param array $param 登录参数
     * @return array
     */
    private function handleSecurityVerify($client, $param)
    {
        // 1. 检查是否已提交安全验证参数
        if (!empty($param['security_verify_method'])) {
            $SecurityVerifyLogic = new \app\common\logic\SecurityVerifyLogic();
            
            switch ($param['security_verify_method']) {
                case 'operate_password':
                    // 用户未设置操作密码，直接通过
                    if (empty($client->operate_password)) {
                        return ['status' => 200];
                    }
                    // 验证操作密码
                    if (empty($param['security_verify_value'])) {
                        return ['status' => 400, 'msg' => lang('operate_password_required')];
                    }
                    $verifyResult = $SecurityVerifyLogic->verifyOperatePassword($client->id, $param['security_verify_value']);
                    if ($verifyResult['status'] !== 200) {
                        return $verifyResult;
                    }
                    return [
                        'status' => 200,
                        'log' => lang('log_client_login_security_verify_success', [
                            '{client}' => 'client#' . $client->id . '#' . $client->username . '#',
                            '{method}' => lang('security_verify_operate_password')
                        ])
                    ];

                case 'email_code':
                    // 用户未绑定邮箱，直接通过
                    if (empty($client->email)) {
                        return ['status' => 200];
                    }
                    // 验证邮箱验证码
                    if (empty($param['security_verify_value'])) {
                        return ['status' => 400, 'msg' => lang('verification_code_required')];
                    }
                    $verifyResult = $SecurityVerifyLogic->verifyCode($client->id, $param['security_verify_method'], $param['security_verify_value'],'exception_login');
                    if ($verifyResult['status'] !== 200) {
                        return $verifyResult;
                    }
                    return [
                        'status' => 200,
                        'log' => lang('log_client_login_security_verify_success', [
                            '{client}' => 'client#' . $client->id . '#' . $client->username . '#',
                            '{method}' => lang('security_verify_email_code')
                        ])
                    ];

                case 'phone_code':
                    // 用户未绑定手机，直接通过
                    if (empty($client->phone)) {
                        return ['status' => 200];
                    }
                    // 验证手机验证码
                    if (empty($param['security_verify_value'])) {
                        return ['status' => 400, 'msg' => lang('verification_code_required')];
                    }
                    $verifyResult = $SecurityVerifyLogic->verifyCode($client->id, $param['security_verify_method'], $param['security_verify_value'],'exception_login');
                    if ($verifyResult['status'] !== 200) {
                        return $verifyResult;
                    }
                    return [
                        'status' => 200,
                        'log' => lang('log_client_login_security_verify_success', [
                            '{client}' => 'client#' . $client->id . '#' . $client->username . '#',
                            '{method}' => lang('security_verify_phone_code')
                        ])
                    ];

                case 'certification':
                    // 用户未实名认证，直接通过
                    if (!check_certification($client->id)) {
                        return ['status' => 200];
                    }
                    // 验证实名认证
                    if (empty($param['certify_id'])) {
                        return ['status' => 400, 'msg' => lang('certify_id_required')];
                    }
                    if (!$SecurityVerifyLogic->isCertificationPassed($client->id, $param['certify_id'])) {
                        return ['status' => 400, 'msg' => lang('certification_not_passed')];
                    }
                    return [
                        'status' => 200,
                        'log' => lang('log_client_login_security_verify_success', [
                            '{client}' => 'client#' . $client->id . '#' . $client->username . '#',
                            '{method}' => lang('security_verify_certification')
                        ])
                    ];

                default:
                    return ['status' => 400, 'msg' => lang('security_verify_method_invalid')];
            }
        }

        // 2. 检查是否需要异常IP验证
        if (!$this->checkNeedSecurityVerify($client)) {
            return ['status' => 200]; // 不需要验证，直接通过
        }

        // 3. 获取可用验证方式
        $SecurityVerifyLogic = new \app\common\logic\SecurityVerifyLogic();
        $availableMethods = $SecurityVerifyLogic->getAvailableMethods($client->id, 'login');

        // 如果没有可用验证方式，跳过验证
        if (empty($availableMethods)) {
            return ['status' => 200];
        }

        // 4. 返回需要验证的提示
        return [
            'status' => 400,
            'msg' => lang('login_ip_exception_need_verify'),
            'data' => [
                'need_security_verify' => true,
//                'client_id' => $client->id,
                'available_methods' => $availableMethods,
            ]
        ];
    }

    /**
     * 时间 2024-11-25
     * @title 检查是否需要异常IP验证
     * @desc 根据IP登录次数判断是否需要安全验证（登录>=3次视为常用IP）
     * @author wyh
     * @version v1
     * @param object $client 用户对象
     * @return bool
     */
    private function checkNeedSecurityVerify($client)
    {
        // 1. 检查是否配置异常IP验证方式
        $exceptionVerifyConfig = configuration('home_login_ip_exception_verify');
        if (empty($exceptionVerifyConfig)) {
            return false; // 未配置验证方式，不验证
        }

        // 2. 使用ClientLoginModel判断是否常用IP登录
        $ClientLoginModel = new ClientLoginModel();
        $isCommonIpLogin = $ClientLoginModel->isCommonIpLogin(['client_id' => $client->id]);

        // 不是常用IP登录，需要验证
        return !$isCommonIpLogin;
    }
}
