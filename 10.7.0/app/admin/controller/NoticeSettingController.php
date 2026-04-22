<?php
namespace app\admin\controller;

use app\common\model\NoticeSettingModel;
use app\admin\validate\NoticeSettingValidate;

/**
 * @title 通知发送管理
 * @desc 通知发送管理
 * @use app\admin\controller\NoticeSettingController
 */
class NoticeSettingController extends AdminBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new NoticeSettingValidate();
    }

    /**
     * 时间 2022-05-18
     * @title 发送管理
     * @desc 发送管理
     * @url /admin/v1/notice/send
     * @method GET
     * @author xiong
     * @version v1
     * @return array list - desc:发送管理列表
     * @return string list[].name - desc:动作名称
     * @return string list[].sms_global_name - desc:短信国际接口名称
     * @return int list[].sms_global_template - desc:短信国际接口模板ID
     * @return string list[].sms_name - desc:短信国内接口名称
     * @return int list[].sms_template - desc:短信国内接口模板ID
     * @return int list[].sms_enable - desc:短信启用状态 0禁用 1启用
     * @return string list[].email_name - desc:邮件接口名称
     * @return int list[].email_template - desc:邮件接口模板ID
     * @return int list[].email_enable - desc:邮件启用状态 0禁用 1启用
     * @return string list[].type - desc:通知分类标识
     * @return array configuration - desc:默认接口
     * @return string configuration.send_sms - desc:默认国内短信接口
     * @return string configuration.send_sms_global - desc:默认国际短信接口
     * @return string configuration.send_email - desc:默认邮件接口
     * @return string type[].name - desc:分类标识
     * @return string type[].name_lang - desc:分类名称
     */
	public function settingList(){
        
        //实例化模型类
        $NoticeSettingModel = new NoticeSettingModel();

        //获取产品列表
        $data = $NoticeSettingModel->settingList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}

	/**
     * 时间 2022-05-18
     * @title 发送设置
     * @desc 发送设置
     * @url /admin/v1/notice/send
     * @method PUT
     * @author xiong
     * @version v1
     * @param array name - desc:动作名称为键 validate:required
     * @param string name.name - desc:动作名称 validate:required
     * @param string name.sms_global_name - desc:短信国际接口名称 validate:optional
     * @param int name.sms_global_template - desc:短信国际接口模板ID validate:optional
     * @param string name.sms_name - desc:短信国内接口名称 validate:optional
     * @param int name.sms_template - desc:短信国内接口模板ID validate:optional
     * @param int name.sms_enable - desc:短信启用状态 0禁用 1启用 validate:optional
     * @param string name.email_name - desc:邮件接口名称 validate:optional
     * @param int name.email_template - desc:邮件接口模板ID validate:optional
     * @param int name.email_enable - desc:邮件启用状态 0禁用 1启用 validate:optional
     * @param array configuration - desc:默认接口 validate:optional
     * @param string configuration.send_sms - desc:默认国内短信接口 validate:optional
     * @param string configuration.send_sms_global - desc:默认国际短信接口 validate:optional
     * @param string configuration.send_email - desc:默认邮件接口 validate:optional
     */
	public function update(){
		//接收参数
        $param = $this->request->param();

        //参数验证
		/* foreach($param as $params){
			if (!$this->validate->scene('update')->check($params)){
				return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
			}
        } */

        //实例化模型类
        $NoticeSettingModel = new NoticeSettingModel();
        
        //修改产品
        $result = $NoticeSettingModel->updateNoticeSetting($param);

        return json($result);
	}

    /**
     * 时间 2025-11-18
     * @title 发送批量设置
     * @desc 发送批量设置
     * @url /admin/v1/notice/send/batch
     * @method POST
     * @author wyh
     * @version v1
     * @param array name - desc:动作名称数组 validate:required
     * @param string sms_name - desc:国内短信接口名称 validate:optional
     * @param string sms_global_name - desc:国际短信接口名称 validate:optional
     * @param string email_name - desc:邮件接口名称 validate:optional
     */
    public function batchUpdate()
    {
        //接收参数
        $param = $this->request->param();
        //实例化模型类
        $NoticeSettingModel = new NoticeSettingModel();
        //修改产品
        $result = $NoticeSettingModel->batchUpdate($param);

        return json($result);
    }

}