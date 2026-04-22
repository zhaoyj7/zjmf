<?php
namespace app\home\controller;

use app\common\model\ApiModel;
use app\home\validate\ApiValidate;

/**
 * @title API管理
 * @desc API管理
 * @use app\home\controller\ApiController
 */
class ApiController extends HomeBaseController
{   
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ApiValidate();
    }

    /**
     * 时间 2022-07-06
     * @title API密钥列表
     * @desc API密钥列表
     * @author theworld
     * @version v1
     * @url /console/v1/api
     * @method GET
     * @param int page 1 desc:页数 validate:optional
     * @param int limit 10 desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 id validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:API密钥列表
     * @return string list[].name - desc:API密钥名称
     * @return int list[].id - desc:API密钥ID
     * @return string list[].token - desc:token
     * @return int list[].create_time - desc:创建时间
     * @return int list[].status - desc:白名单状态 0关闭 1开启
     * @return string list[].ip - desc:白名单IP
     * @return int count - desc:API总数
     * @return int create_api - desc:是否可创建API 0否 1是
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $ApiModel = new ApiModel();

        // 获取API密钥列表
        $data = $ApiModel->apiList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-07-06
     * @title 创建API密钥
     * @desc 创建API密钥
     * @author theworld
     * @version v1
     * @url /console/v1/api
     * @method POST
     * @param string name - desc:API密钥名称 validate:required
     * @return string name - desc:API密钥名称
     * @return int id - desc:API密钥ID
     * @return string token - desc:token
     * @return int create_time - desc:创建时间
     * @return string private_key - desc:私钥
     * @return string api_url - desc:API接口地址
     */
    public function create()
    {
        $param = $this->request->param();
        
        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ApiModel = new ApiModel();

        // 创建API密钥
        $result = $ApiModel->createApi($param);

        return json($result);
    }

    /**
     * 时间 2022-07-06
     * @title API白名单设置
     * @desc API白名单设置
     * @author theworld
     * @version v1
     * @url /console/v1/api/:id/white_list
     * @method PUT
     * @param int id - desc:API密钥ID validate:required
     * @param int status - desc:白名单状态 0关闭 1开启 validate:required
     * @param string ip - desc:白名单IP 白名单开启时必填 validate:optional
     */
    public function whiteListSetting()
    {
        $param = $this->request->param();
        
        // 参数验证
        if (!$this->validate->scene('white_list')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ApiModel = new ApiModel();

        // API白名单设置
        $result = $ApiModel->whiteListSetting($param);

        return json($result);
    }

    /**
     * 时间 2022-07-06
     * @title 删除API密钥
     * @desc 删除API密钥
     * @author theworld
     * @version v1
     * @url /console/v1/api/:id
     * @method DELETE
     * @param int id - desc:API密钥ID validate:required
     */
    public function delete()
    {
        $param = $this->request->param();
        
        // 实例化模型类
        $ApiModel = new ApiModel();

        // 删除API
        $result = $ApiModel->deleteApi($param['id']);

        return json($result);
    }
}
