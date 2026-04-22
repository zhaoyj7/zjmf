<?php
namespace app\admin\controller;

use app\common\model\SmsTemplateModel;
use app\admin\validate\NoticeSmsValidate;

/**
 * @title 短信模板管理
 * @desc 短信模板管理
 * @use app\admin\controller\NoticeSmsController
 */
class NoticeSmsController extends AdminBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new NoticeSmsValidate();
    }

    /**
     * 时间 2022-05-17
     * @title 获取短信模板
     * @desc 获取短信模板
     * @url /admin/v1/notice/sms/:name/template
     * @method GET
     * @author xiong
     * @version v1
     * @param string name - desc:短信接口标识名称 validate:required
     * @return array list - desc:短信模板列表
     * @return string list[].id - desc:短信模板ID
     * @return string list[].template_id - desc:短信接口模板ID
     * @return string list[].type - desc:模板类型 0大陆 1国际
     * @return string list[].sms_name - desc:接口标识名称
     * @return string list[].title - desc:模板标题
     * @return string list[].content - desc:模板内容
     * @return string list[].notes - desc:备注
     * @return string list[].status - desc:状态 0未提交审核 1审核中 2通过审核 3未通过审核
     * @return string list[].notice_setting_name - desc:默认动作名称
     */
	public function templateList(){
		# 合并分页参数
        $param = $this->request->param();
        
        //实例化模型类
        $SmsTemplateModel = new SmsTemplateModel();

        //获取短信模板列表
        $data = $SmsTemplateModel->smsTemplateList($param['name']);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}

	/**
     * 时间 2022-05-17
     * @title 获取单个短信模板
     * @desc 获取单个短信模板
     * @url /admin/v1/notice/sms/:name/template/:id
     * @method GET
     * @author xiong
     * @version v1
     * @param string name - desc:短信接口标识名称 validate:required
     * @param int id - desc:短信模板ID validate:required
     * @return string template_id - desc:模板ID
     * @return string type - desc:模板类型 0大陆 1国际
     * @return string title - desc:模板标题
     * @return string content - desc:模板内容
     * @return string notes - desc:备注
     * @return string status - desc:状态 0未提交审核 1审核中 2通过审核 3未通过审核
     * @return string product_url - desc:应用场景
     * @return string remark - desc:场景说明
     * @return string notice_setting_name - desc:默认动作名称
     */
	public function index(){
		//接收参数
        $param = $this->request->param();
        
        //实例化模型类
        $SmsTemplateModel = new SmsTemplateModel();

        //获取短信模板
        $sms = $SmsTemplateModel->indexSmsTemplate($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $sms,
        ];
        return json($result);
	}
	/**
     * 时间 2022-05-17
     * @title 更新模板审核状态
     * @desc 更新模板审核状态
     * @url /admin/v1/notice/sms/:name/template/status
     * @method GET
     * @author xiong
     * @version v1
     * @param string name - desc:短信接口标识名称 validate:required
     */
	public function status(){
		//接收参数
        $param = $this->request->param();
        
        //实例化模型类
        $SmsTemplateModel = new SmsTemplateModel();

        //获取短信模板
        $result = $SmsTemplateModel->statusSmsTemplate($param);
        return json($result);
	}	
	/**
     * 时间 2022-05-17
     * @title 提交审核短信模板
     * @desc 提交审核短信模板
     * @url /admin/v1/notice/sms/:name/template/audit
     * @method POST
     * @author xiong
     * @version v1
     * @param string name - desc:短信接口标识名称 validate:required
     * @param array ids - desc:模板ID数组 validate:required
     * @param int resubmit - desc:已通过模板是否重新提交 0否 1是 validate:optional
     */
	public function audit(){
		//接收参数
        $param = $this->request->param();
        
        //实例化模型类
        $SmsTemplateModel = new SmsTemplateModel();

        //获取短信模板
        $result = $SmsTemplateModel->auditSmsTemplate($param);

        return json($result);
	}	
	/**
     * 时间 2022-05-17
     * @title 创建短信模板
     * @desc 创建短信模板
     * @url /admin/v1/notice/sms/:name/template
     * @method POST
     * @author xiong
     * @version v1
     * @param string name - desc:短信接口标识名称 validate:required
     * @param string template_id - desc:模板ID validate:optional
     * @param string type - desc:模板类型 0大陆 1国际 validate:required
     * @param string title - desc:模板标题 validate:optional
     * @param string content - desc:模板内容 validate:optional
     * @param string notes - desc:备注 validate:optional
     * @param string status - desc:状态 0未提交审核 2通过审核 3未通过审核 validate:optional
     * @param string product_url - desc:应用场景 阿里云短信必填 validate:optional
     * @param string remark - desc:场景说明 阿里云短信必填 validate:optional
     * @param string notice_setting_name - desc:默认动作名称 validate:optional
     */
	public function create(){
		//接收参数
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $SmsTemplateModel = new SmsTemplateModel();
        
        //修改产品
        $result = $SmsTemplateModel->createSmsTemplate($param);

        return json($result);
	}
	/**
     * 时间 2022-05-17
     * @title 修改短信模板
     * @desc 修改短信模板
     * @url /admin/v1/notice/sms/:name/template/:id
     * @method PUT
     * @author xiong
     * @version v1
     * @param string name - desc:短信接口标识名称 validate:required
     * @param int id - desc:短信模板ID validate:required
     * @param string template_id - desc:模板ID validate:optional
     * @param string type - desc:模板类型 0大陆 1国际 validate:optional
     * @param string title - desc:模板标题 validate:optional
     * @param string content - desc:模板内容 validate:optional
     * @param string notes - desc:备注 validate:optional
     * @param string status - desc:状态 0未提交审核 2通过审核 3未通过审核 validate:optional
     * @param string product_url - desc:应用场景 阿里云短信必填 validate:optional
     * @param string remark - desc:场景说明 阿里云短信必填 validate:optional
     * @param string notice_setting_name - desc:默认动作名称 validate:optional
     */
	public function update(){
		//接收参数
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $SmsTemplateModel = new SmsTemplateModel();
        
        //修改产品
        $result = $SmsTemplateModel->updateSmsTemplate($param);

        return json($result);
	}

	/**
     * 时间 2022-05-17
     * @title 删除短信模板
     * @desc 删除短信模板
     * @url /admin/v1/notice/sms/:name/template/:id
     * @method DELETE
     * @author xiong
     * @version v1
     * @param string name - desc:短信接口标识名称 validate:required
     * @param int id - desc:短信模板ID validate:required
     */
	public function delete(){
		//接收参数
        $param = $this->request->param();

        //实例化模型类
        $SmsTemplateModel = new SmsTemplateModel();
        
        //删除产品
        $result = $SmsTemplateModel->deleteSmsTemplate($param['id']);

        return json($result);
	}
	/**
     * 时间 2022-05-17
     * @title 测试短信模板
     * @desc 测试短信模板
     * @url /admin/v1/notice/sms/:name/template/:id/test
     * @method GET
     * @author xiong
     * @version v1
     * @param string name - desc:短信接口标识名称 validate:required
     * @param int id - desc:短信模板ID validate:required
     * @param string phone_code - desc:手机区号 validate:optional
     * @param string phone - desc:手机号 validate:required
     */
	public function test(){
		//接收参数
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('test')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $SmsTemplateModel = new SmsTemplateModel();
        
        $result = $SmsTemplateModel->test($param);

        return json($result);
	}
}