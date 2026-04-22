<?php
namespace app\admin\controller;

use app\common\model\ClientModel;
use app\admin\validate\ClientValidate;

/**
 * @title 用户管理
 * @desc 用户管理
 * @use app\admin\controller\ClientController
 */
class ClientController extends AdminBaseController
{	
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ClientValidate();
    }

	/**
     * 时间 2022-05-10
     * @title 用户列表
     * @desc 用户列表
     * @author theworld
     * @version v1
     * @url /admin/v1/client
     * @method GET
     * @param object custom_field - desc:自定义字段 key为自定义字段名称 value为自定义字段的值 validate:optional
     * @param string type - desc:关键字类型 id用户ID username姓名 phone手机号 email邮箱 company公司 validate:optional
     * @param string keywords - desc:关键字 搜索范围随关键字类型变化 默认搜索范围为用户ID 姓名 邮箱 手机号 公司 validate:optional
     * @param int client_id - desc:用户ID 精确搜索 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序 id reg_time host_active_num host_num credit cost_price refund_price withdraw_price validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @param int show_sub_client - desc:显示子账户 0隐藏 1显示 validate:optional
     * @param string addon_client_custom_field_[num] - desc:搜索用户自定义字段 num为自定义字段ID validate:optional
     * @return array list - desc:用户列表
     * @return int list[].id - desc:用户ID
     * @return string list[].username - desc:姓名
     * @return string list[].email - desc:邮箱
     * @return int list[].phone_code - desc:国际电话区号
     * @return string list[].phone - desc:手机号
     * @return int list[].status - desc:状态 0禁用 1正常
     * @return int list[].reg_time - desc:注册时间
     * @return string list[].country - desc:国家
     * @return string list[].address - desc:地址
     * @return string list[].company - desc:公司
     * @return string list[].language - desc:语言
     * @return string list[].notes - desc:备注
     * @return string list[].credit - desc:余额
     * @return int list[].host_num - desc:产品数量
     * @return int list[].host_active_num - desc:已激活产品数量
     * @return array list[].custom_field - desc:自定义字段
     * @return string list[].custom_field[].name - desc:名称
     * @return string list[].custom_field[].value - desc:值
     * @return string list[].cost_price - desc:消费金额
     * @return bool list[].certification - desc:是否实名认证 true是 false否
     * @return string list[].certification_type - desc:实名类型 person个人 company企业
     * @return string list[].client_level - desc:用户等级 显示字段有client_level时返回
     * @return string list[].client_level_color - desc:用户等级颜色 显示字段有client_level时返回
     * @return string list[].sale - desc:销售 显示字段有sale时返回
     * @return array list[].oauth - desc:关联的三方登录类型
     * @return int list[].mp_weixin_notice - desc:微信公众号关注状态 0未关注 1已关注
     * @return string list[].refund_price - desc:退款金额 显示字段有refund_price时返回
     * @return string list[].withdraw_price - desc:提现金额 显示字段有withdraw_price时返回
     * @return string list[].addon_client_custom_field_[num] - desc:用户自定义字段 显示字段有addon_client_custom_field_[num]时返回 num为数字
     * @return int count - desc:用户总数
     * @return string total_credit - desc:总余额
     * @return string page_total_credit - desc:当前页总余额
     */
	public function clientList()
	{
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $ClientModel = new ClientModel();

        // 获取用户列表
        $data = $ClientModel->clientList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}

    /**
     * 时间 2022-05-10
     * @title 用户详情
     * @desc 用户详情
     * @author theworld
     * @version v1
     * @url /admin/v1/client/:id
     * @method GET
     * @param int id - desc:用户ID validate:required
     * @return object client - desc:用户
     * @return int client.id - desc:用户ID
     * @return string client.username - desc:姓名
     * @return string client.email - desc:邮箱
     * @return int client.phone_code - desc:国际电话区号
     * @return string client.phone - desc:手机号
     * @return string client.company - desc:公司
     * @return int client.country_id - desc:国家ID
     * @return string client.address - desc:地址
     * @return string client.language - desc:语言
     * @return string client.notes - desc:备注
     * @return int client.status - desc:状态 0禁用 1正常
     * @return int client.register_time - desc:注册时间
     * @return int client.last_login_time - desc:上次登录时间
     * @return string client.last_login_ip - desc:上次登录IP
     * @return string client.credit - desc:余额
     * @return string client.consume - desc:消费
     * @return string client.refund - desc:退款
     * @return string client.withdraw - desc:提现
     * @return int client.host_num - desc:产品数量
     * @return int client.host_active_num - desc:已激活产品数量
     * @return array client.login_logs - desc:登录记录
     * @return string client.login_logs[].ip - desc:IP
     * @return int client.login_logs[].login_time - desc:登录时间
     * @return boolean client.certification - desc:是否实名认证 true是 false否
     * @return object client.certification_detail - desc:实名认证详情 当certification为true时才有此字段
     * @return object client.certification_detail.company - desc:企业实名认证详情
     * @return string client.certification_detail.company.card_name - desc:认证姓名
     * @return int client.certification_detail.company.card_type - desc:证件类型 1身份证 2港澳通行证 3台湾通行证 4港澳居住证 5台湾居住证 6海外护照 7中国以外驾照 8其他
     * @return string client.certification_detail.company.card_number - desc:证件号
     * @return string client.certification_detail.company.phone - desc:手机号
     * @return int client.certification_detail.company.status - desc:状态 1已认证 2未通过 3待审核 4已提交资料
     * @return string client.certification_detail.company.company - desc:公司名称
     * @return string client.certification_detail.company.company_organ_code - desc:公司代码
     * @return string client.certification_detail.company.img_one - desc:身份证正面
     * @return string client.certification_detail.company.img_two - desc:身份证反面
     * @return string client.certification_detail.company.img_three - desc:营业执照
     * @return string client.certification_detail.company.auth_fail - desc:失败原因
     * @return object client.certification_detail.person - desc:个人实名认证详情
     * @return string client.certification_detail.person.card_name - desc:认证姓名
     * @return int client.certification_detail.person.card_type - desc:证件类型 1身份证 2港澳通行证 3台湾通行证 4港澳居住证 5台湾居住证 6海外护照 7中国以外驾照 8其他
     * @return string client.certification_detail.person.card_number - desc:证件号
     * @return string client.certification_detail.person.phone - desc:手机号
     * @return int client.certification_detail.person.status - desc:状态 1已认证 2未通过 3待审核 4已提交资料
     * @return string client.certification_detail.person.img_one - desc:身份证正面
     * @return string client.certification_detail.person.img_two - desc:身份证反面
     * @return string client.certification_detail.person.img_three - desc:营业执照
     * @return string client.certification_detail.person.auth_fail - desc:失败原因
     * @return bool client.set_operate_password - desc:是否设置了操作密码
     * @return int client.receive_sms - desc:接收短信 0关闭 1开启
     * @return int client.receive_email - desc:接收邮件 0关闭 1开启
     * @return array client.oauth - desc:关联的三方登录类型
     * @return int client.mp_weixin_notice - desc:微信公众号关注状态 0未关注 1已关注
     * @return float client.freeze_credit - desc:冻结余额
     */
    public function index()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $ClientModel = new ClientModel();

        // 获取用户
        $client = $ClientModel->indexClient($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'client' => $client
            ]
        ];
        return json($result);
    }

	/**
     * 时间 2022-05-10
     * @title 新建用户
     * @desc 新建用户
     * @author theworld
     * @version v1
     * @url /admin/v1/client
     * @method POST
     * @param string username - desc:姓名 validate:optional
     * @param string email - desc:邮箱 邮箱手机号两者至少输入一个 validate:optional
     * @param int phone_code - desc:国际电话区号 输入手机号时必须传此参数 validate:optional
     * @param string phone - desc:手机号 邮箱手机号两者至少输入一个 validate:optional
     * @param string password - desc:密码 validate:required
     * @param string repassword - desc:重复密码 validate:required
     * @return int id - desc:用户ID
     */
	public function create()
    {
        // 接收参数
		$param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

		// 实例化模型类
        $ClientModel = new ClientModel();
        
        // 新建用户
        $result = $ClientModel->createClient($param);

        return json($result);
	}

	/**
     * 时间 2022-05-10
     * @title 修改用户
     * @desc 修改用户
     * @author theworld
     * @version v1
     * @url /admin/v1/client/:id
     * @method PUT
     * @param int id - desc:用户ID validate:required
     * @param string username - desc:姓名 validate:optional
     * @param string email - desc:邮箱 邮箱手机号两者至少输入一个 validate:optional
     * @param int phone_code - desc:国际电话区号 输入手机号时必须传此参数 validate:optional
     * @param string phone - desc:手机号 邮箱手机号两者至少输入一个 validate:optional
     * @param string company - desc:公司 validate:optional
     * @param string country - desc:国家 validate:optional
     * @param string address - desc:地址 validate:optional
     * @param string language - desc:语言 validate:optional
     * @param string notes - desc:备注 validate:optional
     * @param string password - desc:密码 为空代表不修改 validate:optional
     * @param string operate_password - desc:操作密码 为空代表不修改 validate:optional
     * @param object customfield - desc:自定义字段 validate:optional
     */
	public function update()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();
        
        // 修改用户
        $result = $ClientModel->updateClient($param);

        return json($result);
	}

	/**
     * 时间 2022-05-10
     * @title 删除用户
     * @desc 删除用户
     * @author theworld
     * @version v1
     * @url /admin/v1/client/:id
     * @method DELETE
     * @param int id - desc:用户ID validate:required
     */
	public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $ClientModel = new ClientModel();
        
        // 删除用户
        $result = $ClientModel->deleteClient($param);

        return json($result);

	}

    /**
     * 时间 2022-5-26
     * @title 用户状态切换
     * @desc 用户状态切换
     * @author theworld
     * @version v1
     * @url /admin/v1/client/:id/status
     * @method PUT
     * @param int id - desc:用户ID validate:required
     * @param int status - desc:状态 0禁用 1启用 validate:required
     */
    public function status()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('status')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();
        
        // 更改状态
        $result = $ClientModel->updateClientStatus($param);

        return json($result);
    }

    /**
     * 时间 2022-5-26
     * @title 修改用户接收短信
     * @desc 修改用户接收短信
     * @author theworld
     * @version v1
     * @url /admin/v1/client/:id/receive_sms
     * @method PUT
     * @param int id - desc:用户ID validate:required
     * @param int receive_sms - desc:接收短信 0禁用 1启用 validate:required
     */
    public function receiveSms()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('receive_sms')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();
        
        // 修改用户接收短信
        $result = $ClientModel->updateClientReceiveSms($param);

        return json($result);
    }

    /**
     * 时间 2022-5-26
     * @title 修改用户接收邮件
     * @desc 修改用户接收邮件
     * @author theworld
     * @version v1
     * @url /admin/v1/client/:id/receive_email
     * @method PUT
     * @param int id - desc:用户ID validate:required
     * @param int receive_email - desc:接收邮件 0禁用 1启用 validate:required
     */
    public function receiveEmail()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('receive_email')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();
        
        // 修改用户接收邮件
        $result = $ClientModel->updateClientReceiveEmail($param);

        return json($result);
    }

    /**
     * 时间 2022-05-16
     * @title 搜索用户
     * @desc 搜索用户
     * @author theworld
     * @version v1
     * @url /admin/v1/client/search
     * @method GET
     * @param string keywords - desc:关键字 搜索范围为用户ID 姓名 邮箱 手机号 validate:optional
     * @param int client_id - desc:用户ID 精确搜索 validate:optional
     * @return array list - desc:用户列表
     * @return int list[].id - desc:用户ID
     * @return string list[].username - desc:姓名
     */
    public function search()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $ClientModel = new ClientModel();

        // 获取用户列表
        $data = $ClientModel->searchClient($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-05-30
     * @title 以用户登录
     * @desc 以用户登录
     * @author wyh
     * @version v1
     * @url /admin/v1/client/:id/login
     * @method POST
     * @param int id - desc:用户ID validate:required
     * @return string jwt - desc:JWT 获取后放在请求头Authorization里 拼接格式为 Bearer yJ0eX.test.ste
     */
    public function login()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $ClientModel = new ClientModel();

        // 获取用户列表
        $result = $ClientModel->loginByClient(intval($param['id']));

        return json($result);
    }
}