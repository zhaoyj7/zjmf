<?php 
namespace addon\idcsmart_cloud\logic;

use app\common\model\UpstreamProductModel;
use app\common\model\UpstreamHostModel;
use app\common\model\SupplierModel;

class DefaultSupplierLogic{

    // 供应商ID
    protected $supplierId = 0;

    // 供应商信息
    protected $supplierInfo = [];

    public function __construct($supplierId)
    {
        $SupplierModel = new SupplierModel();
        $supplier = $SupplierModel->find($supplierId);
        if(empty($supplier) || $supplier['type'] != 'default' ){
            throw new \Exception('no support');
        }
        $this->supplierId = $supplierId;
        $this->supplierInfo = $supplier->toArray();
    }


    




    /**
     * 时间 2022-06-08
     * @title 安全组列表
     * @desc 安全组列表
     * @author theworld
     * @version v1
     * @param array param - 请求参数
     * @return array
     */
    public function securityGroupList($param = [])
    {
        return $this->curl('console/v1/security_group', $param, 'GET');
    }

    /**
     * 时间 2022-06-08
     * @title 安全组详情
     * @desc 安全组详情
     * @author theworld
     * @version v1
     * @param int id - 安全组ID
     * @return array
     */
    public function securityGroupDetail($id)
    {
        return $this->curl('console/v1/security_group/' . $id, [], 'GET');
    }

    /**
     * 时间 2022-06-08
     * @title 添加安全组
     * @desc 添加安全组
     * @author theworld
     * @version v1
     * @param array param - 请求参数
     * @return array
     */
    public function createSecurityGroup($param)
    {
        // 上游不要自动创建规则，否则有问题
        $param['auto_create_rule'] = 0;

        return $this->curl('console/v1/security_group', $param, 'POST');
    }

    /**
     * 时间 2022-06-08
     * @title 修改安全组
     * @desc 修改安全组
     * @author theworld
     * @version v1
     * @param int id - 安全组ID
     * @param array param - 请求参数
     * @return array
     */
    public function updateSecurityGroup($id, $param)
    {
        return $this->curl('console/v1/security_group/' . $id, $param, 'PUT');
    }

    /**
     * 时间 2022-06-08
     * @title 删除安全组
     * @desc 删除安全组
     * @author theworld
     * @version v1
     * @param int id - 安全组ID
     * @return array
     */
    public function deleteSecurityGroup($id)
    {
        return $this->curl('console/v1/security_group/' . $id, [], 'DELETE');
    }

    /**
     * 时间 2022-06-09
     * @title 安全组实例列表
     * @desc 安全组实例列表
     * @author theworld
     * @version v1
     * @param int id - 安全组ID
     * @param array param - 请求参数
     * @return array
     */
    public function securityGroupHostList($id, $param = [])
    {
        return $this->curl('console/v1/security_group/' . $id . '/host', $param, 'GET');
    }

    /**
     * 时间 2022-09-08
     * @title 关联安全组
     * @desc 关联安全组
     * @author theworld
     * @version v1
     * @param int id - 安全组ID
     * @param int host_id - 产品ID
     * @return array
     */
    public function linkSecurityGroup($id, $hostId)
    {
        return $this->curl('console/v1/security_group/' . $id . '/host/' . $hostId, [], 'POST');
    }

    /**
     * 时间 2022-09-08
     * @title 取消关联安全组
     * @desc 取消关联安全组
     * @author theworld
     * @version v1
     * @param int id - 安全组ID
     * @param int host_id - 产品ID
     * @return array
     */
    public function unlinkSecurityGroup($id, $hostId)
    {
        return $this->curl('console/v1/security_group/' . $id . '/host/' . $hostId, [], 'DELETE');
    }

    /**
     * 时间 2024-07-02
     * @title 批量关联安全组
     * @desc 批量关联安全组
     * @author hh
     * @version v1
     * @param int id - 安全组ID
     * @param array param - 请求参数
     * @return array
     */
    public function batchLinkSecurityGroup($id, $param)
    {
        return $this->curl('console/v1/security_group/' . $id . '/host', $param, 'POST');
    }

    /**
     * 时间 2022-06-09
     * @title 安全组规则列表
     * @desc 安全组规则列表
     * @author theworld
     * @version v1
     * @param int id - 安全组ID
     * @param array param - 请求参数
     * @return array
     */
    public function securityGroupRuleList($id, $param = [])
    {
        return $this->curl('console/v1/security_group/' . $id . '/rule', $param, 'GET');
    }

    /**
     * 时间 2022-06-09
     * @title 安全组规则详情
     * @desc 安全组规则详情
     * @author theworld
     * @version v1
     * @param int id - 安全组规则ID
     * @return array
     */
    public function securityGroupRuleDetail($id)
    {
        return $this->curl('console/v1/security_group/rule/' . $id, [], 'GET');
    }

    /**
     * 时间 2022-06-09
     * @title 添加安全组规则
     * @desc 添加安全组规则
     * @author theworld
     * @version v1
     * @param int id - 安全组ID
     * @param array param - 请求参数
     * @return array
     */
    public function createSecurityGroupRule($id, $param)
    {
        return $this->curl('console/v1/security_group/' . $id . '/rule', $param, 'POST');
    }

    /**
     * 时间 2022-08-26
     * @title 批量添加安全组规则
     * @desc 批量添加安全组规则
     * @author theworld
     * @version v1
     * @param int id - 安全组ID
     * @param array param - 请求参数
     * @return array
     */
    public function batchCreateSecurityGroupRule($id, $param)
    {
        return $this->curl('console/v1/security_group/' . $id . '/rule/batch', $param, 'POST');
    }

    /**
     * 时间 2022-06-09
     * @title 修改安全组规则
     * @desc 修改安全组规则
     * @author theworld
     * @version v1
     * @param int id - 安全组规则ID
     * @param array param - 请求参数
     * @return array
     */
    public function updateSecurityGroupRule($id, $param)
    {
        return $this->curl('console/v1/security_group/rule/' . $id, $param, 'PUT');
    }

    /**
     * 时间 2022-06-09
     * @title 删除安全组规则
     * @desc 删除安全组规则
     * @author theworld
     * @version v1
     * @param int id - 安全组规则ID
     * @return array
     */
    public function deleteSecurityGroupRule($id)
    {
        return $this->curl('console/v1/security_group/rule/' . $id, [], 'DELETE');
    }

    /**
     * 时间 2023-02-16
     * @title 请求上游curl
     * @desc  请求上游curl
     * @author hh
     * @version v1
     * @param   string path - 请求地址路由 require
     * @param   array data - 请求参数
     * @param   string request POST 请求方式(POST,GET,DELETE,PUT)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  array data - 其他数据
     */
    public function curl($path, $data = [], $request = 'POST')
    {
        return idcsmart_api_curl($this->supplierId, $path, $data, 60, $request);
    }

}