<?php
namespace addon\idcsmart_certification\controller\clientarea;


use addon\idcsmart_certification\logic\IdcsmartCertificationLogic;
use addon\idcsmart_certification\model\CertificationLogModel;
use app\event\controller\PluginBaseController;
use addon\idcsmart_certification\validate\CertificationValidate;

/**
 * @title 实名认证(前台接口)
 * @desc 实名认证(前台接口)
 * @use addon\idcsmart_certification\controller\clientarea\CertificationController
 */
class CertificationController extends PluginBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new CertificationValidate();
        if (!IdcsmartCertificationLogic::getDefaultConfig('certification_open') && request()->action() != 'certificationInfo'){
            echo json_encode(['status'=>400,'msg'=>lang_plugins('certification_is_not_open')]);die;
        }
    }

    /**
     * 时间 2022-9-23
     * @title 获取实名认证信息
     * @desc 获取实名认证信息
     * @url /console/v1/certification/info
     * @method GET
     * @author wyh
     * @version v1
     * @return int certification_open - desc:实名认证是否开启 1开启 0关
     * @return int certification_company_open - desc:企业认证是否开启 1开启 0关
     * @return int certification_upload - desc:是否需要上传证件照 1是 0否
     * @return int certification_uncertified_cannot_buy_product - desc:未认证无法购买产品 1是 0否
     * @return int is_certification - desc:是否实名认证 1是 0否
     * @return object person - desc:个人认证信息
     * @return string person.username - desc:申请人
     * @return string person.company - desc:公司
     * @return string person.card_name - desc:姓名
     * @return string person.card_number - desc:证件号
     * @return int person.create_time - desc:认证时间
     * @return string person.status - desc:状态 1已认证 2未通过 3待审核 4已提交资料
     * @return object company - desc:企业认证信息
     * @return string company.username - desc:申请人
     * @return string company.company - desc:公司
     * @return string company.card_name - desc:姓名
     * @return string company.card_number - desc:证件号
     * @return string company.certification_company - desc:实名认证企业
     * @return string company.company_organ_code - desc:企业代码
     * @return int company.create_time - desc:认证时间
     * @return string company.status - desc:状态 1已认证 2未通过 3待审核 4已提交资料
     */
    public function certificationInfo()
    {
        $param = $this->request->param();

        $CertificationLogModel = new  CertificationLogModel();

        $result = $CertificationLogModel->certificationInfo($param);

        return json($result);
    }

    /**
     * @title 实名认证接口
     * @desc 实名认证接口
     * @url /console/v1/certification/plugin
     * @method GET
     * @author wyh
     * @version v1
     * @return array list - desc:实名认证接口列表
     * @return int list[].id - desc:ID
     * @return string list[].title - desc:名称
     * @return string list[].name - desc:标识
     * @return string list[].url - desc:图片 base64格式
     * @return array list[].certification_type - desc:接口支持的类型 person个人 company企业
     * @return int count - desc:总数
     */
    public function certificationPlugin()
    {
        $data = certification_list();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];

        return json($result);
    }

    /**
     * 时间 2022-9-23
     * @title 获取实名认证自定义字段
     * @desc 获取实名认证自定义字段
     * @url /console/v1/certification/custom_fields
     * @method GET
     * @author wyh
     * @version v1
     * @param string name - desc:实名接口标识 validate:required
     * @param string type - desc:验证类型 person个人 company企业 validate:required
     * @return array custom_fields - desc:自定义字段列表
     * @return string custom_fields.title - desc:名称
     * @return string custom_fields.type - desc:字段类型 text文本 select下拉 file文件
     * @return string custom_fields.options - desc:字段类型为checkbox复选框 select下拉 radio单选时的选项 传键
     * @return string custom_fields.tip - desc:提示
     * @return string custom_fields.required - desc:是否必填 bool类型
     * @return string custom_fields.field - desc:字段名 提交时的键値
     */
    public function certificationCustomfields()
    {
        $param = $this->request->param();

        $CertificationLogModel = new  CertificationLogModel();

        $data = $CertificationLogModel->getCertificationCustomFields($param['name']??'',$param['type']??'');

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'custom_fields' => $data
            ]
        ];

        return json($result);
    }

    /**
     * 时间 2022-9-23
     * @title 个人认证
     * @desc 个人认证
     * @url /console/v1/certification/person
     * @method POST
     * @author wyh
     * @version v1
     * @param string plugin_name - desc:实名接口标识 validate:required
     * @param string card_name - desc:姓名 validate:required
     * @param string card_type - desc:证件类型 1身份证 2港澳通行证 3台湾通行证 4港澳居住证 5台湾居住证 6海外护照 7中国以外驾照 8其他 validate:required
     * @param string card_number - desc:证件号码 validate:required
     * @param string phone - desc:手机号 validate:optional
     * @param string img_one - desc:身份证正面照 调系统上传文件接口获取savename validate:optional
     * @param string img_two - desc:身份证反面照 调系统上传文件接口获取savename validate:optional
     * @param object custom_fields - desc:其他自定义字段 文件类型先调系统上传文件接口获取savename validate:optional
     */
    public function certificationPerson()
    {
        $param = $this->request->param();
        //参数验证
        if (!$this->validate->scene('create_person')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $CertificationLogModel = new  CertificationLogModel();

        $result = $CertificationLogModel->certificationPerson($param);

        return json($result);
    }

    /**
     * 时间 2022-9-24
     * @title 企业认证
     * @desc 企业认证
     * @url /console/v1/certification/company
     * @method POST
     * @author wyh
     * @version v1
     * @param string plugin_name - desc:实名接口标识 validate:required
     * @param string card_name - desc:姓名 validate:required
     * @param string card_type - desc:证件类型 1身份证 2港澳通行证 3台湾通行证 4港澳居住证 5台湾居住证 6海外护照 7中国以外驾照 8其他 validate:required
     * @param string card_number - desc:证件号码 validate:required
     * @param string company - desc:公司 validate:required
     * @param string company_organ_code - desc:公司代码 validate:required
     * @param string phone - desc:手机号 validate:optional
     * @param string img_one - desc:身份证正面照 调系统上传文件接口获取savename validate:optional
     * @param string img_two - desc:身份证反面照 调系统上传文件接口获取savename validate:optional
     * @param string img_three - desc:营业执照 调系统上传文件接口获取savename validate:optional
     * @param object custom_fields - desc:其他自定义字段 文件类型先调系统上传文件接口获取savename validate:optional
     */
    public function certificationCompany()
    {
        $param = $this->request->param();
        //参数验证
        if (!$this->validate->scene('create_company')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $CertificationLogModel = new  CertificationLogModel();

        $result = $CertificationLogModel->certificationCompany($param);

        return json($result);
    }

    /**
     * 时间 2022-9-24
     * @title 个人转企业
     * @desc 个人转企业
     * @url /console/v1/certification/convert
     * @method POST
     * @author wyh
     * @version v1
     * @param string plugin_name - desc:实名接口标识 validate:required
     * @param string card_name - desc:姓名 validate:required
     * @param string card_type - desc:证件类型 1身份证 2港澳通行证 3台湾通行证 4港澳居住证 5台湾居住证 6海外护照 7中国以外驾照 8其他 validate:required
     * @param string card_number - desc:证件号码 validate:required
     * @param string company - desc:公司 validate:required
     * @param string company_organ_code - desc:公司代码 validate:required
     * @param string phone - desc:手机号 validate:optional
     * @param string img_one - desc:身份证正面照 调系统上传文件接口获取savename validate:optional
     * @param string img_two - desc:身份证反面照 调系统上传文件接口获取savename validate:optional
     * @param string img_three - desc:营业执照 调系统上传文件接口获取savename validate:optional
     * @param object custom_fields - desc:其他自定义字段 文件类型先调系统上传文件接口获取savename validate:optional
     */
    public function certificationConvert()
    {
        $param = $this->request->param();
        //参数验证
        if (!$this->validate->scene('create_company')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $CertificationLogModel = new  CertificationLogModel();

        $param['convert'] = 1;

        $result = $CertificationLogModel->certificationCompany($param);

        return json($result);
    }

    /**
     * 时间 2022-9-24
     * @title 实名认证验证页面
     * @desc 实名认证验证页面
     * @url /console/v1/certification/auth
     * @method GET
     * @author wyh
     * @version v1
     * @return string code - desc:status为400时返回 10000重定向提交资料页面 10001调基础信息接口并加载相应页面
     * @return string html - desc:status为200时返回实名接口的html文档 同时轮询系统状态接口
     */
    public function certificationAuth()
    {
        $CertificationLogModel = new  CertificationLogModel();

        $result = $CertificationLogModel->certificationAuth();

        return json($result);
    }

    /**
     * 时间 2022-9-24
     * @title 获取实名认证状态
     * @desc 获取实名认证状态 在验证页面轮询调用
     * @url /console/v1/certification/status
     * @method GET
     * @author wyh
     * @version v1
     * @return int status - desc:status为400表示无认证信息 直接跳转提交资料页面
     * @return string code - desc:status为200时 1通过 2未通过 3待审核 4提交资料 code为2且refresh为0时继续轮询 其他情况终止轮询
     */
    public function certificationStatus()
    {
        $CertificationLogModel = new  CertificationLogModel();

        $result = $CertificationLogModel->certificationStatus();

        return json($result);
    }

    /**
     * 时间 2024-06-07
     * @title 实名认证接口配置
     * @desc 实名认证接口配置
     * @url /console/v1/certification/plugin/config
     * @method GET
     * @author wyh
     * @version v1
     * @param string name - desc:实名接口标识 validate:required
     * @param string type - desc:验证类型 person个人 company企业 validate:required
     * @return int free - desc:免费次数
     * @return float amount - desc:金额
     * @return int pay - desc:是否需要支付 1是 0否
     * @return object order - desc:订单
     * @return int order.id - desc:订单ID
     * @return string order.status - desc:状态 Paid已付款 Unpaid未付款 Cancelled已取消
     * @return string order.url - desc:跳转地址
     * @return string order.amount - desc:订单金额
     */
    public function certificationConfig()
    {
        $param = $this->request->param();

        $CertificationLogModel = new  CertificationLogModel();

        $data = $CertificationLogModel->certificationConfig($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];

        return json($result);
    }

    /**
     * 时间 2024-06-11
     * @title 生成实名认证订单
     * @desc 生成实名认证订单
     * @url /console/v1/certification/plugin/order
     * @method POST
     * @author wyh
     * @version v1
     * @param string name - desc:实名接口标识 validate:required
     * @param string type - desc:验证类型 person个人 company企业 validate:required
     * @return int order_id - desc:订单ID
     */
    public function certificationOrder()
    {
        $param = $this->request->param();

        $CertificationLogModel = new  CertificationLogModel();

        $result = $CertificationLogModel->certificationOrder($param);

        return json($result);
    }



}