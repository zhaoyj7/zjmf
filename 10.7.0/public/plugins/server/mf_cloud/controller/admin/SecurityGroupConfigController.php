<?php
namespace server\mf_cloud\controller\admin;

use think\response\Json;
use server\mf_cloud\model\SecurityGroupConfigModel;
use server\mf_cloud\validate\SecurityGroupConfigValidate;

/**
 * @title 魔方云(自定义配置)-安全组配置管理(后台)
 * @desc 魔方云(自定义配置)-安全组配置管理(后台)
 * @use server\mf_cloud\controller\admin\SecurityGroupConfigController
 */
class SecurityGroupConfigController
{
    /**
     * @title 安全组配置列表
     * @desc 安全组配置列表
     * @author hh
     * @version v1
     * @url /admin/v1/mf_cloud/security_group_config
     * @method GET
     * @param int product_id - 商品ID require
     * @return array list - 配置列表
     * @return int list[].id - 配置ID
     * @return string list[].description - 描述
     * @return string list[].protocol - 协议类型
     * @return string list[].port - 端口
     * @return string list[].direction - 方向
     * @return int list[].status - 状态(0禁用1启用)
     */
    public function list(): Json
    {
        $param = request()->param();
        
        $SecurityGroupConfigModel = new SecurityGroupConfigModel();
        $data = $SecurityGroupConfigModel->getConfigList($param['product_id'] ?? 0);

        $result = [
            'status' => 200, 
            'msg' => lang_plugins('success_message'), 
            'data' => $data,
        ];
        
        return json($result);
    }

    /**
     * @title 添加安全组配置
     * @desc 添加安全组配置
     * @author hh
     * @version v1
     * @url /admin/v1/mf_cloud/security_group_config
     * @method POST
     * @param int product_id - 商品ID require
     * @param string description - 描述 require
     * @param string protocol - 协议类型(all,all_tcp,all_udp,tcp,udp,icmp,ssh,telnet,http,https,mssql,oracle,mysql,rdp,postgresql,redis) require
     * @param string port - 端口 require
     */
    public function create(): Json
    {
        $param = request()->param();
        
        // 验证参数
        $SecurityGroupConfigValidate = new SecurityGroupConfigValidate();
        if (!$SecurityGroupConfigValidate->scene('create')->check($param)) {
            return json(['status' => 400, 'msg' => lang_plugins($SecurityGroupConfigValidate->getError())]);
        }
        
        $SecurityGroupConfigModel = new SecurityGroupConfigModel();
        $result = $SecurityGroupConfigModel->createConfig($param);
        
        return json($result);
    }

    /**
     * @title 编辑安全组配置
     * @desc 编辑安全组配置
     * @author hh
     * @version v1
     * @url /admin/v1/mf_cloud/security_group_config/:id
     * @method PUT
     * @param int id - 配置ID require
     * @param string description - 描述 require
     * @param string protocol - 协议类型(all,all_tcp,all_udp,tcp,udp,icmp,ssh,telnet,http,https,mssql,oracle,mysql,rdp,postgresql,redis) require
     * @param string port - 端口 require
     */
    public function update(): Json
    {
        $param = request()->param();
        
        // 验证参数
        $SecurityGroupConfigValidate = new SecurityGroupConfigValidate();
        if (!$SecurityGroupConfigValidate->scene('update')->check($param)) {
            return json(['status' => 400, 'msg' => lang_plugins($SecurityGroupConfigValidate->getError())]);
        }
        
        $SecurityGroupConfigModel = new SecurityGroupConfigModel();
        $result = $SecurityGroupConfigModel->updateConfig($param);
        
        return json($result);
    }

    /**
     * @title 删除安全组配置
     * @desc 删除安全组配置
     * @author hh
     * @version v1
     * @url /admin/v1/mf_cloud/security_group_config/:id
     * @method DELETE
     * @param int id - 配置ID require
     */
    public function delete(): Json
    {
        $param = request()->param();
        
        // 验证参数
        $SecurityGroupConfigValidate = new SecurityGroupConfigValidate();
        if (!$SecurityGroupConfigValidate->scene('delete')->check($param)) {
            return json(['status' => 400, 'msg' => lang_plugins($SecurityGroupConfigValidate->getError())]);
        }
        
        $SecurityGroupConfigModel = new SecurityGroupConfigModel();
        $result = $SecurityGroupConfigModel->deleteConfig($param['id'] ?? 0);
        
        return json($result);
    }

    /**
     * @title 重置为默认安全组配置
     * @desc 重置为默认安全组配置
     * @author hh
     * @version v1
     * @url /admin/v1/mf_cloud/security_group_config/reset
     * @method POST
     * @param int product_id - 商品ID require
     */
    public function reset(): Json
    {
        $param = request()->param();
        
        // 验证参数
        $SecurityGroupConfigValidate = new SecurityGroupConfigValidate();
        if (!$SecurityGroupConfigValidate->scene('reset')->check($param)) {
            return json(['status' => 400, 'msg' => lang_plugins($SecurityGroupConfigValidate->getError())]);
        }
        
        $SecurityGroupConfigModel = new SecurityGroupConfigModel();
        $result = $SecurityGroupConfigModel->resetConfigs($param['product_id'] ?? 0);
        
        return json($result);
    }

    /**
     * @title 安全组配置排序
     * @desc 安全组配置排序
     * @author hh
     * @version v1
     * @url /admin/v1/mf_cloud/security_group_config/sort
     * @method PUT
     * @param array ids - 配置ID数组(按新的排序顺序) require
     */
    public function sort(): Json
    {
        $param = request()->param();
        
        // 验证参数
        $SecurityGroupConfigValidate = new SecurityGroupConfigValidate();
        if (!$SecurityGroupConfigValidate->scene('sort')->check($param)) {
            return json(['status' => 400, 'msg' => lang_plugins($SecurityGroupConfigValidate->getError())]);
        }
        
        $SecurityGroupConfigModel = new SecurityGroupConfigModel();
        $result = $SecurityGroupConfigModel->sortConfigs($param['ids'] ?? []);
        
        return json($result);
    }
}
