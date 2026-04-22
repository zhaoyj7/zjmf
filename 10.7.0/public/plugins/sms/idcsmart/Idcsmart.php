<?php
namespace sms\idcsmart;

use app\common\lib\Plugin;
use app\common\model\CountryModel;


class Idcsmart extends Plugin
{
    # 基础信息
    public $info = array(
        'name'        => 'Idcsmart',//Demo插件英文名，改成你的插件英文就行了
        'title'       => '智简魔方',
        'description' => '智简魔方官方短信平台接口',
        'status'      => 1,
        'author'      => '智简魔方',
        'version'     => '2.3.2',
        'help_url'     => 'https://my.idcsmart.com/cart/goods.htm?id=337',//申请接口地址
    );

    # 插件安装
    public function install()
    {
		//导入模板
		$smsTemplate= [];
		if (file_exists(__DIR__.'/config/smsTemplate.php')){
            $smsTemplate = require __DIR__.'/config/smsTemplate.php';
        }
		
        return $smsTemplate;
    }

    # 插件卸载
    public function uninstall()
    {
        return true;//卸载成功返回true，失败false
    }

    public function pullSign($params)
    {
        $result=$this->APIHttpRequestCURL('cn',"sign",[],$params['config'],'GET');
        if($result['status']==200){
            $data['status']="success";
            $data['sign']=$result['sign'];
        }else{
            $data['status']="error";
            $data['msg']=$result['msg'];
        }

        return $data;
    }
	
	#获取国内模板
	/*
	返回数据格式
	status//状态只有两种（成功success，失败error）
	template_id//模板的ID,
	template_status//只能是1,2,3（1正在审核，2审核通过，3未通过审核）
	msg//接口返回的错误消息传给msg参数
	[
		'status'=>'success',
		'template'=>[
			'template_id'=>'w34da',
			'template_status'=>2,
		]
	]
	获取失败
	[
		'status'=>'error',
		'msg'=>'模板ID错误',
	]
	*/
	public function getCnTemplate($params)
	{		
		$param['template_id']=trim($params['template_id']);		
		$resultTemplate=$this->APIHttpRequestCURL('cn',"template",$param,$params['config'],'GET');
		if($resultTemplate['status']==200){
			$data['status']="success";
			if($resultTemplate['template']){
				//单个模板
				$data['template']['template_id']=$resultTemplate['template']['template_id'];
				$data['template']['template_status']=$resultTemplate['template']['status'];
			}
		}else{
			$data['status']="error";
			$data['msg']=$resultTemplate['msg'];
		}

		return $data;
	}
	#创建国内模板
	/*
	返回数据格式
	status//状态只有两种（成功success，失败error）
	template_id//模板的ID,
	template_status//只能是1,2,3（1正在审核，2审核通过，3未通过审核）
	msg//接口返回的错误消息传给msg参数
	成功
	[
		'status'=>'success',
		'template'=>[
			'template_id'=>'w34da',
			'template_status'=>2,
		]
	]
	失败
	[
		'status'=>'error',
		'msg'=>'模板ID错误',
	]
	*/
	public function createCnTemplate($params)
	{
		$param['title']=trim($params['title']);
        if(empty($params['config']['sign'])){
            $data['status']="error";
            $data['msg']='短信签名不能为空';
            return $data;
        }
		$param['signature']=$this->templateSign($params['config']['sign']);	
		$param['content']=trim($params['content']);		
        $resultTemplate= $this->APIHttpRequestCURL('cn',"template",$param,$params['config'],'POST');
		if($resultTemplate['status']==200){
			$data['status']="success";
			$data['template']['template_id']=$resultTemplate['template_id'];
			$data['template']['template_status']=1;
		}else{
			$data['status']="error";
			$data['msg']=$resultTemplate['msg'];
		}
		return $data;
	}
	#修改国内模板
	/*
	返回数据格式
	status//状态只有两种（成功success，失败error）
	template_status//只能是1,2,3（1正在审核，2审核通过，3未通过审核）
	msg//接口返回的错误消息传给msg参数
	成功
	[
		'status'=>'success',
		'template'=>[
			'template_status'=>2,
		]
	]
	失败
	[
		'status'=>'error',
		'msg'=>'模板ID错误',
	]
	*/
	public function putCnTemplate($params)
	{
        if(empty($params['config']['sign'])){
            $data['status']="error";
            $data['msg']='短信签名不能为空';
            return $data;
        }
		$param['template_id']=trim($params['template_id']);
		$param['title']=trim($params['title']);	
		$param['signature']=$this->templateSign($params['config']['sign']);	
		$param['content']=trim($params['content']);
        $resultTemplate=  $this->APIHttpRequestCURL('cn',"template",$param,$params['config'],'PUT');
		if($resultTemplate['status']==200){
			$data['status']="success";
			$data['template']['template_status']=1;
		}else{
			$data['status']="error";
			$data['msg']=$resultTemplate['msg'];
		}
		return $data;
	}
	#删除国内模板
	/*
	返回数据格式
	status//状态只有两种（成功success，失败error）
	msg//接口返回的错误消息传给msg参数
	成功
	[
		'status'=>'success',
	]
	失败
	[
		'status'=>'error',
		'msg'=>'模板ID错误',
	]
	*/
	public function deleteCnTemplate($params)
	{
		$param['template_id']=trim($params['template_id']);
        $resultTemplate=$this->APIHttpRequestCURL('cn',"template",$param,$params['config'],'DELETE');
		if($resultTemplate['status']==200){
			$data['status']="success";
		}else{
			$data['status']="error";
			$data['msg']=$resultTemplate['msg'];
		}
		return $data;
	}
	#发送国内短信
	/*
	返回数据格式
	status//状态只有两种（成功success，失败error）
	content//替换参数过后的模板内容
	msg//接口返回的错误消息传给msg参数
	成功
	[
		'status'=>'success',
		'content'=>'success',
	]
	失败
	[
		'status'=>'error',
		'content'=>'error',
		'msg'=>'手机号错误',
	]
	*/
    public function sendCnSms($params)
    {
        $content=$this->templateParam($params['content'],$params['templateParam']);
        if(empty($params['config']['sign'])){
            $data['status']="error";
            $data['content']=$content;
            $data['msg']='短信签名不能为空';
            return $data;
        }
        $param['to']=trim($params['mobile']);
		$param['content']=$this->templateSign($params['config']['sign']).$content;
		$param['template_id']=trim($params['template_id']);
		$param['vars']=$params['templateParam'];
        $resultTemplate= $this->APIHttpRequestCURL('cn',"send",$param,$params['config'],'POST');
        if($resultTemplate['status']==200){
			$data['status']="success";
			$data['content']=$content;
		}else{
			$data['status']="error";
			$data['content']=$content;
            $data['msg']=$this->errorCode()[$resultTemplate['code']??'']??$resultTemplate['msg'];
		}
		return $data;
    }		
	#获取国际模板
	public function getGlobalTemplate($params=[])
	{		
		$param['template_id']=trim($params['template_id']);
        // TODO

		$resultTemplate=$this->APIHttpRequestCURL('global',"template",$param,$params['config'],'GET');
		if($resultTemplate['status']==200){
			$data['status']="success";
			if($resultTemplate['template']){
				//单个模板
				$data['template']['template_id']=$resultTemplate['template']['template_id'];
				$data['template']['template_status']=$resultTemplate['template']['status'];
			}
		}else{
			$data['status']="error";
			$data['msg']=$resultTemplate['msg'];
		}

		return $data;
		

	}
	#创建国际模板
	public function createGlobalTemplate($params=[])
	{
		$param['title']=trim($params['title']);	
		$param['signature']=$this->templateSign($params['config']['global_sign'] ?? '');	
		$param['content']=trim($params['content']);		
        $resultTemplate= $this->APIHttpRequestCURL('global',"template",$param,$params['config'],'POST');
		if($resultTemplate['status']==200){
			$data['status']="success";
			$data['template']['template_id']=$resultTemplate['template_id'];
			$data['template']['template_status']=1;
		}else{
			$data['status']="error";
			$data['msg']=$resultTemplate['msg'];
		}
		return $data;
	}
	#修改国际模板
	public function putGlobalTemplate($params=[])
	{
		$param['template_id']=trim($params['template_id']);
		$param['title']=trim($params['title']);	
		$param['signature']=$this->templateSign($params['config']['global_sign'] ?? '');	
		$param['content']=trim($params['content']);
        $resultTemplate=  $this->APIHttpRequestCURL('global',"template",$param,$params['config'],'PUT');
		if($resultTemplate['status']==200){
			$data['status']="success";
			$data['template']['template_status']=1;
		}else{
			$data['status']="error";
			$data['msg']=$resultTemplate['msg'];
		}
		return $data;
	}
	#删除国际模板
	public function deleteGlobalTemplate($params=[])
	{
		$param['template_id']=trim($params['template_id']);
        $resultTemplate=$this->APIHttpRequestCURL('global',"template",$param,$params['config'],'DELETE');
		if($resultTemplate['status']==200){
			$data['status']="success";
		}else{
			$data['status']="error";
			$data['msg']=$resultTemplate['msg'];
		}
		return $data;
	}
	#发送国际短信
    public function sendGlobalSms($params=[])
    {
    	$content=$this->templateParam($params['content'],$params['templateParam']);
        $param['to']='+'.trim($params['mobile']);
		$param['content']=$this->templateSign($params['config']['global_sign'] ?? '').$content;
		$param['template_id']=trim($params['template_id']);
		$param['vars']=json_encode($params['templateParam']);
        $param['phone_code'] = $params['phone_code']??'';
//        $CountryModel = new CountryModel();
        $param['area'] = '';
        $resultTemplate= $this->APIHttpRequestCURL('global',"send",$param,$params['config'],'POST');
		if($resultTemplate['status']==200){
			$data['status']="success";
			$data['content']=$content;
		}else{
			$data['status']="error";
			$data['content']=$content;
            $data['msg']=$this->errorCode()[$resultTemplate['code']??'']??$resultTemplate['msg'];
		}
		return $data;
    }		
	# 以下函数名自定义

	private function APIHttpRequestCURL($sms_type,$action,$param,$config,$method='POST'){			
		if($sms_type=='cn'){			
			$api='http://api1.idcsmart.com/smsapi.php?action='.$action;
			$headers = array(
				"api:".$config['api'],
				"key:".$config['key'],
				"Content-Type: application/x-www-form-urlencoded"
			);
		}else if($sms_type=="global"){
			$api='http://api1.idcsmart.com/smsglobalapi.php?action='.$action;
			$headers = array(
				"global-api:".($config['global_api'] ?? ''),
				"global-key:".($config['global_key'] ?? ''),
				"Content-Type: application/x-www-form-urlencoded"
			);
		}

		$postfields=http_build_query($param);
		/* var_dump($headers);
		var_dump($postfields);
		exit; */
		if($method!='GET'){
            $ch = curl_init();
            curl_setopt_array($ch, array(
               CURLOPT_URL => $api,
               CURLOPT_RETURNTRANSFER => true,
               CURLOPT_POSTFIELDS => $postfields,
               CURLOPT_CUSTOMREQUEST => strtoupper($method),
               CURLOPT_HTTPHEADER => $headers
            ));
        }else{
            $url=$api."&".$postfields;
            $ch = curl_init($url) ;
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ;
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1) ;
        }
        $output = curl_exec($ch);
        curl_close($ch);
        $output = trim($output, "\xEF\xBB\xBF");
        return json_decode($output,true);
    }

    private function templateParam($content,$templateParam){
        foreach ($templateParam as $key => $para) {
            $content = str_replace('@var(' . $key . ')', $para, $content);//模板中的参数替换
        }       
		$content =preg_replace("/@var\(.*?\)/is","",$content);
        return $content;
    }
	private function templateSign($sign){
		$sign = str_replace("【","",$sign);
		$sign = str_replace("】","",$sign);
		$sign = "【".$sign."】"; 	
        return $sign;
    }

    private function errorCode(){
        // 赛邮错误码
        return [
            '101' => '不正确的 APP ID',
            '102' => '此应用已被禁用，请至 submail > 应用集成 > 应用 页面开启此应用',
            '103' => '未启用的开发者，此应用的开发者身份未验证，请更新您的开发者资料',
            '104' => '此开发者未通过验证或此开发者资料发生更改。请至应用集成页面更新你的开发者资料',
            '105' => '此账户已过期',
            '106' => '此账户已被禁用',
            '107' => 'sign_type（验证模式）必须设置为 MD5 或 SHA1 或 normal',
            '108' => 'signature 参数无效',
            '109' => 'appkey 无效',
            '110' => 'sign_type 错误',
            '111' => '空的 signature 参数',
            '112' => '应用的订阅与退订功能已禁用',
            '113' => '请求的 APPID 已设置 IP 白名单，您的 IP 不在此白名单范围',
            '114' => '该手机号码在账户黑名单中，已被屏蔽',
            '115' => '该手机号码请求超限',
            '116' => '签名错误，该签名已被其他应用使用并已申请固定签名',
            '117' => '该模板已失效，短信模板签名与固定签名不一致或你的账户已取消固签，请联系 SUBMAIL 管理员',
            '118' => '该模板已失效，请联系SUBMAIL管理员',
            '119' => '您不具备使用该API的权限，请联系SUBMAIL管理员',
            '120' => '模板已失效',
            '126' => '短信签名还未报备成功',
            '127' => '短信签名已存在，无需创建新签名',
            '151' => '错误的 UNIX 时间戳',
            '152' => '错误的 UNIX 时间戳，请将请求控制在6秒以内',
            '154' => 'appid 下无可用签名',
            '201' => '未知的 addressbook 模式',
            '202' => '错误的收件人地址',
            '203' => '错误的收件人地址。你所标记的地址薄不包含任何联系人',
            '251' => '错误的收件人地址（message）',
            '252' => '错误的收件人地址（message），地址薄不包含任何联系人',
            '253' => '此联系人已退订你的短信系统',
            '305' => '没有填写项目标记',
            '306' => '无效的项目标记',
            '307' => '错误的 json 格式，请检查 vars 和 links 参数',
            '310' => 'tag参数长度不能超过32个字符',
            '401' => '短信签名不能为空',
            '402' => '请将短信签名控制在40个字符以内',
            '403' => '短信正文不能为空',
            '404' => '请将短信内容（加上签名）控制在1000个字符以内',
            '405' => '依据当地法律法规，短信中不能出现非法词语',
            '406' => '项目标记不能为空',
            '407' => '无效的项目标记',
            '408' => '不能向此联系人或地址簿发送相同短信',
            '409' => '短信项目正在审核中，请稍候再试',
            '410' => 'multi 参数无效',
            '411' => '必须为模板提交签名，并用【】括起，字数2-10字符（不含括号）',
            '412' => '短信签名不能超过10个字符（不含括号）',
            '413' => '短信签名需为2到10个字符（不含括号）',
            '414' => '请提交短信正文',
            '415' => '短信正文不能超过1000个字符',
            '416' => '短信标题不能超过64个字符',
            '417' => '请提交需要更新的模板ID',
            '418' => '尝试更新的模板不存在',
            '419' => '短信正文不能为空',
            '420' => '找不到可匹配的模板',
            '422' => '请控制模板长度在255个字符内',
            '501' => '错误的目标地址簿标识',
            '901' => '今日的发送配额已用尽，请在应用页面开启更多配额',
            '903' => '短信发送许可已用尽或余额不足，请至商店购买',
            '904' => '账户余额已用尽，请充值后重试',
            '905' => '交易类短信余额不足，请充值后重试',
        ];
    }
}