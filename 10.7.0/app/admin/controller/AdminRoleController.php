<?php
namespace app\admin\controller;

use app\admin\model\AdminRoleModel;
use app\admin\validate\AdminRoleValidate;

/**
 * @title 管理员分组
 * @desc 管理员分组管理
 * @use app\admin\controller\AdminRoleController
 */
class AdminRoleController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new AdminRoleValidate();
    }

    /**
     * 时间 2022-5-10
     * @title 管理员分组列表
     * @desc 管理员分组列表
     * @url /admin/v1/admin/role
     * @method GET
     * @author wyh
     * @version v1
     * @param string keywords - desc:关键字 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序 id name description validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:管理员分组列表
     * @return int list[].id - desc:ID
     * @return string list[].name - desc:分组名称
     * @return string list[].description - desc:分组说明
     * @return string list[].admins - desc:分组下管理员 逗号分隔
     * @return int count - desc:管理员分组总数
     */
    public function adminRoleList()
    {
        # 合并分页参数
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new AdminRoleModel())->adminRoleList($param)
        ];
       return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 获取单个管理员分组
     * @desc 获取单个管理员分组
     * @url /admin/v1/admin/role/:id
     * @method GET
     * @author wyh
     * @version v1
     * @param int id - desc:管理员分组ID validate:required
     * @return object admin_role - desc:管理员分组
     * @return int admin_role.id - desc:ID
     * @return string admin_role.name - desc:分组名称
     * @return string admin_role.description - desc:分组描述
     * @return string admin_role.admins - desc:分组下管理员 逗号分隔
     * @return array admin_role.auth - desc:权限ID数组
     * @return array admin_role.auth_widget - desc:挂件标识
     */
    public function index()
    {
        $param = $this->request->param();

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>[
                'admin_role' => (new AdminRoleModel())->indexAdminRole(intval($param['id']))
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 添加管理员分组
     * @desc 添加管理员分组
     * @url /admin/v1/admin/role
     * @method POST
     * @author wyh
     * @version v1
     * @param string name - desc:分组名称 validate:required
     * @param string description - desc:分组说明 validate:required
     * @param array auth - desc:权限ID数组 validate:optional
     * @param array auth_widget - desc:挂件标识 validate:optional
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new AdminRoleModel())->createAdminRole($param);

        return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 修改管理员分组
     * @desc 修改管理员分组
     * @url /admin/v1/admin/role/:id
     * @method PUT
     * @author wyh
     * @version v1
     * @param int id - desc:分组ID validate:required
     * @param string name - desc:分组名称 validate:required
     * @param string description - desc:分组说明 validate:required
     * @param array auth - desc:权限ID数组 validate:optional
     * @param array auth_widget - desc:挂件标识 validate:optional
     */
    public function update()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new AdminRoleModel())->updateAdminRole($param);

        return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 删除管理员分组
     * @desc 删除管理员分组
     * @url /admin/v1/admin/role/:id
     * @method DELETE
     * @author wyh
     * @version v1
     * @param int id - desc:管理员分组ID validate:required
     */
    public function delete()
    {
        $param = $this->request->param();

        $result = (new AdminRoleModel())->deleteAdminRole($param);

        return json($result);
    }
}

