<?php 
namespace app\common\logic;

use app\common\model\ProductModel;
use app\common\model\HostModel;
use app\common\model\ClientModel;
use app\common\model\OrderModel;
use app\common\model\HostIpModel;
use app\common\model\HostAdditionModel;
use app\common\model\ConfigurationModel;

/**
 * @title 通知模板变量逻辑
 * @desc  通知模板变量逻辑
 * @use  app\common\logic\NoticeTemplateVarLogic
 */
class NoticeTemplateVarLogic
{
    // 用户ID
    public $clientId = 0;

    // 错误信息
    public $error = '';

    // 用户信息
    public $client = [];

    /**
     * @时间 2025-03-11
     * @title 获取通知模板变量
     * @desc  获取通知模板变量
     * @author hh
     * @version v1
     * @param   array param - 参数
     * @param   int param.order_id - 订单ID,可获取订单/用户相关变量
     * @param   int param.host_id - 产品ID,可获取产品/用户相关变量
     * @param   int param.client_id - 用户ID,可获取用户相关变量
     */
    public function format($param): array
    {
        $system = $this->getSystemVar();
        $order = $this->getOrderVar($param);
        $host = $this->getHostVar($param);
        $client = $this->getClientVar($param);
		
        // 动作额外参数
		$templateParam = $param['template_param'] ?? [];
		$data = array_merge($system,$order,$host,$client,$templateParam);

        return $data;
    }

    /**
     * @时间 2025-03-11
     * @title 获取系统相关变量
     * @desc  获取系统相关变量
     * @author hh
     * @version v1
     * @return  string system_website_name - 网站名称
     * @return  string system_website_url - 网站地址
     * @return  string send_time - 发送时间
     */
    protected function getSystemVar(): array
    {
        $setting = ['website_name','website_url'];
		$configuration=configuration($setting);

		$data = [
			'system_website_name'=>$configuration['website_name'],
			'system_website_url'=>$configuration['website_url'],
			'send_time'=>date('Y-m-d H:i:s'),//发送时间
		];
        return $data;
    }

    /**
     * @时间 2025-03-11
     * @title 获取订单相关变量
     * @desc  获取订单相关变量
     * @author hh
     * @version v1
     * @param   array param - 参数
     * @param   int param.order_id - 订单ID require
     * @param   int param.client_id - 用户ID
     * @return  string order_id - 订单ID
     * @return  string order_create_time - 订单创建时间
     * @return  string order_amount - 订单金额
     * @return  string pay_time - 支付时间
     */
    protected function getOrderVar($param): array
    {
        $data = [];
        if(!empty($param['order_id'])){
            $OrderModel = new OrderModel();
			$order = $OrderModel
                    ->field('id,type,amount,create_time,status,gateway_name gateway,credit,client_id,is_recycle,pay_time')
                    ->where('id', $param['order_id'])
                    ->find();
			if(empty($order) || $order['is_recycle']){
                $this->error = lang('order_is_not_exist');
				return $data;
			}
			$data = [
				'order_id' => $order['id'],
				'order_create_time' => $order['create_time'],
				'order_amount' => $order['amount'],
				'pay_time' => $order['pay_time'] > 0 ? date('Y-m-d H:i:s', $order['pay_time']) : '',
			];
			if(!empty($param['client_id'])){
				$this->clientId = $param['client_id'];
			}else{
				$this->clientId = $order['client_id'];
			}
		}
        return $data;
    }

    /**
     * @时间 2025-03-11
     * @title 获取产品相关变量
     * @desc  获取产品相关变量
     * @author hh
     * @version v1
     * @param   array param - 参数
     * @param   int param.host_id - 产品ID require
     * @param   int param.client_id - 用户ID
     * @return  string product_id - 产品ID
     * @return  string product_name - 商品名称-产品标识
     * @return  string product_marker_name - 产品标识
     * @return  string product_first_payment_amount - 订购金额
     * @return  string product_renew_amount - 续费金额
     * @return  string product_billing_cycle - 计费周期
     * @return  string product_active_time - 开通时间
     * @return  string product_due_time - 到期时间
     * @return  string product_suspend_reason - 暂停原因
     * @return  string renewal_first - X天后到期第一次续费提醒
     * @return  string renewal_second - X天后到期第二次续费提醒
     * @return  string dedicate_ip - 主IP
     * @return  string product_username - 产品用户名(远程用户名)
     * @return  string product_password - 产品密码(远程密码)
     * @return  string product_port - 产品端口
     * @return  string product - 商品名称
     */
    protected function getHostVar($param): array
    {
        $data = [];
        if(!empty($param['host_id'])){
            $HostModel = new HostModel();
			$host = $HostModel
                        ->field('id,product_id,server_id,name,notes,first_payment_amount,renew_amount,billing_cycle,billing_cycle_name,billing_cycle_time,active_time,due_time,status,client_id,suspend_reason')
                        ->where('id', $param['host_id'])
                        ->find();
			if(empty($host)){
                $this->error = lang('host_is_not_exist');
				return $data;
			}
            $ProductModel = new ProductModel();
            $product = $ProductModel
                    ->field('id,name')
                    ->where('id', $host['product_id'])
                    ->find();
			if(empty($product)){
                $this->error = lang('product_is_not_exist');
				return $data;
			}

            // 获取产品IP
            $HostIpModel = new HostIpModel();
			$hostIp = $HostIpModel
                    ->field('dedicate_ip')
                    ->where('host_id', $param['host_id'])
                    ->find();

            // 获取产品附加配置
            $HostAddtionModel = new HostAdditionModel();
            $hostAddtion = $HostAddtionModel
                        ->where('host_id', $param['host_id'])
                        ->find();

			//获取自动化设置
            $ConfigurationModel = new ConfigurationModel();
			$config = $ConfigurationModel->cronList();

			$data = [
				'product_id' => $host['id'],
				'product_name' => $product['name'] .'-'.$host['name'],
				'product_marker_name' => $host['name'],
				'product_first_payment_amount' => $host['first_payment_amount'],
				'product_renew_amount' => $host['renew_amount'],
				'product_binlly_cycle' => $host['billing_cycle_name'],
				'product_active_time' => date("Y-m-d H:i:s", $host['active_time']),
				'product_due_time' => format_due_time_for_noitce($host['due_time']),
				'product_suspend_reason' => $host['suspend_reason'],
				'renewal_first' => $config['cron_due_renewal_first_day'],
				'renewal_second' => $config['cron_due_renewal_second_day'],
				'dedicate_ip' => $hostIp['dedicate_ip'] ?? '',
				'product_username' => $hostAddtion['username'] ?? '',
				'product_password' => $hostAddtion['password'] ?? '',
				'product_port' => $hostAddtion['port'] ?? '',
				'product' => $product['name'],
			];	
			if(!empty($param['client_id'])){
				$this->clientId = $param['client_id'];
			}else{
				$this->clientId = $host['client_id'];
			}		
		}

        return $data;
    }

    /**
     * @时间 2025-03-11
     * @title 获取用户相关变量
     * @desc  获取用户相关变量
     * @author hh
     * @version v1
     * @param   array param - 参数
     * @param   int param.client_id - 用户ID
     * @return  string client_id - 用户ID
     * @return  string client_register_time - 注册时间
     * @return  string client_username - 用户名
     * @return  string client_email - 邮箱
     * @return  string client_phone - 手机号
     * @return  string client_company - 公司
     * @return  string client_last_login_time - 最后登录时间
     * @return  string client_last_login_ip - 最后登录IP
     * @return  string account - 账户(用户名不存在则显示手机/邮箱)
     */ 
    protected function getClientVar($param): array
    {
        $data = [];
        $param['client_id'] = !empty($param['client_id']) ? $param['client_id'] : $this->clientId;
        if(!empty($param['client_id'])){
            $ClientModel = new ClientModel();

			$client = $ClientModel
                    ->field('id,username,email,phone_code,phone,company,country_id,address,language,notes,status,create_time register_time,last_login_time,last_login_ip,credit,receive_sms,receive_email')
                    ->where('id', $param['client_id'])
                    ->find();
			if(empty($client)){
                $this->error = lang('client_is_not_exist');
				return [];
			}
            $this->client = $client;
			if($client['username'] !== ''){
				$account = $client['username'];
			}else if($client['phone']){
				$account = $client['phone_code'].$client['phone'];
			}else if($client['email']){
				$account = $client['email'];
            }
			
			$data = [
				'client_id' => $client['id'],
				'client_register_time' => date("Y-m-d H:i:s", $client['register_time']),
				'client_username' => $client['username'],
				'client_email' => $client['email'],
				'client_phone' => $client['phone_code'].$client['phone'],
				'client_company' => $client['company'],
				'client_last_login_time' => date("Y-m-d H:i:s", $client['last_login_time']),
				'client_last_login_ip' => $client['last_login_ip'],
				'account' => $account,
			];
			$this->clientId = $param['client_id'];
		}

        return $data;
    }

}