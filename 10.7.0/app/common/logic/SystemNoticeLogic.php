<?php 

namespace app\common\logic;

use app\common\model\ProductModel;
use app\common\model\HostModel;
use app\common\model\ClientModel;
use app\common\model\OrderModel;
use app\common\model\NoticeSettingModel;
use app\common\model\EmailTemplateModel;
use app\common\model\SmsTemplateModel;
use app\common\model\ProductNoticeGroupModel;

/**
 * @title 系统通知逻辑
 * @desc  系统通知逻辑
 * @use  app\common\logic\SystemNoticeLogic
 */
class SystemNoticeLogic
{

    // 通知动作
    protected $name = '';
    
    // 通知任务数据
    protected $taskData = [];

    // 邮件任务数据
    protected $emailData = [];

    // 短信任务数据
    protected $smsData = [];

    // 邮件开关
    protected $emailEnable = 0;

    // 短信开关
    protected $smsEnable = 0;

    // 发送目标用户
    protected $user = [];

    // 支持的通知方式,NULL代表所有动作都可以
    protected $noticeType = NULL;

    /**
     * @时间 2024-12-12
     * @title 系统通知
     * @desc  系统通知
     * @author hh
     * @version v1
     * @param   array param - 参数
     * @param   string param.name - 发送动作 require
     * @param   string param.email_description - 邮件任务描述,为空不发邮件
     * @param   string param.sms_description - 短信任务描述,为空不发短信
     * @param   array param.task_data - 任务数据
     * @param   int param.task_data.order_id - 获取订单/用户相关参数
     * @param   int param.task_data.host_id - 获取产品/用户相关参数
     * @param   int param.task_data.client_id - 获取用户相关参数
     * @param   array param.task_data.template_param - 模板变量
     */
    public function __construct(array $param)
    {
        $param['task_data'] = $this->formatTaskData($param);

        $param['email'] = [];
        if(!empty($param['email_description'])){
            $param['email']['type'] = 'email';
            $param['email']['description'] = $param['email_description'];
            $param['email']['task_data'] = $param['task_data'];
        }
        $param['sms'] = [];
        if(!empty($param['sms_description'])){
            $param['sms']['type'] = 'sms';
            $param['sms']['description'] = $param['sms_description'];
            $param['sms']['task_data'] = $param['task_data'];
        }

        $this->name = $param['name'];
        $this->taskData = $param['task_data'];
        $this->emailData = $param['email'];
        $this->smsData = $param['sms'];
    }

    /**
     * @时间 2024-12-12
     * @title 初始化任务数据
     * @desc  初始化任务数据
     * @author hh
     * @version v1
     * @param   array param - 参数
     * @param   string param.name - 发送动作 require
     * @param   array param.task_data - 任务数据
     * @param   array param.task_data.template_param - 模板变量
     */
    protected function formatTaskData($param)
    {
        $param['task_data'] = $param['task_data'] ?? [];
        $param['task_data']['name'] = $param['name'];

        // 添加动作触发事件
        $param['task_data']['template_param'] = $param['task_data']['template_param'] ?? [];
        $param['task_data']['template_param']['action_trigger_time'] = date('Y-m-d H:i:s');

        return $param['task_data'];
    }

    /**
     * @时间 2024-12-12
     * @title 执行系统通知
     * @desc  执行系统通知
     * @author hh
     * @version v1
     */
    public function exec()
    {
        $user = $this->getSendUser();

        // 需要发送邮件或短信,才获取短信全局通知设置
        if(!empty($this->emailData) || !empty($this->smsData)){
            $noticeSetting = $this->getNoticeSetting([
                'name'      => $this->name,
                'host_id'   => $this->taskData['host_id'] ?? 0,
            ]);
    
            $this->emailEnable = $this->emailEnable($noticeSetting);
            $this->smsEnable = $this->smsEnable($noticeSetting, $user['phone_code']);
        }
        // var_dump($user);
        if($this->emailEnable == 1){
            // 用户接收
            if($user['receive_email'] == 1){
                $this->sendEmail($this->emailData);
            }
        }
        if($this->smsEnable == 1){
            // 用户接收
            if($user['receive_sms'] == 1){
                $this->sendSms($this->smsData);
            }
        }
        
        // 系统通知钩子,可以追加通知方式
        hook('system_notice', [
            'name'                  => $this->name,
            'task_data'             => $this->taskData,
            'notice_type'           => $this->noticeType,
        ]);
    }

    /**
     * @时间 2024-12-12
     * @title 添加发送邮件任务
     * @desc  添加发送邮件任务
     * @author hh
     * @version v1
     * @param   array param - 通知参数
     * @param   string param.description - 任务描述 require
     * @param   array param.task_data - 任务数据 require
     */
    public function sendEmail($param)
    {
        $this->addTask('email', $param);
    }

    /**
     * @时间 2024-12-12
     * @title 添加发送短信任务
     * @desc  添加发送短信任务
     * @author hh
     * @version v1
     * @param   array param - 通知参数
     * @param   string param.description - 任务描述 require
     * @param   array param.task_data - 任务数据 require
     */
    public function sendSms($param)
    {
        $this->addTask('sms', $param);
    }

    /**
     * @时间 2024-12-12
     * @title 获取通知设置
     * @desc  获取通知设置
     * @author hh
     * @version v1
     * @param   string param.name - 通知动作标识 require
     * @param   int param.host_id - 产品ID,用于检查全局商品设置
     * @return  string name - 动作名称 
     * @return  int sms_global_name - 短信国际接口名称 
     * @return  int sms_global_template - 短信国际接口模板id 
     * @return  string sms_name - 短信国内接口名称 
     * @return  int sms_template - 短信国内接口模板id 
     * @return  int sms_enable - 启用状态，0禁用,1启用 
     * @return  string email_name - 邮件接口名称 
     * @return  int email_template - 邮件接口模板id 
     * @return  int email_enable - 启用状态，0禁用,1启用
     */
    public function getNoticeSetting($param)
    {
        // 读取发送动作
        $NoticeSettingModel = new NoticeSettingModel();

        $noticeSetting = $NoticeSettingModel->indexSetting($param['name']);
        if($noticeSetting instanceof NoticeSettingModel){
            $noticeSetting = $noticeSetting->toArray();
        }

        $productNoticeSetting = $this->globalNoticeSetting($param);
        $noticeSetting['sms_enable'] = ($noticeSetting['sms_enable'] == 1 && $productNoticeSetting['sms_enable'] == 1) ? 1 : 0;
        $noticeSetting['email_enable'] = ($noticeSetting['email_enable'] == 1 && $productNoticeSetting['email_enable'] == 1) ? 1 : 0;
        
        return $noticeSetting;
    }

    /**
     * @时间 2024-12-12
     * @title 获取商品全局通知设置
     * @desc  获取商品全局通知设置
     * @author hh
     * @version v1
     * @param   string param.name - 通知动作标识 require
     * @param   int param.host_id - 产品ID,用于检查全局商品设置
     */
    public function globalNoticeSetting($param)
    {
        $productNoticeGroupAction = config('idcsmart.product_notice_group_action');

        // 商品全局通知设置
        $noticeSetting = [];
        $noticeSetting['email_enable'] = 1;
        $noticeSetting['sms_enable'] = 1;
        // 商品全局设置动作
        if(in_array($this->name, $productNoticeGroupAction)){
            if(empty($param['host_id'])){
                $noticeSetting['email_enable'] = 0;
                $noticeSetting['sms_enable'] = 0;
            }else{
                $HostModel = new HostModel();
                $host = $HostModel
                    ->field('id,product_id')
                    ->find($param['host_id']);
                if(empty($host)){
                    $noticeSetting['email_enable'] = 0;
                    $noticeSetting['sms_enable'] = 0;
                }else{
                    // 把商品ID放入任务数据中
                    $this->taskData['product_id'] = $host['product_id'];

                    $ProductNoticeGroupModel = new ProductNoticeGroupModel();
                    $type = $ProductNoticeGroupModel->getProductNoticeEnableType([
                        'product_id'    => $host['product_id'],
                        'act'           => $this->name,
                    ]);
                    if(is_array($type)){
                        $noticeSetting['email_enable'] = in_array('email', $type);
                        $noticeSetting['sms_enable'] = in_array('sms', $type);
                    }
                    $this->noticeType = $type;
                }
            }
        }
        return $noticeSetting;
    }

    /**
     * @时间 2024-12-12
     * @title 邮件是否启用
     * @desc  邮件是否启用
     * @author hh
     * @version v1
     * @param   array $noticeSetting - noticeSetting数据 require
     * @return  int
     */
    public function emailEnable($noticeSetting)
    {
        $enable = 0;
        // 邮件开关
        if($noticeSetting['email_enable'] == 1){
            if($noticeSetting['email_enable'] == 0){
                return $enable;
            }
            if(isset($this->emailData['email_name'])){
                $noticeSetting['email_name'] = $this->emailData['email_name'];
            }
            // 没有邮件发送方式
            if(empty($noticeSetting['email_name'])){
                return $enable;
            }
            if(isset($this->emailData['email_template'])){
                $noticeSetting['email_template'] = $this->emailData['email_template'];
            }
            // 没有邮件模板
            if($noticeSetting['email_template'] == 0){
                return $enable;
            }

            // 先不验证这个
            // $EmailTemplateModel = new EmailTemplateModel();
            // $emailTemplate = $EmailTemplateModel->find($noticeSetting['email_template']);

            // if(empty($emailTemplate)){
            //     return $enable;
            // }
            $enable = 1;
        }
        return $enable;
    }

    /**
     * @时间 2024-12-12
     * @title 短信是否启用
     * @desc  短信是否启用
     * @author hh
     * @version v1
     * @param   array $noticeSetting - noticeSetting数据 require
     * @return  int
     */
    public function smsEnable($noticeSetting, $phoneCode = 0)
    {
        $enable = 0;
        // 邮件开关
        if($noticeSetting['sms_enable'] == 1){
            // if($phoneCode == '86' || empty($phoneCode)){
            //     // 国内短信发送接口未设置
            //     if(empty($noticeSetting['sms_name'])){
            //         return $enable;
            //     }
            //     //国内短信发送模板未设置
            //     if($noticeSetting['sms_template'] == 0){
            //         return $enable;
            //     }

                // $SmsTemplateModel = new SmsTemplateModel();
                // $smsTemplate = $SmsTemplateModel->indexSmsTemplate(['name'=>$noticeSetting['sms_name'],'id'=>$noticeSetting['sms_template']]);
                // if(!isset($smsTemplate->id) || $smsTemplate['type'] != 0){
                //     return $enable;
                // }
                // // 未通过审核
                // if($smsTemplate['status'] != 2){
                //     return $enable;
                // }
            // }else{
                // 国际短信发送接口未设置
                // if(empty($noticeSetting['sms_global_name'])){
                //     return $enable;
                // }
                // // 国际短信发送模板未设置
                // if($noticeSetting['sms_global_template'] == 0){
                //     return $enable;
                // }

                // $SmsTemplateModel = new SmsTemplateModel();
                // $smsTemplate = $SmsTemplateModel->indexSmsTemplate(['name'=>$noticeSetting['sms_global_name'],'id'=>$noticeSetting['sms_global_template']]);
                // if(!isset($smsTemplate->id) || $smsTemplate['type'] !=1 ){
                //     return $enable;
                // }   
                // if($noticeSetting['status'] != 2){
                //     return $enable;
                // }
            // }

            $enable = 1;
        }
        return $enable;
    }

    /**
     * @时间 2024-12-12
     * @title 获取发送用户
     * @desc  获取发送用户
     * @author hh
     * @version v1
     * @return  int id - 用户ID
     * @return  string type - 用户类型(client=用户,admin=管理员)
     * @return  string phone - 手机号
     * @return  int phone_code - 区号
     * @return  string email - 邮箱
     * @return  int receive_sms - 用户是否接收短信(0=不接收,1=接收)
     * @return  int receive_email - 用户是否接收邮箱(0=不接收,1=接收)
     */
    public function getSendUser(): array
    {
        // 发送给用户
        // if(empty($this->taskData['client_id'])){
        //     if(!empty($this->taskData['order_id'])){
        //         $OrderModel = new OrderModel();
        //         $this->taskData['client_id'] = $OrderModel
        //                                     ->where('id', $this->taskData['order_id'])
        //                                     ->value('client_id');   
        //     }else if(!empty($this->taskData['host_id'])){
        //         $HostModel = new HostModel();
        //         $this->taskData['client_id'] = $HostModel
        //                                     ->where('id', $this->taskData['host_id'])
        //                                     ->value('client_id');
        //     }
        // }
        $data = [
            'id'            => 0,
            'type'          => 'client',
            'phone'         => '',
            'phone_code'    => 0,
            'email'         => '',
            'receive_sms'   => 0,
            'receive_email' => 0,
        ];
        // 如果有用户
        if(!empty($this->taskData['client_id'])){
            $data['id'] = $this->taskData['client_id'];
            $data['receive_email'] = 1;
            $data['receive_sms'] = 1;
            // 去掉提升速度
            // $ClientModel = new ClientModel();
            // $client = $ClientModel
            //         ->field('id,phone,phone_code,email,notice_open,notice_method,receive_sms,receive_email')
            //         ->where('id', $this->taskData['client_id'])
            //         ->find();
            // if(!empty($client)){
            //     $data = $client->toArray();
            //     $data['type'] = 'client';

            //     // 接收通知开关
            //     if($client['notice_open'] == 1){
            //         $data['receive_email'] = in_array($client['notice_method'], ['all','email']) && $client['receive_email'] == 1 ? 1 : 0;
            //         $data['receive_sms'] = in_array($client['notice_method'], ['all','sms']) && $client['receive_sms'] == 1 ? 1 : 0;
            //     }else{
            //         $data['receive_email'] = 0;
            //         $data['receive_sms'] = 0;
            //     }

            //     unset($client['notice_open'],$client['notice_method']);
            // }
        }
        // 处理指定邮箱
        if(!empty($this->taskData['email'])){
            $data['email'] = $this->taskData['email'];
            $data['receive_email'] = 1;
        }
        // 处理指定手机号
        if(!empty($this->taskData['phone'])){
            $data['phone'] = $this->taskData['phone'];
            $data['phone_code'] = $this->taskData['phone_code'] ?? '86';
            $data['receive_sms'] = 1;
        }
        return $data;
    }

    /**
     * @时间 2025-02-28
     * @title 添加任务
     * @desc  添加任务
     * @author hh
     * @version v1
     * @param   string type - 任务类型(email=邮件,sms=短信) require
     * @param   array param - 任务参数 require
     */
    private function addTask($type, $param)
    {
        if(!empty($param)){
            $param['type'] = $type;
            $param['task_data']['ip'] = request()->ip();
            $param['task_data']['port'] = request()->remotePort();
            add_task($param);
        }
    }

}


