<?php
namespace app\admin\controller;

use app\common\model\ApiModel;
use app\admin\validate\ApiValidate;

/**
 * @title API管理
 * @desc API管理
 * @use app\admin\controller\ApiController
 */
class ApiController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ApiValidate();
    }

    /**
     * 时间 2024-04-28
     * @title 获取API设置
     * @desc 获取API设置
     * @author theworld
     * @version v1
     * @url /admin/v1/api/config
     * @method GET
     * @return int client_create_api - desc:用户API创建权限 0关闭 1开启
     * @return int client_create_api_type - desc:用户API创建权限类型 0全部用户 1指定用户可创建 2指定用户不可创建
     */
    public function getConfig()
    {
        //实例化模型类
        $ApiModel = new ApiModel();
        
        //获取API设置
        $data = $ApiModel->getConfig();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
       return json($result);
    }

    /**
     * 时间 2024-04-28
     * @title 保存API设置
     * @desc 保存API设置
     * @author theworld
     * @version v1
     * @url /admin/v1/api/config
     * @method PUT
     * @param int client_create_api - desc:用户API创建权限 0关闭 1开启 validate:optional
     * @param int client_create_api_type - desc:用户API创建权限类型 0全部用户 1指定用户可创建 2指定用户不可创建 validate:optional
     */
    public function updateConfig()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('config')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ApiModel())->updateConfig($param);

        return json($result);
    }

    /**
     * 时间 2024-04-28
     * @title API指定用户列表
     * @desc API指定用户列表
     * @author theworld
     * @version v1
     * @url /admin/v1/api/client
     * @method GET
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序 id validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:用户列表
     * @return int list[].id - desc:用户ID
     * @return string list[].username - desc:姓名
     * @return string list[].email - desc:邮箱
     * @return int list[].phone_code - desc:国际电话区号
     * @return string list[].phone - desc:手机号
     * @return int list[].status - desc:状态 0禁用 1正常
     * @return string list[].company - desc:公司
     * @return int list[].host_num - desc:产品数量
     * @return int list[].host_active_num - desc:已激活产品数量
     * @return array list[].custom_field - desc:自定义字段
     * @return string list[].custom_field[].name - desc:名称
     * @return string list[].custom_field[].value - desc:值
     * @return bool list[].certification - desc:是否实名认证 true是 false否
     * @return string list[].certification_type - desc:实名类型 person个人 company企业
     * @return int count - desc:用户总数
     */
    public function clientList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $ApiModel = new ApiModel();

        // 获取用户列表
        $data = $ApiModel->clientList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-28
     * @title 添加API指定用户
     * @desc 添加API指定用户
     * @author theworld
     * @version v1
     * @url /admin/v1/api/client/:id
     * @method POST
     * @param int id - desc:用户ID validate:required
     */
    public function addClient()
    {
        $param = $this->request->param();

        $result = (new ApiModel())->addClient($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-04-28
     * @title 移除API指定用户
     * @desc 移除API指定用户
     * @author theworld
     * @version v1
     * @url /admin/v1/api/client/:id
     * @method DELETE
     * @param int id - desc:用户ID validate:required
     */
    public function removeClient()
    {
        $param = $this->request->param();

        $result = (new ApiModel())->removeClient($param['id']);

        return json($result);
    }

}

