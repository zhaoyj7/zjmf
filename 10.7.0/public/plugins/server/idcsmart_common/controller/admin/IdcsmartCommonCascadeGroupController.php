<?php
namespace server\idcsmart_common\controller\admin;

use app\event\controller\BaseController;
use server\idcsmart_common\logic\IdcsmartCommonLogic;
use server\idcsmart_common\model\IdcsmartCommonCascadeGroupModel;
use server\idcsmart_common\validate\IdcsmartCommonCascadeGroupValidate;

/**
 * @title 通用商品-级联组管理
 * @desc 通用商品-级联组管理
 * @use server\idcsmart_common\controller\admin\IdcsmartCommonCascadeGroupController
 */
class IdcsmartCommonCascadeGroupController extends BaseController
{
    public $validate;

    /**
     * 初始化验证
     */
    public function initialize()
    {
        parent::initialize();

        $this->validate = new IdcsmartCommonCascadeGroupValidate();

        $param = $this->request->param();

        $IdcsmartCommonLogic = new IdcsmartCommonLogic();
        $IdcsmartCommonLogic->validateConfigoption($param);
    }

    /**
     * 时间 2024-12-20
     * @title 更新级联组
     * @desc 更新级联组名称
     * @url /admin/v1/idcsmart_common/configoption/:configoption_id/cascade/group/:id
     * @method PUT
     * @author theworld
     * @version v1
     * @param int configoption_id - desc:配置项ID validate:required
     * @param int id - desc:级联组ID validate:required
     * @param string group_name - desc:级联组名称 validate:required
     */
    public function update()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)) {
            return json(['status' => 400, 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();

        $result = $IdcsmartCommonCascadeGroupModel->updateCascadeGroup($param);

        return json($result);
    }

    /**
     * 时间 2024-12-20
     * @title 删除级联组
     * @desc 删除级联组 自动删除下级级联组和级联项 不能删除顶级级联组
     * @url /admin/v1/idcsmart_common/configoption/:configoption_id/cascade/group/:id
     * @method DELETE
     * @author theworld
     * @version v1
     * @param int configoption_id - desc:配置项ID validate:required
     * @param int id - desc:级联组ID validate:required
     */
    public function delete()
    {
        $param = $this->request->param();

        $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();

        $result = $IdcsmartCommonCascadeGroupModel->deleteCascadeGroup($param);

        return json($result);
    }
}
