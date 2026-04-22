<?php
namespace app\admin\controller;

use app\common\model\SelfDefinedFieldModel;
use app\common\model\SelfDefinedFieldLinkModel;
use app\common\validate\SelfDefinedFieldValidate;

/**
 * @title 自定义字段管理
 * @desc 自定义字段管理
 * @use app\admin\controller\SelfDefinedFieldController
 */
class SelfDefinedFieldController extends AdminBaseController
{
	public function initialize()
    {
        parent::initialize();
    }

    /**
     * 时间 2024-01-02
     * @title 自定义字段列表
     * @desc 自定义字段列表
     * @url /admin/v1/self_defined_field
     * @method GET
     * @author hh
     * @version v1
     * @param string type - desc:类型 product商品 product_group商品组 validate:optional
     * @param int relid - desc:关联ID 商品ID validate:optional
     * @return int list[].id - desc:自定义字段ID
     * @return string list[].field_name - desc:字段名称
     * @return int list[].is_required - desc:是否必填 0否 1是
     * @return string list[].field_type - desc:字段类型 text文本框 link链接 password密码 dropdown下拉 tickbox勾选框 textarea文本区 explain说明
     * @return string list[].description - desc:字段描述
     * @return string list[].regexpr - desc:验证规则
     * @return string list[].field_option - desc:下拉选项
     * @return int list[].show_order_page - desc:订单页可见 0否 1是
     * @return int list[].show_order_detail - desc:订单详情可见 0否 1是
     * @return int list[].show_client_host_detail - desc:前台产品详情可见 0否 1是
     * @return int list[].show_admin_host_detail - desc:后台产品详情可见 0否 1是
     * @return int list[].show_client_host_list - desc:会员中心列表显示 0否 1是
     * @return int list[].show_admin_host_list - desc:后台产品列表显示 0否 1是
     * @return int list[].upstream_id - desc:上游ID 大于0不能修改删除
     * @return string list[].explain_content - desc:说明内容
     * @return int list[].is_global - desc:全局 0否 1是
     * @return array list[].product_group - desc:关联商品分组 类型为商品组时返回
     * @return int list[].product_group[].id - desc:关联商品分组ID
     * @return string list[].product_group[].first_group_name - desc:一级分组名称
     * @return string list[].product_group[].name - desc:关联商品分组名称
     * @return int count - desc:总条数
     */
    public function selfDefinedFieldList()
    {
        $param = $this->request->param();
        
        $SelfDefinedFieldModel = new SelfDefinedFieldModel();

        $data = $SelfDefinedFieldModel->selfDefinedFieldList($param);

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-01-02
     * @title 添加自定义字段
     * @desc 添加自定义字段
     * @url /admin/v1/self_defined_field
     * @method POST
     * @author hh
     * @version v1
     * @param string type - desc:类型 product商品 product_group商品组 validate:required
     * @param int relid - desc:关联ID 商品ID 类型为product时必填 validate:optional
     * @param string field_name - desc:字段名称 validate:required
     * @param int is_required - desc:是否必填 0否 1是 validate:required
     * @param string field_type - desc:字段类型 text文本框 link链接 password密码 dropdown下拉 tickbox勾选框 textarea文本区 explain说明 validate:required
     * @param string description - desc:字段描述 validate:optional
     * @param string regexpr - desc:验证规则 validate:optional
     * @param string field_option - desc:下拉选项 field_type=dropdown时必填 validate:optional
     * @param int show_order_page - desc:订单页可见 0否 1是 validate:required
     * @param int show_order_detail - desc:订单详情可见 0否 1是 validate:required
     * @param int show_client_host_detail - desc:前台产品详情可见 0否 1是 validate:required
     * @param int show_admin_host_detail - desc:后台产品详情可见 0否 1是 validate:required
     * @param int show_client_host_list - desc:会员中心列表显示 0否 1是 validate:required
     * @param int show_admin_host_list - desc:后台产品列表显示 0否 1是 validate:required
     * @param string explain_content - desc:说明内容 field_type=explain时可用 validate:optional
     * @param int is_global - desc:全局 0否 1是 类型为product_group时必填 validate:optional
     * @return int id - desc:自定义字段ID
     */
    public function create()
    {
        $param = $this->request->param();

        $SelfDefinedFieldValidate = new SelfDefinedFieldValidate();
        if(isset($param['field_type']) && $param['field_type'] == 'explain'){
            // 说明验证
            if (!$SelfDefinedFieldValidate->scene('explain_create')->check($param)){
                return json(['status' => 400 , 'msg' => lang($SelfDefinedFieldValidate->getError())]);
            }
        }else{
            if (!$SelfDefinedFieldValidate->scene('create')->check($param)){
                return json(['status' => 400 , 'msg' => lang($SelfDefinedFieldValidate->getError())]);
            }
        }

        $SelfDefinedFieldModel = new SelfDefinedFieldModel();
        
        $result = $SelfDefinedFieldModel->selfDefinedFieldCreate($param);
        return json($result);
    }

    /**
     * 时间 2024-01-02
     * @title 修改自定义字段
     * @desc 修改自定义字段
     * @url /admin/v1/self_defined_field/:id
     * @method PUT
     * @author hh
     * @version v1
     * @param int id - desc:自定义字段ID validate:required
     * @param string field_name - desc:字段名称 validate:required
     * @param int is_required - desc:是否必填 0否 1是 validate:required
     * @param string field_type - desc:字段类型 text文本框 link链接 password密码 dropdown下拉 tickbox勾选框 textarea文本区 explain说明 validate:required
     * @param string description - desc:字段描述 validate:optional
     * @param string regexpr - desc:验证规则 validate:optional
     * @param string field_option - desc:下拉选项 field_type=dropdown时必填 validate:optional
     * @param int show_order_page - desc:订单页可见 0否 1是 validate:required
     * @param int show_order_detail - desc:订单详情可见 0否 1是 validate:required
     * @param int show_client_host_detail - desc:前台产品详情可见 0否 1是 validate:required
     * @param int show_admin_host_detail - desc:后台产品详情可见 0否 1是 validate:required
     * @param int show_client_host_list - desc:会员中心列表显示 0否 1是 validate:required
     * @param int show_admin_host_list - desc:后台产品列表显示 0否 1是 validate:required
     * @param string explain_content - desc:说明内容 field_type=explain时可用 validate:optional
     * @param int is_global - desc:全局 0否 1是 validate:optional
     */
    public function update()
    {
        $param = $this->request->param();

        $SelfDefinedFieldValidate = new SelfDefinedFieldValidate();
        if(isset($param['field_type']) && $param['field_type'] == 'explain'){
            // 说明验证
            if (!$SelfDefinedFieldValidate->scene('explain_update')->check($param)){
                return json(['status' => 400 , 'msg' => lang($SelfDefinedFieldValidate->getError())]);
            }
        }else{
            if (!$SelfDefinedFieldValidate->scene('update')->check($param)){
                return json(['status' => 400 , 'msg' => lang($SelfDefinedFieldValidate->getError())]);
            }
        }

        $SelfDefinedFieldModel = new SelfDefinedFieldModel();
        
        $result = $SelfDefinedFieldModel->selfDefinedFieldUpdate($param);
        return json($result);
    }

    /**
    * 时间 2024-01-02
    * @title 删除自定义字段
    * @desc 删除自定义字段
    * @url /admin/v1/self_defined_field/:id
    * @method DELETE
    * @author hh
    * @version v1
    * @param int id - desc:自定义字段ID validate:required
    */
    public function delete()
    {
        $param = $this->request->param();

        $SelfDefinedFieldModel = new SelfDefinedFieldModel();
        
        $result = $SelfDefinedFieldModel->selfDefinedFieldDelete($param);
        return json($result);
    }

    /**
     * 时间 2024-01-02
     * @title 拖动排序
     * @desc 拖动排序
     * @url /admin/v1/self_defined_field/:id/drag
     * @method PUT
     * @author hh
     * @version v1
     * @param int prev_id - desc:前一个自定义字段ID 0表示置顶 validate:required
     * @param int id - desc:当前自定义字段ID validate:required
     */
    public function dragToSort()
    {
        $param = request()->param();

        $SelfDefinedFieldValidate = new SelfDefinedFieldValidate();
        if (!$SelfDefinedFieldValidate->scene('drag')->check($param)){
            return json(['status' => 400 , 'msg' => lang($SelfDefinedFieldValidate->getError())]);
        }        
        $SelfDefinedFieldModel = new SelfDefinedFieldModel();

        $result = $SelfDefinedFieldModel->dragToSort($param);
        return json($result);
    }

    /**
     * 时间 2024-10-23
     * @title 关联商品组
     * @desc 关联商品组
     * @author theworld
     * @version v1
     * @url /admin/v1/self_defined_field/:id/related_product_group
     * @method PUT
     * @param int id - desc:自定义字段ID 仅限类型为product_group validate:required
     * @param array product_group_id - desc:二级商品分组ID validate:required
     */
    public function relatedProductGroup()
    {
        $param = request()->param();

        $SelfDefinedFieldValidate = new SelfDefinedFieldValidate();
        if (!$SelfDefinedFieldValidate->scene('related')->check($param)){
            return json(['status' => 400 , 'msg' => lang($SelfDefinedFieldValidate->getError())]);
        }        
        $SelfDefinedFieldLinkModel = new SelfDefinedFieldLinkModel();

        $result = $SelfDefinedFieldLinkModel->relatedProductGroup($param);
        return json($result);
    }

    /**
     * 时间 2024-10-23
     * @title 全局自定义字段切换
     * @desc 全局自定义字段切换
     * @author theworld
     * @version v1
     * @url /admin/v1/self_defined_field/:id/is_global
     * @method PUT
     * @param int id - desc:自定义字段ID 仅限类型为product_group validate:required
     * @param int is_global - desc:是否为全局自定义字段 0否 1是 validate:required
     * @param int remove - desc:是否移除关联商品组下商品同名自定义字段 0否 1是 validate:optional
     */
    public function isGlobalUpdate()
    {
        $param = request()->param();

        $SelfDefinedFieldValidate = new SelfDefinedFieldValidate();
        if (!$SelfDefinedFieldValidate->scene('global_update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($SelfDefinedFieldValidate->getError())]);
        }        
        $SelfDefinedFieldModel = new SelfDefinedFieldModel();

        $result = $SelfDefinedFieldModel->isGlobalUpdate($param);
        return json($result);
    }
}