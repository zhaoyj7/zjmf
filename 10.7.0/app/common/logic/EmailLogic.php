<?php 
namespace app\common\logic;

use think\facade\Db;
use app\admin\model\PluginModel;
use app\common\model\NoticeSettingModel;
use app\common\model\EmailTemplateModel;
use app\admin\model\EmailLogModel;
use app\common\model\ConfigurationModel;
use app\common\model\ProductModel;
use app\common\model\ProductNoticeGroupModel;

/**
 * @title 邮件发送
 * @desc 邮件发送
 * @use app\common\logic\EmailLogic
 */
class EmailLogic
{
    /**
     * 时间 2022-05-19
     * @title 基础发送
     * @desc 基础发送
     * @author xiong
     * @version v1
     * @param array
     * @param string param.email_name - 邮件插件标识名 required 
     * @param string param.email - 邮箱 required
     * @param string param.subject - 邮件标题 required
     * @param string param.message - 邮件内容 required
     * @param string param.attachments - 邮件附件
     * @param array param.template_param - 参数替换
     */
    public function sendBase($param)
    {
		$data = [
			'email' => $param['email'],
			'subject' => $this->paramStrReplace($param['subject'],$param['template_param']),
			'message' => $this->paramStrReplace($param['message'],$param['template_param']),
			'attachments' => $param['attachments'],
			'email_name' => $param['email_name'],
		];		
		if(empty($param['email'])){
			return ['status'=>400, 'msg'=>lang('email_cannot_be_empty'),'data'=>$data];//邮箱不能为空
		}
		$mail_methods = $this->mailMethods('send',$data);
		if($mail_methods['status'] == 'success'){
			return ['status'=>200, 'msg'=>lang('send_mail_success'), 'data'=>$data];//邮件发送成功
		}else{
			return ['status'=>400, 'msg'=>lang('send_mail_error').' : '.$mail_methods['msg'], 'data'=>$data];//邮件发送失败
		}
    }
    /**
     * 时间 2022-05-19
     * @title 发送
     * @desc 发送
     * @author xiong
     * @version v1
     * @param string param.email - 邮箱 required
     * @param string param.name - 动作名称 required
     * @param int param.client_id - 客户id
     * @param int param.host_id - 主机id
     * @param int param.order_id - 订单id
     * @param array param.template_param - 参数
     */
    public function send($param)
    {
		//读取发送动作
		$index_setting = (new NoticeSettingModel())->indexSetting($param['name']);
		if(empty($index_setting['name'])){
			return ['status'=>400, 'msg'=>lang('send_wrong_action_name')];//动作名称错误
		}
		// 开关仅影响发送给用户
		if($index_setting['email_enable'] == 0 && empty($param['admin_id']) ){
			return ['status'=>400, 'msg'=>lang('send_mail_action_not_enabled')];//邮件动作发送未开启
		}
		if(isset($param['email_name'])){
			$index_setting['email_name'] = $param['email_name'];
		}
		if(empty($index_setting['email_name'])){
			return ['status'=>400, 'msg'=>lang('send_mail_interface_not_set')];//邮件发送接口未设置
		}
		if(isset($param['email_template'])){
			$index_setting['email_template'] = $param['email_template'];
		}
		if($index_setting['email_template'] == 0){
			return ['status'=>400, 'msg'=>lang('send_mail_template_not_set')];//邮件发送模板未设置
		}			
		$index_mail_template = (new EmailTemplateModel())->indexEmailTemplate($index_setting['email_template']);
		if(!isset($index_mail_template->id)){
			return ['status'=>400, 'msg'=>lang('email_template_is_not_exist')];//邮件模板不存在
		}

		$productNoticeGroupAction = config('idcsmart.product_notice_group_action');
		//自定义产品通知
		if(in_array($param['name'], $productNoticeGroupAction)){
			if(empty($param['host_id'])){
				return ['status'=>400, 'msg'=>lang('id_error')];
			}
			$index_host = Db::name('host')->field('id,product_id,server_id,name,notes,first_payment_amount,renew_amount,billing_cycle,billing_cycle_name,billing_cycle_time,active_time,due_time,status,client_id,suspend_reason')->find($param['host_id']);
			if(empty($index_host)){
				return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
			}
			// 仅影响发送给用户
			if(empty($param['admin_id'])){
				$ProductNoticeGroupModel = new ProductNoticeGroupModel();
				$noticeEnable = $ProductNoticeGroupModel->getProductNoticeStatus([
					'type'          => 'email',
					'product_id'    => $index_host['product_id'],
					'act'           => $param['name'],
				]);
				if(empty($noticeEnable)){
					return ['status'=>400, 'msg'=>lang('send_mail_action_not_enabled')];
				}
			}
		}
		
		$NoticeTemplateVarLogic = new NoticeTemplateVarLogic();
		$template_param = $NoticeTemplateVarLogic->format([
			'order_id' => $param['order_id'] ?? 0,
			'host_id' => $param['host_id'] ?? 0,
			'client_id' => $param['client_id'] ?? 0,
			'template_param' => $param['template_param'] ?? [],
		]);
		if($NoticeTemplateVarLogic->error){
			return ['status'=>400, 'msg'=>$NoticeTemplateVarLogic->error];
		}
		$index_client = $NoticeTemplateVarLogic->client;
		$client_id = $NoticeTemplateVarLogic->clientId;

		if(empty($param['email']) && !empty($index_client['email'])){
			$param['email'] = $index_client['email'];
		}
		
		$data = [
			'email' => $param['email']??'',
			'subject' => $index_mail_template['subject'],
			'message' => $index_mail_template['message'],
			'attachments' => $index_mail_template['attachments'],
			'email_name' => $index_setting['email_name'],
			'template_param' => $template_param,
		];

		if(isset($param['admin_id'])){
			$admin_id = $param['admin_id'];
		}
		if(!isset($admin_id)){
			if(isset($index_client) && empty($index_client['receive_email']) && $param['name']!='code'){
				return ['status'=>400, 'msg'=>lang('email_cancel_send')];//邮件取消发送
			}
		}

		// 发送前hook
		$send = true;
		$result_hook = hook('before_email_send', ['param' => $param, 'data' => $data]); // name:动作名称send:true发送false取消发送data:发送数据
		$result_hook = array_values(array_filter($result_hook ?? []));
		foreach ($result_hook as $key => $value) {
			if(isset($value['send']) && $value['send']===false){
				$send = false;
				break;
			}
		}
		if($send===false){
			return ['status'=>400, 'msg'=>lang('email_cancel_send')];//邮件取消发送
		}
		
		$send_result = $this->sendBase($data);	
		$log = [       
            'subject' => $send_result['data']['subject'] ?? '',
            'message' => $send_result['data']['message'] ?? '',
            'status' => ($send_result['status'] == 200)?1:0,
			'fail_reason' =>($send_result['status'] == 200)?'':$send_result['msg'],			
			'to' =>$data['email'],			
            'rel_id' => $admin_id ?? $client_id,
            'type' => isset($admin_id) ? 'admin' : 'client',
			'ip' =>  empty($param['ip'])?'':$param['ip'],
			'port' =>  empty($param['port'])?'':$param['port'],			
        ];
		(new EmailLogModel())->createEmailLog($log);
		unset($send_result['data']);	
		return $send_result;
    }
	//邮件接口调用
	private function mailMethods($cmd,$param)
	{
		//邮件接口判断
		$mail = (new PluginModel())->pluginList(['module'=>'mail']);				
		$mail_status = array_column($mail['list'],"status","name");
		if(empty($mail_status[$param['email_name']])){
			return ['status'=>400, 'msg'=>lang('send_mail_interface_is_not_exist')];//邮件接口不存在
		}else if($mail_status[$param['email_name']]==0){
			return ['status'=>400, 'msg'=>lang('send_mail_interface_is_disabled')];//邮件接口已禁用
		}else if($mail_status[$param['email_name']]==3){
			return ['status'=>400, 'msg'=>lang('send_mail_interface_not_installed_')];//邮件接口未安装
		}
		//提交到接口
		
		$class = get_plugin_class($param['email_name'],'mail');
		if (!class_exists($class)) {
			return ['status'=>400, 'msg'=>lang('send_mail_interface_is_not_exist')];//邮件接口不存在
		}
		$methods = get_class_methods($class)?:[];
		if(!in_array($cmd,$methods)){
			return ['status'=>400, 'msg'=>lang('send_mail_interface_not_supported')];//邮件接口不支持
		}
		$mail_class = new $class();
		$config = $mail_class->getConfig();
		//发送
		$data=[
			'email' => $param['email'],
			'subject' => $param['subject'],
			'content' => htmlspecialchars_decode($param['message']),
			'attachments' => $param['attachments'],
			'config' => $config?:[],
		];
		return $mail_class->$cmd($data);
		
		
	}
	//参数替换
	private function paramStrReplace($content,$param)
	{
		foreach($param as $k=>$v){
		$content=str_replace('{'.$k.'}',$v,$content);
		}
		return $content;
	}
}