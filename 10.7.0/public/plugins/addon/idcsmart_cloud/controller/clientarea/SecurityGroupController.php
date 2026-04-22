<?php
namespace addon\idcsmart_cloud\controller\clientarea;

use think\response\Json;
use app\event\controller\PluginBaseController;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupHostLinkModel;
use addon\idcsmart_cloud\validate\IdcsmartSecurityGroupValidate;
use app\common\model\HostModel;

/**
 * @title 安全组管理
 * @desc 安全组管理
 * @use addon\idcsmart_cloud\controller\clientarea\SecurityGroupController
 */
class SecurityGroupController extends PluginBaseController
{

    protected $validate;

    public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartSecurityGroupValidate();
    }

    /**
     * 时间 2022-06-08
     * @title 安全组列表
     * @desc 安全组列表
     * @author theworld
     * @version v1
     * @url /console/v1/security_group
     * @method GET
     * @param string keywords - desc:关键字 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 id validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:安全组列表
     * @return int list[].id - desc:安全组ID
     * @return string list[].name - desc:名称
     * @return string list[].description - desc:描述
     * @return int list[].create_time - desc:创建时间
     * @return int list[].host_num - desc:产品数量
     * @return int list[].rule_num - desc:规则数量
     * @return int count - desc:安全组总数
     */
    public function list(): Json
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $IdcsmartSecurityGroupModel = new IdcsmartSecurityGroupModel();

        // 获取安全组列表
        $data = $IdcsmartSecurityGroupModel->idcsmartSecurityGroupList($param);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-08
     * @title 安全组详情
     * @desc 安全组详情
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id
     * @method GET
     * @param int id - desc:安全组ID validate:required
     * @return object security_group - desc:安全组
     * @return int security_group.id - desc:安全组ID
     * @return string security_group.name - desc:名称
     * @return string security_group.description - desc:描述
     * @return int security_group.create_time - desc:创建时间
     */
    public function index(): Json
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartSecurityGroupModel = new IdcsmartSecurityGroupModel();

        // 获取安全组
        $securityGroup = $IdcsmartSecurityGroupModel->indexIdcsmartSecurityGroup($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'security_group' => $securityGroup
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-08
     * @title 添加安全组
     * @desc 添加安全组
     * @author theworld
     * @version v1
     * @url /console/v1/security_group
     * @method POST
     * @param string name - desc:名称 validate:required
     * @param string description - desc:描述 validate:optional
     * @param int auto_create_rule - desc:是否创建默认规则 0否 1是 validate:optional
     * @return int id - desc:安全组ID
     */
    public function create(): Json
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartSecurityGroupModel = new IdcsmartSecurityGroupModel();

        // 创建安全组
        $result = $IdcsmartSecurityGroupModel->createIdcsmartSecurityGroup($param);

        return json($result);
    }

    /**
     * 时间 2022-06-08
     * @title 修改安全组
     * @desc 修改安全组
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id
     * @method PUT
     * @param int id - desc:安全组ID validate:required
     * @param string name - desc:名称 validate:required
     * @param string description - desc:描述 validate:optional
     */
    public function update(): Json
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartSecurityGroupModel = new IdcsmartSecurityGroupModel();

        // 修改安全组
        $result = $IdcsmartSecurityGroupModel->updateIdcsmartSecurityGroup($param);

        return json($result);
    }

    /**
     * 时间 2022-06-08
     * @title 删除安全组
     * @desc 删除安全组
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id
     * @method DELETE
     * @param int id - desc:安全组ID validate:required
     */
    public function delete(): Json
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartSecurityGroupModel = new IdcsmartSecurityGroupModel();

        // 删除安全组
        $result = $IdcsmartSecurityGroupModel->deleteIdcsmartSecurityGroup($param['id']);

        return json($result);
    }

    /**
     * 时间 2022-06-09
     * @title 安全组实例列表
     * @desc 安全组实例列表
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id/host
     * @method GET
     * @param int id - desc:安全组ID validate:required
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 id validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:实例列表
     * @return int list[].id - desc:实例ID
     * @return string list[].name - desc:名称
     * @return string list[].ip - desc:IP
     * @return int count - desc:实例总数
     */
    public function securityGroupHostList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $IdcsmartSecurityGroupHostLinkModel = new IdcsmartSecurityGroupHostLinkModel();

        // 关联安全组
        $data = $IdcsmartSecurityGroupHostLinkModel->idcsmartSecurityGroupHostList($param);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }


    /**
     * 时间 2022-09-08
     * @title 关联安全组
     * @desc 关联安全组
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id/host/:host_id
     * @method POST
     * @param int id - desc:安全组ID validate:required
     * @param int host_id - desc:产品ID validate:required
     */
    public function linkSecurityGroup()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('link')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        // 实例化模型类
        $IdcsmartSecurityGroupHostLinkModel = new IdcsmartSecurityGroupHostLinkModel();

        // 关联安全组
        $result = $IdcsmartSecurityGroupHostLinkModel->linkSecurityGroup($param);

        return json($result);
    }

    /**
     * 时间 2022-09-08
     * @title 取消关联安全组
     * @desc 取消关联安全组
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id/host/:host_id
     * @method DELETE
     * @param int id - desc:安全组ID validate:required
     * @param int host_id - desc:产品ID validate:required
     */
    public function unlinkSecurityGroup()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('unlink')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        // 实例化模型类
        $IdcsmartSecurityGroupHostLinkModel = new IdcsmartSecurityGroupHostLinkModel();

        // 取消关联安全组
        $result = $IdcsmartSecurityGroupHostLinkModel->unlinkSecurityGroup($param);

        return json($result);
    }

    /**
     * 时间 2024-07-02
     * @title 批量关联安全组
     * @desc 批量关联安全组
     * @author hh
     * @version v1
     * @url /console/v1/security_group/:id/host
     * @method POST
     * @param int id - desc:安全组ID validate:required
     * @param array host_id - desc:产品ID列表 validate:required
     * @return int [].status - desc:状态码 200成功 400失败
     * @return string [].msg - desc:信息
     * @return string [].name - desc:产品标识
     * @return int [].id - desc:产品ID
     */
    public function batchLinkSecurityGroup()
    {
        $param = $this->request->param();

        if(!isset($param['host_id']) || !is_array($param['host_id'])){
            return json(['status'=>400, 'msg'=>lang('param_error')]);
        }
        if(empty($param['host_id'])){
            return json(['status'=>400, 'msg'=>lang_plugins('id_error')]);
        }

        $host = HostModel::field('id,name')->whereIn('id', $param['host_id'])->where('client_id', get_client_id() )->where('is_delete', 0)->select()->toArray();
        $host = array_column($host, 'name', 'id');

        // 实例化模型类
        $IdcsmartSecurityGroupHostLinkModel = new IdcsmartSecurityGroupHostLinkModel();

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [],
        ];

        foreach($param['host_id'] as $hostId){
            if(!isset($host[$hostId])){
                $result['data'][] = [
                    'status'    => 400,
                    'msg'       => lang_plugins('host_is_not_exist'),
                    'name'      => 'ID-#' . $hostId,
                    'id'        => $hostId,
                ];
            }else{
                // 关联安全组
                $linkRes = $IdcsmartSecurityGroupHostLinkModel->linkSecurityGroup([
                    'id'        => $param['id'],
                    'host_id'   => $hostId,
                ]);
                $result['data'][] = [
                    'status'    => $linkRes['status'],
                    'msg'       => $linkRes['msg'],
                    'name'      => $host[$hostId],
                    'id'        => $hostId,
                ];
            }
        }
        return json($result);
    }

}