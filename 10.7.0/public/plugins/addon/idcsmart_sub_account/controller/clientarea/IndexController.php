<?php
namespace addon\idcsmart_sub_account\controller\clientarea;

use app\event\controller\PluginBaseController;
use addon\idcsmart_sub_account\model\IdcsmartSubAccountModel;
use addon\idcsmart_sub_account\validate\IdcsmartSubAccountValidate;

/**
 * @title 子账户管理
 * @desc 子账户管理
 * @use addon\idcsmart_sub_account\controller\clientarea\IndexController
 */
class IndexController extends PluginBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartSubAccountValidate();
    }

    /**
     * 时间 2022-08-09
     * @title 子账户列表
     * @desc 子账户列表
     * @author theworld
     * @version v1
     * @url /console/v1/sub_account
     * @method GET
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 id validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:子账户列表
     * @return int list[].id - desc:子账户ID
     * @return int list[].status - desc:状态 0禁用 1启用
     * @return string list[].username - desc:账户名
     * @return string list[].last_action_time - desc:上次使用时间
     * @return int count - desc:子账户总数
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        // 获取子账户列表
        $data = $IdcsmartSubAccountModel->idcsmartSubAccountList($param, 'home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-08-09
     * @title 子账户详情
     * @desc 子账户详情
     * @author theworld
     * @version v1
     * @url /console/v1/sub_account/:id
     * @method GET
     * @param int id - desc:子账户ID validate:required
     * @return object account - desc:子账户信息
     * @return int account.id - desc:子账户ID
     * @return string account.username - desc:账户名
     * @return string account.email - desc:邮件
     * @return int account.phone_code - desc:国际电话区号
     * @return string account.phone - desc:手机号
     * @return array account.auth - desc:权限
     * @return array account.notice - desc:通知 product产品 marketing营销 ticket工单 cost费用 recommend推介 system系统
     * @return array account.project_id - desc:项目ID数组
     * @return string account.visible_product - desc:可见产品 module模块 host具体产品
     * @return array account.module - desc:模块
     * @return array account.host_id - desc:产品ID数组
     */
    public function index()
    {
        // 合并分页参数
        $param = $this->request->param();

        // 实例化模型类
        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        // 获取子账户详情
        $account = $IdcsmartSubAccountModel->idcsmartSubAccountDetail($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'account' => $account
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-08-09
     * @title 创建子账户
     * @desc 创建子账户
     * @author theworld
     * @version v1
     * @url /console/v1/sub_account
     * @method POST
     * @param string username - desc:账户名 validate:required
     * @param string email - desc:邮件 邮件手机号两者至少输入一个 validate:optional
     * @param int phone_code - desc:国际电话区号 输入手机号时必传 validate:optional
     * @param string phone - desc:手机号 邮件手机号两者至少输入一个 validate:optional
     * @param string password - desc:密码 validate:required
     * @param array project_id - desc:项目ID数组 validate:optional
     * @param string visible_product - desc:可见产品 module模块 host具体产品 validate:optional
     * @param array module - desc:模块 validate:optional
     * @param array host_id - desc:产品ID数组 validate:optional
     * @param array auth - desc:权限 validate:required
     * @param array notice - desc:通知 product产品 marketing营销 ticket工单 cost费用 recommend推介 system系统 validate:required
     */
    public function create()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        // 创建子账户
        $result = $IdcsmartSubAccountModel->createIdcsmartSubAccount($param);

        return json($result);
    }

    /**
     * 时间 2022-08-09
     * @title 编辑子账户
     * @desc 编辑子账户
     * @author theworld
     * @version v1
     * @url /console/v1/sub_account/:id
     * @method PUT
     * @param int id - desc:子账户ID validate:required
     * @param string email - desc:邮件 邮件手机号两者至少输入一个 validate:optional
     * @param int phone_code - desc:国际电话区号 输入手机号时必传 validate:optional
     * @param string phone - desc:手机号 邮件手机号两者至少输入一个 validate:optional
     * @param string password - desc:密码 validate:required
     * @param array project_id - desc:项目ID数组 validate:optional
     * @param string visible_product - desc:可见产品 module模块 host具体产品 validate:optional
     * @param array module - desc:模块 validate:optional
     * @param array host_id - desc:产品ID数组 validate:optional
     * @param array auth - desc:权限 validate:required
     * @param array notice - desc:通知 product产品 marketing营销 ticket工单 cost费用 recommend推介 system系统 validate:required
     */
    public function update()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        // 编辑子账户
        $result = $IdcsmartSubAccountModel->updateIdcsmartSubAccount($param);

        return json($result);
    }

    /**
     * 时间 2022-08-09
     * @title 删除子账户
     * @desc 删除子账户
     * @author theworld
     * @version v1
     * @url /console/v1/sub_account/:id
     * @method DELETE
     * @param int id - desc:子账户ID validate:required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        // 删除子账户
        $result = $IdcsmartSubAccountModel->deleteIdcsmartSubAccount($param['id']);

        return json($result);
    }

    /**
     * 时间 2022-08-09
     * @title 子账户状态切换
     * @desc 子账户状态切换
     * @author theworld
     * @version v1
     * @url /console/v1/sub_account/:id/status
     * @method PUT
     * @param int id - desc:子账户ID validate:required
     * @param int status - desc:状态 0禁用 1启用 validate:required
     */
    public function status()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('status')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        // 子账户状态切换
        $result = $IdcsmartSubAccountModel->updateIdcsmartSubAccountStatus($param);

        return json($result);
    }

    /**
     * 时间 2022-5-27
     * @title 当前子账户权限列表
     * @desc 当前子账户权限列表
     * @author theworld
     * @version v1
     * @url /console/v1/sub_account/:id/auth
     * @method GET
     * @param int id - desc:子账户ID validate:required
     * @return array list - desc:权限列表
     * @return int list[].id - desc:权限ID
     * @return string list[].title - desc:权限标题
     * @return string list[].url - desc:地址
     * @return int list[].order - desc:排序
     * @return int list[].parent_id - desc:父级ID
     * @return array list[].child - desc:权限子集
     * @return int list[].child[].id - desc:权限ID
     * @return string list[].child[].title - desc:权限标题
     * @return string list[].child[].url - desc:地址
     * @return int list[].child[].order - desc:排序
     * @return int list[].child[].parent_id - desc:父级ID
     * @return array list[].child[].child - desc:权限子集
     * @return int list[].child[].child[].id - desc:权限ID
     * @return string list[].child[].child[].title - desc:权限标题
     * @return string list[].child[].child[].url - desc:地址
     * @return int list[].child[].child[].order - desc:排序
     * @return int list[].child[].child[].parent_id - desc:父级ID
     * @return array rules - desc:权限规则
     */
    public function subAccountAuthList()
    {
        // 接收参数
        $param = $this->request->param();
        
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new IdcsmartSubAccountModel())->authList($param['id'])
        ];
        return json($result);
    }
}