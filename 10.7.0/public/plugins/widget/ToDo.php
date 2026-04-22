<?php
namespace widget;

use app\common\lib\Widget;
use app\admin\model\PluginModel;
use app\common\model\HostModel;
use app\common\model\OrderModel;

class ToDo extends Widget
{
    protected $weight = 100;

    protected $columns = 2;

    protected $language = [
        'zh-cn' => [
            'title' => '待办事项',
            'pending_work_orders' => '待处理工单',
            'pending_refunds' => '待处理退款',
            'pending_real_name_authentication' => '待处理实名',
            'pending_withdrawals' => '待处理提现',
            'to_be_confirmed_recommend' => '待确认推介',
            'pending_invoices' => '待处理发票',
            'pending_host_num' => '开通中产品数量',
            'pending_offline_transfer' => '待处理线下转账',
            'host_failed_action_count' => '待手动处理产品',
            'host_expiring_count' => '即将到期产品',
            'pending_sms_sign' => '待处理签名审核',
            'pending_e_contract' => '待审核合同',
        ],
        'en-us' => [
            'title' => 'To Do',
            'pending_work_orders' => 'Pending work orders',
            'pending_refunds' => 'Pending refunds',
            'pending_real_name_authentication' => 'Pending real name real',
            'pending_withdrawals' => 'Pending withdrawals',
            'to_be_confirmed_recommend' => 'To be confirmed recommendation',
            'pending_invoices' => 'Pending invoices',
            'pending_host_num' => 'Number of products in use',
            'pending_offline_transfer' => 'Pending offline transfer',
            'host_failed_action_count' => 'Products to be manually processed',
            'host_expiring_count' => 'Products about to expire',
            'pending_sms_sign' => 'Pending signature review',
            'pending_e_contract' => 'Contracts pending review',
        ],
        'zh-hk' => [
            'title' => '待辦事項',
            'pending_work_orders' => '待處理工單',
            'pending_refunds' => '待處理退款',
            'pending_real_name_authentication' => '待處理實名',
            'pending_withdrawals' => '待處理提現',
            'to_be_confirmed_recommend' => '待確認推介',
            'pending_invoices' => '待處理發票',
            'pending_host_num' => '開通中產品數量',
            'pending_offline_transfer' => '待處理線下轉帳',
            'host_failed_action_count' => '待手動處理產品',
            'host_expiring_count' => '即將到期產品',
            'pending_sms_sign' => '待處理簽章審核',
            'pending_e_contract' => '待審核合同',
        ],
    ];

    public function __construct(){
        $this->title = $this->lang('title');
    }

    public function getData()
    {
        $PluginModel = new PluginModel();
        $addons = $PluginModel->plugins('addon');
        $addons = array_column($addons['list'], 'name');

        $data = [];

        if(in_array('TicketPremium', $addons)){
            $TicketPremiumStatusModel = new \addon\ticket_premium\model\TicketPremiumStatusModel();
            $status = $TicketPremiumStatusModel->where('status', 0)->column('id');
            $TicketPremiumModel = new \addon\ticket_premium\model\TicketPremiumModel();
            $count = $TicketPremiumModel->whereIn('status', $status)->count();
            $data['pending_work_orders'] = $count;
            $data['pending_work_orders_url'] = request()->domain().'/'.DIR_ADMIN.'/plugin/ticket_premium/index.htm';
        }else if(in_array('IdcsmartTicket', $addons)){
            $IdcsmartTicketStatusModel = new \addon\idcsmart_ticket\model\IdcsmartTicketStatusModel();
            $status = $IdcsmartTicketStatusModel->where('status', 0)->column('id');
            $IdcsmartTicketModel = new \addon\idcsmart_ticket\model\IdcsmartTicketModel();
            $data['pending_work_orders'] = $IdcsmartTicketModel->whereIn('status', $status)->count();
            $data['pending_work_orders_url'] = request()->domain().'/'.DIR_ADMIN.'/plugin/idcsmart_ticket/index.htm';
        }

        if(in_array('IdcsmartRefund', $addons)){
            $IdcsmartRefundModel = new \addon\idcsmart_refund\model\IdcsmartRefundModel();
            $data['pending_refunds'] = $IdcsmartRefundModel->where('status', 'Pending')->count();
            $data['pending_refunds_url'] = request()->domain().'/'.DIR_ADMIN.'/plugin/idcsmart_refund/index.htm';
        }

        if(in_array('IdcsmartCertification', $addons)){
            $CertificationLogModel = new \addon\idcsmart_certification\model\CertificationLogModel();
            $idsPerson = $CertificationLogModel->field('max(id) as ids')
                ->where('type',1)
                ->group('client_id')
                ->order('id','desc')
                ->select()
                ->toArray();

            $idsPerson && $idsPerson = array_column($idsPerson, 'ids');

            $idsCompany =  $CertificationLogModel->field('max(id) as ids')
                ->whereIn('type',[2,3])
                ->group('client_id')
                ->order('id','desc')
                ->select()
                ->toArray();
            $idsCompany && $idsCompany = array_column($idsCompany,'ids');

            $ids = array_merge($idsPerson,$idsCompany);

            $data['pending_real_name_authentication'] = $CertificationLogModel->whereIn('status', [3, 4])->whereIn('id', $ids)->count();
            $data['pending_real_name_authentication_url'] = request()->domain().'/'.DIR_ADMIN.'/plugin/idcsmart_certification/index.htm';
        }

        if(in_array('IdcsmartWithdraw', $addons)){
            $IdcsmartWithdrawModel = new \addon\idcsmart_withdraw\model\IdcsmartWithdrawModel();
            $data['pending_withdrawals'] = $IdcsmartWithdrawModel->whereIn('status', ['0', '1'])->count();
            $data['pending_withdrawals_url'] = request()->domain().'/'.DIR_ADMIN.'/plugin/idcsmart_withdraw/index.htm';
        }

        if(in_array('IdcsmartRecommend', $addons)){
            $RecommendModel = new \addon\idcsmart_recommend\model\RecommendModel();
            $data['to_be_confirmed_recommend'] = $RecommendModel->where('status', 'Pending')->count();
            $data['to_be_confirmed_recommend_url'] = request()->domain().'/'.DIR_ADMIN.'/plugin/idcsmart_recommend/index.htm';
        }

        if(in_array('IdcsmartInvoice', $addons)){
            $IdcsmartInvoiceModel = new \addon\idcsmart_invoice\model\IdcsmartInvoiceModel();
            $data['pending_invoices'] = $IdcsmartInvoiceModel->whereIn('status', ['pending', 'wait_send'])->count();
            $data['pending_invoices_url'] = request()->domain().'/'.DIR_ADMIN.'/plugin/idcsmart_invoice/index.htm';
        }

        if(in_array('IdcsmartSmsSign', $addons)){
            $IdcsmartSmsSignModel = new \addon\idcsmart_sms_sign\model\IdcsmartSmsSignModel();
            $data['pending_sms_sign'] = $IdcsmartSmsSignModel->where('status', 0)->count();
            $data['pending_sms_sign_url'] = request()->domain().'/'.DIR_ADMIN.'/plugin/idcsmart_sms_sign/index.htm?status=0';
        }

        if(in_array('EContract', $addons)){
            $EContractModel = new \addon\e_contract\model\EContractModel();
            $data['pending_e_contract'] = $EContractModel->where('status', 'review')->count();
            $data['pending_e_contract_url'] = request()->domain().'/'.DIR_ADMIN.'/plugin/e_contract/index.htm?status=review';
        }

        $HostModel = new HostModel();
        $data['pending_host_num'] = $HostModel->where('status', 'Pending')->where('is_sub',0)->count();
        $data['pending_host_num_url'] = request()->domain().'/'.DIR_ADMIN.'/host.htm?type=status&keywords=Pending';

        $OrderModel = new OrderModel();
        $data['pending_offline_transfer'] = $OrderModel->where('status', 'WaitReview')->where('is_recycle',0)->count();
        $data['pending_offline_transfer_url'] = request()->domain().'/'.DIR_ADMIN.'/order.htm';

        $data['host_failed_action_count'] = $HostModel->failedActionCount();
        $data['host_failed_action_count_url'] = request()->domain().'/'.DIR_ADMIN.'/host.htm?tab=failed';

        $data['host_expiring_count'] = $HostModel->expiringCount();
        $data['host_expiring_count_url'] = request()->domain().'/'.DIR_ADMIN.'/host.htm?tab=expiring';

    	return $data;
    }

    public function output(){
    	$data = $this->getData();
        $pendingTitle = $this->lang('title');
        $pendingWorkOrders = $this->lang('pending_work_orders');
        $pendingRefunds = $this->lang('pending_refunds');
        $pendingRealNameAuthentication = $this->lang('pending_real_name_authentication');
        $pendingWithdrawals = $this->lang('pending_withdrawals');
        $toBeConfirmedRecommend = $this->lang('to_be_confirmed_recommend');
        $pendingInvoices = $this->lang('pending_invoices');
        $pendingHostNum = $this->lang('pending_host_num');
        $pendingOfflineTransfer = $this->lang('pending_offline_transfer');
        $hostFailedActionCount = $this->lang('host_failed_action_count');
        $hostExpiringCount = $this->lang('host_expiring_count');
        $pendingSmsSign = $this->lang('pending_sms_sign');
        $pendingEContract = $this->lang('pending_e_contract');
        $adminPath = '/'.DIR_ADMIN;

        $content = '';
        if(isset($data['pending_work_orders'])){
            $content .= <<<SUBHTML
                <a class="todo-item" href="{$data['pending_work_orders_url']}"><div class="todo-title"><div class="todo-img todo-img-1"></div><span>{$pendingWorkOrders}</span></div><div class="todo-num">{$data['pending_work_orders']}</div></a>
SUBHTML;
        }
        if(isset($data['pending_refunds'])){
            $content .= <<<SUBHTML
                <a class="todo-item" href="{$data['pending_refunds_url']}"><div class="todo-title"><div class="todo-img todo-img-2"></div><span>{$pendingRefunds}</span></div><div class="todo-num">{$data['pending_refunds']}</div></a>
SUBHTML;
        }
        if(isset($data['pending_real_name_authentication'])){
            $content .= <<<SUBHTML
                <a class="todo-item" href="{$data['pending_real_name_authentication_url']}?isIndex=1"><div class="todo-title"><div class="todo-img todo-img-3"></div><span>{$pendingRealNameAuthentication}</span></div><div class="todo-num">{$data['pending_real_name_authentication']}</div></a>
SUBHTML;
        }
        if(isset($data['pending_withdrawals'])){
            $content .= <<<SUBHTML
                <a class="todo-item" href="{$data['pending_withdrawals_url']}"><div class="todo-title"><div class="todo-img todo-img-4"></div><span>{$pendingWithdrawals}</span></div><div class="todo-num">{$data['pending_withdrawals']}</div></a>
SUBHTML;
        }
        if(isset($data['to_be_confirmed_recommend'])){
            $content .= <<<SUBHTML
                <a class="todo-item" href="{$data['to_be_confirmed_recommend_url']}"><div class="todo-title"><div class="todo-img todo-img-5"></div><span>{$toBeConfirmedRecommend}</span></div><div class="todo-num">{$data['to_be_confirmed_recommend']}</div></a>
SUBHTML;
        }
        if(isset($data['pending_invoices'])){
            $content .= <<<SUBHTML
                <a class="todo-item" href="{$data['pending_invoices_url']}"><div class="todo-title"><div class="todo-img todo-img-6"></div><span>{$pendingInvoices}</span></div><div class="todo-num">{$data['pending_invoices']}</div></a>
SUBHTML;
        }
        if(isset($data['pending_host_num'])){
            $content .= <<<SUBHTML
                <a class="todo-item" href="{$data['pending_host_num_url']}"><div class="todo-title"><div class="todo-img todo-img-7"></div><span>{$pendingHostNum}</span></div><div class="todo-num">{$data['pending_host_num']}</div></a>
SUBHTML;
        }
        if(isset($data['pending_offline_transfer'])){
            $content .= <<<SUBHTML
                <a class="todo-item" href="{$data['pending_offline_transfer_url']}?isIndex=1"><div class="todo-title"><div class="todo-img todo-img-8"></div><span>{$pendingOfflineTransfer}</span></div><div class="todo-num">{$data['pending_offline_transfer']}</div></a>
SUBHTML;
        }
        if(isset($data['host_failed_action_count'])){
            $content .= <<<SUBHTML
                <a class="todo-item" href="{$data['host_failed_action_count_url']}"><div class="todo-title"><div class="todo-img todo-img-9"></div><span>{$hostFailedActionCount}</span></div><div class="todo-num">{$data['host_failed_action_count']}</div></a>
SUBHTML;
        }
        if(isset($data['host_expiring_count'])){
            $content .= <<<SUBHTML
                <a class="todo-item" href="{$data['host_expiring_count_url']}"><div class="todo-title"><div class="todo-img todo-img-10"></div><span>{$hostExpiringCount}</span></div><div class="todo-num">{$data['host_expiring_count']}</div></a>
SUBHTML;
        }
        if(isset($data['pending_sms_sign'])){
            $content .= <<<SUBHTML
                <a class="todo-item" href="{$data['pending_sms_sign_url']}"><div class="todo-title"><div class="todo-img todo-img-11"></div><span>{$pendingSmsSign}</span></div><div class="todo-num">{$data['pending_sms_sign']}</div></a>
SUBHTML;
        }
        if(isset($data['pending_e_contract'])){
            $content .= <<<SUBHTML
                <a class="todo-item" href="{$data['pending_e_contract_url']}"><div class="todo-title"><div class="todo-img todo-img-12"></div><span>{$pendingEContract}</span></div><div class="todo-num">{$data['pending_e_contract']}</div></a>
SUBHTML;
        }

        return <<<HTML
        <div class="todo-box">
            <div class="todo-name">{$pendingTitle}</div>
            <div class="todo-main">{$content}</div>
        </div>
HTML;

    }



}
