<?php 
namespace app\common\logic;
use think\facade\Db;
use app\admin\model\PluginModel;
use app\common\model\NoticeSettingModel;
use app\common\model\SmsTemplateModel;
use app\common\model\CountryModel;
use app\admin\model\SmsLogModel;
use app\common\model\ConfigurationModel;
use app\common\model\ProductModel;
use app\common\model\ProductNoticeGroupModel;
/**
 * @title 短信发送逻辑类
 * @desc 短信发送逻辑类
 * @use app\common\logic\SmsLogic
 */
class SmsLogic
{
    /**
     * 时间 2022-05-19
     * @title 基础发送
     * @desc 基础发送
     * @author xiong
     * @version v1
     * @param array
     * @param string param.sms_name - 短信插件标识名 
     * @param string param.phone_code - 手机区号 
     * @param string param.phone - 手机号
     * @param string param.content - 短信内容
     * @param array param.template_param - 模板要替换的参数
     */
    public function sendBase($param)
    {
        $param['phone_code'] = str_replace('+','',$param['phone_code']);

        $data=[
			'content' => $param['content'],
			'template_param' => $param['template_param'],
			'sms_name' => $param['sms_name'],
            'template_id' => $param['template_id']??0,
		];
        if($param['phone_code'] == '86' || empty($param['phone_code'])){	
			$data['mobile'] = $param['phone'];
			$sms_methods = $this->smsMethods('sendCnSms',$data);
		}else{
			$data['mobile'] = '+'.$param['phone_code'].$param['phone'];
            $data['phone_code'] = $param['phone_code'];
			$sms_methods = $this->smsMethods('sendGlobalSms',$data);
		}

		if(!empty($param['phone_code'])){		
			$country = (new CountryModel())->checkPhoneCode($param['phone_code']);
			if(!$country){
				return ['status'=>400, 'msg'=>lang('send_sms_area_code_error'), 'data'=>$sms_methods];//区号错误
			}
		}else{
			$param['phone_code'] = '';
		}
		
		if(empty($param['phone'])){
			return ['status'=>400, 'msg'=>lang('sms_phone_number_cannot_be_empty'), 'data'=>$sms_methods];//手机号不能为空
		}

		if($sms_methods['status'] == 'success'){
			return ['status'=>200, 'msg'=>lang('send_sms_success'), 'data'=>$sms_methods];//短信发送成功
		}else{
			return ['status'=>400, 'msg'=>lang('send_sms_error').' : '.$sms_methods['msg'], 'data'=>$sms_methods];//短信发送失败
		}
    }
    /**
     * 时间 2022-05-19
     * @title 发送
     * @desc 发送
     * @author xiong
     * @version v1
     * @param string param.phone_code - 手机区号 
     * @param string param.phone - 手机号 
     * @param string param.name - 动作名称
     * @param int param.client_id - 客户id
     * @param int param.host_id - 产品id
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
		if($index_setting['sms_enable'] == 0){
			return ['status'=>400, 'msg'=>lang('send_sms_action_not_enabled')];//短信动作发送未开启
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
					'type'          => 'sms',
					'product_id'    => $index_host['product_id'],
					'act'           => $param['name'],
				]);
				if(empty($noticeEnable)){
					return ['status'=>400, 'msg'=>lang('send_sms_action_not_enabled')];
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

		if(!empty($index_client)){
			$param['phone_code'] = $index_client['phone_code'];
			$param['phone'] = $index_client['phone'];
		}
		
		if(!empty($param['phone_code'])){
			$param['phone_code'] = str_replace('+','',$param['phone_code']);
		}

		if($param['phone_code'] == '86' || empty($param['phone_code'])){
			if(empty($index_setting['sms_name'])){
				return ['status'=>400, 'msg'=>lang('send_sms_interface_not_set_domestic')];//国内短信发送接口未设置
			}
			if($index_setting['sms_template'] == 0){
				return ['status'=>400, 'msg'=>lang('send_sms_template_not_set_domestic')];//国内短信发送模板未设置
			}			
			$index_sms_template = (new SmsTemplateModel())->indexSmsTemplate(['name'=>$index_setting['sms_name'],'id'=>$index_setting['sms_template']]);
			if(!isset($index_sms_template->id) || $index_sms_template['type']!=0){
				return ['status'=>400, 'msg'=>lang('send_sms_template_is_not_exist_domestic')];//国内短信模板不存在
			}
			if ($index_sms_template['status'] != 2){
				return ['status'=>400, 'msg'=>lang('sms_template_review_before_sending')];
			}	
		}else{
			if(empty($index_setting['sms_global_name'])){
				return ['status'=>400, 'msg'=>lang('send_sms_interface_not_set_global')];//国际短信发送接口未设置
			}
			if($index_setting['sms_global_template'] == 0){
				return ['status'=>400, 'msg'=>lang('send_sms_template_not_set_global')];//国际短信发送模板未设置
			}
			$index_sms_template = (new SmsTemplateModel())->indexSmsTemplate(['name'=>$index_setting['sms_global_name'],'id'=>$index_setting['sms_global_template']]);
			if(!isset($index_sms_template->id) || $index_sms_template['type']!=1){
				return ['status'=>400, 'msg'=>lang('send_sms_template_is_not_exist_global')];//国际短信模板不存在
			}	
			if ($index_sms_template['status'] != 2){
				return ['status'=>400, 'msg'=>lang('sms_template_review_before_sending')];
			}
		}
		
		$data=[
			'phone_code' => $param['phone_code']?:'',
			'phone' => $param['phone'],
			'content' => $index_sms_template['content'],
            'template_id' => $index_sms_template['template_id']??0,
			'template_param' => $template_param,
                'sms_name' => ($param['phone_code'] == '86' || empty($param['phone_code'])) ? $index_setting['sms_name'] : $index_setting['sms_global_name'],
		];

		if(isset($index_client) && empty($index_client['receive_sms']) && $param['name']!='code'){
			return ['status'=>400, 'msg'=>lang('sms_cancel_send')];//短信取消发送
		}

		// 发送前hook
		$send = true;
		$result_hook = hook('before_sms_send', ['param' => $param, 'data' => $data]); // name:动作名称send:true发送false取消发送data:发送数据
		$result_hook = array_values(array_filter($result_hook ?? []));
		foreach ($result_hook as $key => $value) {
			if(isset($value['send']) && $value['send']===false){
				$send = false;
				break;
			}
		}
		if($send===false){
			return ['status'=>400, 'msg'=>lang('sms_cancel_send')];//短信取消发送
		}

		$send_result = $this->sendBase($data);	
		$log = [       
            'phone_code' => $data['phone_code'],
            'phone' => $data['phone'],
            'template_code' => $index_sms_template['template_id'],
            'content' => $send_result['data']['content'] ?? $data['content'],
            'status' => ($send_result['status'] == 200)?1:0,
			'fail_reason' => ($send_result['status'] == 200)?'':$send_result['msg'],			
            'rel_id' => $client_id,
            'type' => 'client',
			'ip' =>  empty($param['ip'])?'':$param['ip'],
			'port' =>  empty($param['port'])?'':$param['port'],
        ];
		(new SmsLogModel())->createSmsLog($log);
		if(isset($send_result['data']))unset($send_result['data']);	
		return $send_result;	
    }
	//短信接口调用
	private function smsMethods($cmd,$param)
	{
		//短信接口判断
		$sms = (new PluginModel())->pluginList(['module'=>'sms']);				
		$sms_type = array_column($sms['list'],"sms_type","name");	
		$sms_status = array_column($sms['list'],"status","name");
		if(strpos($cmd,"sendCn")!==false){
		    if(empty($sms_type[$param['sms_name']])){
				return ['status'=>400, 'msg'=>lang('send_sms_interface_is_not_exist_domestic')];//国内短信接口不存在
			}
			if($sms_status[$param['sms_name']]==0){
				return ['status'=>400, 'msg'=>lang('send_sms_interface_is_disabled_domestic')];//国内短信接口已禁用
			}else if($sms_status[$param['sms_name']]==3){
			    return ['status'=>400, 'msg'=>lang('send_sms_interface_not_installed_domestic')];//国内短信接口未安装
			}
			
		}else if(strpos($cmd,"sendGlobal")!==false){
		    if(empty($sms_type[$param['sms_name']])){
				return ['status'=>400, 'msg'=>lang('send_sms_interface_is_not_exist_global')];//国际短信接口不存在
			}
		    if($sms_status[$param['sms_name']]==0){
				return ['status'=>400, 'msg'=>lang('send_sms_interface_is_disabled_global')];//国际短信接口已禁用
			}else if($sms_status[$param['sms_name']]==3){
			    return ['status'=>400, 'msg'=>lang('send_sms_interface_not_installed_global')];//国际短信接口未安装
			}			
		}
		//提交到接口
		
		$class = get_plugin_class($param['sms_name'],'sms');
		if (!class_exists($class)) {
			return ['status'=>400, 'msg'=>lang('send_sms_interface_is_not_exist')];//短信接口不存在
		}
		$methods = get_class_methods($class)?:[];
		if(!in_array($cmd,$methods)){
			return ['status'=>400, 'msg'=>lang('send_sms_interface_not_supported')];//短信接口不支持
		}
		$sms_class = new $class();
		$config = $sms_class->getConfig();
		//发送
		$data = [
			'mobile' => $param['mobile'],
			'content' => $param['content'],
			'templateParam' => $param['template_param'],
            'template_id' => $param['template_id']??0,
			'config' => $config?:[],
			'product_url' => $param['product_url']??'',
			'remark' => $param['remark']??'',
			'phone_code' => $param['phone_code']??'',
		];
		return $sms_class->$cmd($data);
		
		
	}
	
}