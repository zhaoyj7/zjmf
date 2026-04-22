<?php
namespace addon\idcsmart_cloud\controller\clientarea;

use app\event\controller\PluginBaseController;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupRuleModel;
use addon\idcsmart_cloud\validate\IdcsmartSecurityGroupValidate;

/**
 * @title 安全组规则管理
 * @desc 安全组规则管理
 * @use addon\idcsmart_cloud\controller\clientarea\SecurityGroupRuleController
 */
class SecurityGroupRuleController extends PluginBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartSecurityGroupValidate();
    }

    /**
     * 时间 2022-06-09
     * @title 安全组规则列表
     * @desc 安全组规则列表
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id/rule
     * @method GET
     * @param int id - desc:安全组ID validate:required
     * @param string keywords - desc:关键字 validate:optional
     * @param string direction - desc:规则方向 in进方向 out出方向 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序 id validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @param string direction - desc:规则方向筛选 in=进方向 out=出方向 validate:optional
     * @return array list - desc:安全组规则列表
     * @return int list[].id - desc:安全组规则ID
     * @return string list[].description - desc:描述
     * @return string list[].direction - desc:规则方向 in进 out出
     * @return string list[].protocol - desc:协议 all all_tcp all_udp tcp udp icmp ssh telnet http https mssql oracle mysql rdp postgresql redis gre
     * @return string list[].port - desc:端口范围
     * @return string list[].ip - desc:授权IP
     * @return int list[].create_time - desc:创建时间
     * @return int count - desc:安全组规则总数
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $IdcsmartSecurityGroupRuleModel = new IdcsmartSecurityGroupRuleModel();

        // 获取安全组规则列表
        $data = $IdcsmartSecurityGroupRuleModel->idcsmartSecurityGroupRuleList($param);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-09
     * @title 安全组规则详情
     * @desc 安全组规则详情
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/rule/:id
     * @method GET
     * @param int id - desc:安全组规则ID validate:required
     * @return object security_group_rule - desc:安全组规则
     * @return int security_group_rule.id - desc:安全组规则ID
     * @return string security_group_rule.description - desc:描述
     * @return string security_group_rule.direction - desc:规则方向 in进 out出
     * @return string security_group_rule.protocol - desc:协议 all all_tcp all_udp tcp udp icmp ssh telnet http https mssql oracle mysql rdp postgresql redis gre
     * @return string security_group_rule.port - desc:端口范围
     * @return string security_group_rule.ip - desc:授权IP
     * @return int security_group_rule.create_time - desc:创建时间
     */
    public function index()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartSecurityGroupRuleModel = new IdcsmartSecurityGroupRuleModel();

        // 获取安全组规则
        $securityGroupRule = $IdcsmartSecurityGroupRuleModel->indexIdcsmartSecurityGroupRule($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'security_group_rule' => $securityGroupRule
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-09
     * @title 添加安全组规则
     * @desc 添加安全组规则
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id/rule
     * @method POST
     * @param int id - desc:安全组ID validate:required
     * @param string description - desc:描述 validate:optional
     * @param string direction - desc:规则方向 in进 out出 validate:required
     * @param string protocol - desc:协议 all all_tcp all_udp tcp udp icmp ssh telnet http https mssql oracle mysql rdp postgresql redis gre validate:required
     * @param string port - desc:端口范围 validate:required
     * @param string ip - desc:授权IP validate:required
     * @return int id - desc:安全组规则ID
     */
    public function create()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create_rule')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartSecurityGroupRuleModel = new IdcsmartSecurityGroupRuleModel();

        // 创建安全组规则
        $result = $IdcsmartSecurityGroupRuleModel->createIdcsmartSecurityGroupRule($param);

        return json($result);
    }

    /**
     * 时间 2022-08-26
     * @title 批量添加安全组规则
     * @desc 批量添加安全组规则
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id/rule/batch
     * @method POST
     * @param array rule - desc:规则列表 validate:required
     * @param string rule[].description - desc:描述 validate:optional
     * @param string rule[].direction - desc:规则方向 in进 out出 validate:required
     * @param string rule[].protocol - desc:协议 all all_tcp all_udp tcp udp icmp ssh telnet http https mssql oracle mysql rdp postgresql redis gre validate:required
     * @param string rule[].port - desc:端口范围 validate:required
     * @param string rule[].ip - desc:授权IP validate:required
     * @return int success_num - desc:添加成功的规则数量
     */
    public function batchCreate()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartSecurityGroupRuleModel = new IdcsmartSecurityGroupRuleModel();

        // 创建安全组规则
        $result = $IdcsmartSecurityGroupRuleModel->batchCreate($param);

        return json($result);
    }

    /**
     * 时间 2022-06-09
     * @title 修改安全组规则
     * @desc 修改安全组规则
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/rule/:id
     * @method PUT
     * @param int id - desc:安全组规则ID validate:required
     * @param string description - desc:描述 validate:optional
     * @param string direction - desc:规则方向 in进 out出 validate:required
     * @param string protocol - desc:协议 all all_tcp all_udp tcp udp icmp ssh telnet http https mssql oracle mysql rdp postgresql redis gre validate:required
     * @param string port - desc:端口范围 validate:required
     * @param string ip - desc:授权IP validate:required
     */
    public function update()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update_rule')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartSecurityGroupRuleModel = new IdcsmartSecurityGroupRuleModel();

        // 修改安全组规则
        $result = $IdcsmartSecurityGroupRuleModel->updateIdcsmartSecurityGroupRule($param);

        return json($result);
    }

    /**
     * 时间 2022-06-09
     * @title 删除安全组规则
     * @desc 删除安全组规则
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/rule/:id
     * @method DELETE
     * @param int id - desc:安全组规则ID validate:required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartSecurityGroupRuleModel = new IdcsmartSecurityGroupRuleModel();

        // 删除安全组规则
        $result = $IdcsmartSecurityGroupRuleModel->deleteIdcsmartSecurityGroupRule($param['id']);

        return json($result);
    }
}