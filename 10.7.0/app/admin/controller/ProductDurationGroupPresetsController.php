<?php
namespace app\admin\controller;

use app\admin\model\ProductDurationGroupPresetsModel;
use app\admin\validate\ProductDurationGroupPresetsValidate;

/**
 * @title 商品周期预设管理
 * @desc 商品周期预设管理
 * @use app\admin\controller\ProductDurationGroupPresetsController
 */
class ProductDurationGroupPresetsController extends AdminBaseController
{
    private $validate;
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ProductDurationGroupPresetsValidate();
    }

    /**
     * 时间 2024-10-23
     * @title 预设列表
     * @desc 预设列表
     * @url /admin/v1/product_duration_group_presets
     * @method GET
     * @author wyh
     * @version v1
     * @return array list - desc:预设列表
     * @return int list[].id - desc:分组ID
     * @return string list[].name - desc:分组名称
     * @return array list[].duration_info - desc:周期信息
     * @return string list[].duration_info[].name - desc:周期名称
     * @return int list[].duration_info[].num - desc:周期时长
     * @return string list[].duration_info[].unit - desc:周期单位 hour小时 day天 month自然月
     * @return int list[].ratio_open - desc:是否开启周期比例
     * @return array list[].ration_info - desc:周期比例信息
     * @return string list[].ration_info[].name - desc:周期名称
     * @return float list[].ration_info[].ratio - desc:周期比例
     * @return int count - desc:预设总数
     */
    public function presetsList()
    {
        # 合并分页参数
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);
        
        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new ProductDurationGroupPresetsModel())->presetsList($param)
        ];
       return json($result);
    }

    /**
     * 时间 2024-10-23
     * @title 获取周期预设信息
     * @desc 获取周期预设信息
     * @url /admin/v1/product_duration_group_presets/:id
     * @method GET
     * @author wyh
     * @version v1
     * @param int id - desc:周期分组预设ID validate:required
     * @return object presets - desc:预设信息
     * @return int presets.id - desc:分组预设ID
     * @return string presets.name - desc:分组名称
     * @return int presets.ratio_open - desc:是否开启周期比例
     * @return array presets.durations - desc:周期信息
     * @return int presets.durations[].id - desc:周期ID
     * @return string presets.durations[].name - desc:周期名称
     * @return int presets.durations[].num - desc:周期时长
     * @return string presets.durations[].unit - desc:周期单位 hour小时 day天 month自然月
     * @return float presets.durations[].ratio - desc:周期比例
     */
    public function index()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('index')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>[
                'presets' => (new ProductDurationGroupPresetsModel())->indexPresets(intval($param['id']))
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2024-10-23
     * @title 新建周期配置组
     * @desc 新建周期配置组
     * @url /admin/v1/product_duration_group_presets
     * @method POST
     * @author wyh
     * @version v1
     * @param string name - desc:分组名称 validate:required
     * @param int ratio_open - desc:周期比例开关 0关 1开 validate:required
     * @param array durations - desc:周期信息 validate:required
     * @param string durations[].name - desc:周期名称 validate:required
     * @param int durations[].num - desc:周期时长 validate:required
     * @param string durations[].unit - desc:周期单位 hour小时 day天 month自然月 validate:required
     * @param float durations[].ratio - desc:周期比例 可默认传0 validate:required
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ProductDurationGroupPresetsModel())->createPresets($param);

        return json($result);
    }

    /**
     * 时间 2024-10-23
     * @title 修改周期配置组
     * @desc 修改周期配置组
     * @url /admin/v1/product_duration_group_presets/:id
     * @method PUT
     * @author wyh
     * @version v1
     * @param int id - desc:周期分组预设ID validate:required
     * @param string name - desc:分组名称 validate:required
     * @param int ratio_open - desc:周期比例开关 0关 1开 validate:required
     * @param array durations - desc:周期信息 validate:required
     * @param string durations[].name - desc:周期名称 validate:required
     * @param int durations[].num - desc:周期时长 validate:required
     * @param string durations[].unit - desc:周期单位 hour小时 day天 month自然月 validate:required
     * @param float durations[].ratio - desc:周期比例 可默认传0 validate:required
     */
    public function update()
    {
        $param = $this->request->param();
        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
        $result = (new ProductDurationGroupPresetsModel())->updatePresets($param);

        return json($result);
    }

    /**
     * 时间 2024-10-23
     * @title 删除周期配置组
     * @desc 删除周期配置组
     * @url /admin/v1/product_duration_group_presets/:id
     * @method DELETE
     * @author wyh
     * @version v1
     * @param int id - desc:周期分组预设ID validate:required
     */
    public function delete()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('delete')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ProductDurationGroupPresetsModel())->deletePresets($param);

        return json($result);
    }

    /**
     * 时间 2024-10-23
     * @title 周期配置组复制
     * @desc 周期配置组复制
     * @url /admin/v1/product_duration_group_presets/:id/copy
     * @method POST
     * @author wyh
     * @version v1
     * @param int id - desc:周期分组预设ID validate:required
     */
    public function copy()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('copy')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ProductDurationGroupPresetsModel())->copyPresets($param);

        return json($result);
    }

}

