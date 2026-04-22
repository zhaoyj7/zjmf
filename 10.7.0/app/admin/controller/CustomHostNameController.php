<?php
namespace app\admin\controller;

use app\common\model\CustomHostNameModel;
use app\common\model\CustomHostNameLinkModel;
use app\admin\validate\CustomHostNameValidate;

/**
 * @title 自定义产品标识管理
 * @desc 自定义产品标识管理
 * @use app\admin\controller\CustomHostNameController
 */
class CustomHostNameController extends AdminBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new CustomHostNameValidate();
    }

    /**
     * 时间 2024-10-23
     * @title 自定义产品标识列表
     * @desc 自定义产品标识列表
     * @url /admin/v1/custom_host_name
     * @method GET
     * @author theworld
     * @version v1
     * @return int list[].id - desc:自定义产品标识ID
     * @return string list[].custom_host_name_prefix - desc:自定义主机标识前缀
     * @return array list[].custom_host_name_string_allow - desc:允许的字符串 number数字 upper大写字母 lower小写字母
     * @return int list[].custom_host_name_string_length - desc:字符串长度
     * @return array list[].product_group - desc:关联商品分组 类型为商品组时返回
     * @return int list[].product_group[].id - desc:关联商品分组ID
     * @return string list[].product_group[].first_group_name - desc:一级分组名称
     * @return string list[].product_group[].name - desc:关联商品分组名称
     */
	public function list()
    {
        $param = $this->request->param();
        
        $CustomHostNameModel = new CustomHostNameModel();

        $data = $CustomHostNameModel->customHostNameList($param);

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data
        ];
        return json($result);
	}

    /**
     * 时间 2024-10-23
     * @title 添加自定义产品标识
     * @desc 添加自定义产品标识
     * @url /admin/v1/custom_host_name
     * @method POST
     * @author theworld
     * @version v1
     * @param string custom_host_name_prefix - desc:自定义主机标识前缀 validate:required
     * @param array custom_host_name_string_allow - desc:允许的字符串 number数字 upper大写字母 lower小写字母 validate:required
     * @param int custom_host_name_string_length - desc:字符串长度 validate:required
     */
	public function create()
    {
		$param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $CustomHostNameModel = new CustomHostNameModel();
        
        $result = $CustomHostNameModel->createCustomHostName($param);
        return json($result);
	}

    /**
     * 时间 2024-10-23
     * @title 修改自定义产品标识
     * @desc 修改自定义产品标识
     * @url /admin/v1/custom_host_name/:id
     * @method PUT
     * @author theworld
     * @version v1
     * @param int id - desc:自定义产品标识ID validate:required
     * @param string custom_host_name_prefix - desc:自定义主机标识前缀 validate:required
     * @param array custom_host_name_string_allow - desc:允许的字符串 number数字 upper大写字母 lower小写字母 validate:required
     * @param int custom_host_name_string_length - desc:字符串长度 validate:required
     */
    public function update()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $CustomHostNameModel = new CustomHostNameModel();
        
        $result = $CustomHostNameModel->updateCustomHostName($param);
        return json($result);
    }

    /**
     * 时间 2024-10-23
     * @title 删除自定义产品标识
     * @desc 删除自定义产品标识
     * @url /admin/v1/custom_host_name/:id
     * @method DELETE
     * @author theworld
     * @version v1
     * @param int id - desc:自定义产品标识ID validate:required
     */
	public function delete()
    {
        $param = $this->request->param();

        $CustomHostNameModel = new CustomHostNameModel();
        
        $result = $CustomHostNameModel->deleteCustomHostName($param['id']);
        return json($result);
	}

    /**
     * 时间 2024-10-23
     * @title 关联商品组
     * @desc 关联商品组
     * @url /admin/v1/custom_host_name/:id/related_product_group
     * @method PUT
     * @author theworld
     * @version v1
     * @param int id - desc:自定义产品标识ID validate:required
     * @param array product_group_id - desc:二级商品分组ID validate:required
     */
    public function relatedProductGroup()
    {
        $param = request()->param();

        $CustomHostNameValidate = new CustomHostNameValidate();
        if (!$CustomHostNameValidate->scene('related')->check($param)){
            return json(['status' => 400 , 'msg' => lang($CustomHostNameValidate->getError())]);
        }        
        $CustomHostNameLinkModel = new CustomHostNameLinkModel();

        $result = $CustomHostNameLinkModel->relatedProductGroup($param);
        return json($result);
    }

}