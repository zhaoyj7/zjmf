<?php
namespace app\common\model;

use think\Model;
use think\Db;
use think\db\Query;

/**
 * @title 用户余额管理模型
 * @desc 用户余额管理模型
 * @use app\common\model\ClientCreditModel
 */
class ClientCreditModel extends Model
{
    public $isAdmin = true;

	protected $name = 'client_credit';
    
    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'type'              => 'string',
        'amount'            => 'float',
        'credit'            => 'float',
        'notes'             => 'string',
        'order_id'          => 'int',
        'host_id'           => 'int',
        'client_id'         => 'int',
        'admin_id'          => 'int',
        'create_time'       => 'int',
        'rel_id'            => 'int',
        'client_notes'      => 'string',
        'is_unfreeze'       => 'int',
    ];

	/**
     * 时间 2022-05-11
     * @title 用户余额变更记录列表
     * @desc 用户余额变更记录列表
     * @author theworld
     * @version v1
     * @param int param.start_time - 开始时间，时间戳(s)
     * @param int param.end_time - 结束时间，时间戳(s)
     * @param string param.type - 类型:人工Artificial,充值Recharge,应用至订单Applied,超付Overpayment,少付Underpayment,退款Refund 
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,username,phone,email
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 记录
     * @return int list[].id - 记录ID 
     * @return string list[].type - 类型:人工Artificial,充值Recharge,应用至订单Applied,超付Overpayment,少付Underpayment,退款Refund  
     * @return string list[].amount - 金额
     * @return string list[].credit - 变更后余额
     * @return string list[].notes - 备注 
     * @return int list[].create_time - 变更时间 
     * @return int list[].admin_id - 管理员ID 
     * @return string list[].admin_name - 管理员名称 
     * @return int count - 记录总数
     * @return string page_total_amount - 当前页金额总计
     * @return string total_amount - 金额总计
     */
    public function clientCreditList($param)
    {
        // 获取当前应用
        $app = app('http')->getName();
        if($app=='home'){
            $param['id'] = get_client_id();
            if(empty($param['id'])){
                return ['list' => [], 'count' => 0];
            }
        }else{
            $param['id'] = isset($param['id']) ? intval($param['id']) : 0;
        }
        $param['keywords'] = $param['keywords'] ?? '';
        $param['order_id'] = $param['order_id'] ?? 0;
        $param['type'] = $param['type'] ?? '';
        $param['start_time'] = $param['start_time'] ?? 0;
        $param['end_time'] = $param['end_time'] ?? 0;

        $where = function (Query $query) use($param) {
            $query->where('cc.client_id', $param['id']);
            if(isset($param['keywords']) && trim($param['keywords']) !== ''){
                $query->where('cc.id|cc.notes', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['order_id'])){
                $query->where('cc.order_id', $param['order_id']);
            }
            if(!empty($param['type'])){
                $query->where('cc.type', $param['type']);
            }
            if(!empty($param['start_time']) && !empty($param['end_time'])){
                $query->where('cc.create_time', '>=', $param['start_time'])->where('cc.create_time', '<=', $param['end_time']);
            }   
        };

        $count = $this->alias('cc')
            ->field('cc.id')
            ->where($where)
            ->count();

    	$credits = $this->alias('cc')
            ->field('cc.id,cc.type,cc.amount,cc.credit,cc.notes,cc.create_time,cc.admin_id,a.name admin_name')
            ->leftjoin('admin a', 'a.id=cc.admin_id')
            ->where($where)
    		->limit($param['limit'])
    		->page($param['page'])
            ->order('create_time', 'desc')
    		->select()
            ->toArray();
    	foreach ($credits as $key => $credit) {
            $credits[$key]['amount'] = amount_format($credit['amount']); // 处理金额格式
            $credits[$key]['credit'] = amount_format($credit['credit']); // 处理金额格式
            $credits[$key]['admin_name'] = $credit['admin_name'] ?? '';  // 处理为null时的管理员名称
            if($app=='home'){
                if(in_array($credit['type'], ['Overpayment', 'Underpayment'])){
                    $credits[$key]['type'] = 'Recharge';
                }
                unset($credits[$key]['admin_id']);
            }
    	}

        // 当前页总计
        $pageTotalAmount = amount_format(array_sum(array_column($credits, 'amount')));
        $totalAmount = $this
                ->alias('cc')
                ->where($where)
                ->column('amount');
        $totalAmount = amount_format(array_sum($totalAmount));

    	return ['list' => $credits, 'count' => $count, 'page_total_amount'=>$pageTotalAmount, 'total_amount'=>$totalAmount];
    }


    /**
     * 时间 2022-05-11
     * @title 更改用户余额
     * @desc 更改用户余额
     * @author theworld
     * @version v1
     * @param int param.id - 用户ID required
     * @param string param.type - 类型recharge充值deduction扣费 required
     * @param float param.amount - 金额 required
     * @param string param.notes - 备注
     * @param int param.order_id - 订单ID
     * @param int param.host_id - 产品ID
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateClientCredit($param)
    {
        // 验证用户ID
        $client = ClientModel::find($param['id']);
        if (empty($client)){
            return ['status'=>400, 'msg'=>lang('client_is_not_exist')];
        }
        $adminId = get_admin_id();

        $client['credit'] +=  $param['amount'];

        if($client['credit']<0){
            return ['status'=>400, 'msg'=>lang('insufficient_credit_deduction_failed')];
        }

        try {
            $this->create([
                'type' => $param['type'],
                'amount' => $param['amount'],
                'credit' => $client['credit'],  // 记录当前余额
                'notes' => $param['notes'] ?? '',
                'client_id' => $param['id'],
                'order_id' => $param['order_id'] ?? 0,
                'host_id' => $param['host_id'] ?? 0,
                'admin_id' => $adminId,
                'create_time' => time()
            ]);
            ClientModel::update(['credit' => $client['credit'], 'update_time' => time()], ['id' => $param['id']]);
        } catch (\Exception $e) {
            // 回滚事务
            return ['status' => 400, 'msg' => lang('client_credit_fail')];
        }

        hook('after_client_credit_edit',['id'=>$param['id'],'customfield'=>$param['customfield']??[]]);

        return ['status' => 200, 'msg' => lang('client_credit_success')];
    }

    public function freeze($param)
    {
        $this->startTrans();

        try {
            $client = ClientModel::find($param['client_id']);
            if (empty($client)){
                throw new \Exception(lang('client_is_not_exist'));
            }
            $client['credit'] -=  $param['freeze_amount'];
            if($client['credit']<0){
                throw new \Exception(lang('freeze_amount_exceeds_balance'));
            }
            $this->create([
                'type' => 'Freeze',
                'amount' => -$param['freeze_amount'],
                'credit' => $client['credit'], // 记录当前余额
                'notes' => $param['notes']??'',
                'client_id' => $param['client_id'],
                'admin_id' => get_admin_id(),
                'create_time' => time(),
                'client_notes' => $param['client_notes'],
            ]);
            ClientModel::update([
                'credit' => $client['credit'],
                'freeze_credit'=>$client['freeze_credit']+$param['freeze_amount'],
                'update_time' => time()
            ], ['id' => $param['client_id']]);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }

        $currencyPrefix = configuration('currency_prefix');
        system_notice([
            'name'                  => 'client_credit_freeze',
            'email_description'     => lang('client_credit_freeze'),
            'sms_description'       => lang('client_credit_freeze'),
            'task_data' => [
                'client_id' => $param['client_id'],
                'template_param'=>[
                    'frozen_amount' => $currencyPrefix . $param['freeze_amount'],
                    'frozen_reason' => $param['client_notes'],
                ],
            ]
        ]);

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    public function unfreeze($param)
    {
        $this->startTrans();

        try {
            $client = ClientModel::find($param['client_id']);
            if (empty($client)){
                throw new \Exception(lang('client_is_not_exist'));
            }
            $credit = $client['credit'];
            $freezeCredit = $client['freeze_credit'];
            if ($freezeCredit==0){
                throw new \Exception(lang('no_freeze_credit'));
            }
            $insertAll = [];
            $time = time();
            $totalUnfreezeAmount = 0;
            foreach ($param['credit_ids'] as $creditId){
                $clientCredit = $this->where('client_id', $param['client_id'])
                    ->where('type','Freeze')
                    ->where('id',$creditId)
                    ->find();
                if (empty($clientCredit)){
                    throw new \Exception(lang('credit_ids_not_found'));
                }
                $amount = -$clientCredit['amount'];
                $credit += $amount;
                $freezeCredit -= $amount;
                $client['credit'] = $client['credit']+$amount;
                $insertAll[] = [
                    'type' => 'Unfreeze',
                    'amount' => $amount,
                    'credit' => $client['credit'], // 记录当前余额
                    'notes' => ($param['notes']??'') . " 关联ID#{$creditId}",
                    'client_id' => $param['client_id'],
                    'admin_id' => get_admin_id(),
                    'create_time' => $time,
                    'rel_id' => $creditId,
                ];
                $clientCredit->save([
                    'is_unfreeze' => 1,
                ]);
                $totalUnfreezeAmount += $amount;
            }
            $this->insertAll($insertAll);
            ClientModel::update([
                'credit' => $credit,
                'freeze_credit'=>$freezeCredit,
                'update_time' => time()
            ], ['id' => $param['client_id']]);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
        $currencyPrefix = configuration('currency_prefix');
        system_notice([
            'name'                  => 'client_credit_unfreeze',
            'email_description'     => lang('client_credit_unfreeze'),
            'sms_description'       => lang('client_credit_unfreeze'),
            'task_data' => [
                'client_id' => $param['client_id'],
                'template_param'=>[
                    'frozen_amount' => $currencyPrefix . $totalUnfreezeAmount,
                ],
            ]
        ]);
        return ['status' => 200, 'msg' => lang('success_message')];
    }

    public function freezeList($param)
    {
        if ($this->isAdmin){
            $field = 'cc.id,cc.amount,cc.notes,cc.create_time,a.nickname';
        }else{
            $field = 'cc.id,cc.amount,cc.client_notes,cc.create_time';
        }
        $list = $this->alias('cc')
            ->leftJoin('admin a','cc.admin_id = a.id')
            ->where('cc.client_id', $this->isAdmin?$param['client_id']:get_client_id())
            ->where('cc.type','Freeze')
            ->where('cc.is_unfreeze',0)
            ->field($field)
            ->withAttr('amount', function ($value) {
                return abs($value);
            })
            ->order('cc.id', 'desc')
            ->select()
            ->toArray();
        return [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'list' => $list,
                'count' => count($list),
            ]
        ];
    }

    public function creditRemind($param)
    {
        // 全局开关关闭时，不允许设置余额提醒
        $balanceNoticeShow = (int)configuration('balance_notice_show');
        if ($balanceNoticeShow !== 1){
            return ['status' => 400, 'msg' => lang('balance_notice_show_closed')];
        }
        $client = ClientModel::find(get_client_id());
        if (empty($client['phone']) && empty($client['email'])){
            return ['status' => 400, 'msg' => lang('client_phone_email_empty')];
        }
        $this->startTrans();
        try {
            $client->save([
                'credit_remind' => $param['credit_remind'],
                'credit_remind_amount' => $param['credit_remind_amount'],
            ]);
            active_log(lang('credit_remind_update',['{username}'=>$client['username'],'{credit_remind}'=>$param['credit_remind'],'{credit_remind_amount}'=>$param['credit_remind_amount']]),'client',get_client_id());
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
        return ['status' => 200, 'msg' => lang('success_message')];
    }
}
