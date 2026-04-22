<?php
namespace app\admin\controller;

use app\admin\model\ProductDurationGroupPresetsLinkModel;
use app\admin\validate\ProductDurationGroupPresetsLinkValidate;

/**
 * @title 商品周期预设分组关联管理
 * @desc 商品周期预设分组关联管理
 * @use app\admin\controller\ProductDurationGroupPresetsLinkController
 */
class ProductDurationGroupPresetsLinkController extends AdminBaseController
{
    private $validate;
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ProductDurationGroupPresetsLinkValidate();
    }

    /**
     * 时间 2024-10-23
     * @title 关联列表
     * @desc 关联列表
     * @url /admin/v1/product_duration_group_presets_link
     * @method GET
     * @author wyh
     * @version v1
     * @param string keywords - desc:关键字 搜索范围:分组名称 接口名称 validate:optional
     * @return array list - desc:关联列表
     * @return string list[].name - desc:分组名称
     * @return int list[].gid - desc:分组ID
     * @return array list[].servers - desc:接口
     * @return int list[].servers[].server_id - desc:接口ID
     * @return string list[].servers[].server_name - desc:接口名称
     * @return int count - desc:关联总数
     */
    public function linkList()
    {
        # 合并分页参数
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);
        
        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new ProductDurationGroupPresetsLinkModel())->linkList($param)
        ];
       return json($result);
    }

    /**
     * 时间 2024-10-23
     * @title 新建周期配置组关联
     * @desc 新建周期配置组关联
     * @url /admin/v1/product_duration_group_presets_link
     * @method POST
     * @author wyh
     * @version v1
     * @param array server_ids - desc:接口ID数组 validate:required
     * @param int gid - desc:分组ID validate:required
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ProductDurationGroupPresetsLinkModel())->creatLink($param);

        return json($result);
    }

    /**
     * 时间 2024-10-23
     * @title 编辑周期配置组关联
     * @desc 编辑周期配置组关联
     * @url /admin/v1/product_duration_group_presets_link/:gid
     * @method PUT
     * @author wyh
     * @version v1
     * @param array server_ids - desc:接口ID数组 validate:required
     * @param int gid - desc:分组ID validate:required
     */
    public function update()
    {
        $param = $this->request->param();
        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
        $result = (new ProductDurationGroupPresetsLinkModel())->updateLink($param);

        return json($result);
    }

    /**
     * 时间 2024-10-23
     * @title 删除周期配置组关联
     * @desc 删除周期配置组关联
     * @url /admin/v1/product_duration_group_presets_link/:gid
     * @method DELETE
     * @author wyh
     * @version v1
     * @param int gid - desc:分组ID validate:required
     */
    public function delete()
    {
        $param = $this->request->param();

        $result = (new ProductDurationGroupPresetsLinkModel())->deleteLink($param);

        return json($result);
    }

}

