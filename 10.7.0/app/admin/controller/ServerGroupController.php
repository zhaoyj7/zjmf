<?php
namespace app\admin\controller;

use app\admin\validate\ServerGroupValidate;
use app\common\model\ServerGroupModel;

/**
 * @title 接口分组管理
 * @desc 接口分组管理
 * @use app\admin\controller\ServerGroupController
 */
class ServerGroupController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ServerGroupValidate();
    }

     /**
     * 时间 2022-05-27
     * @title 接口分组列表
     * @desc 接口分组列表
     * @url /admin/v1/server/group
     * @method GET
     * @author hh
     * @version v1
     * @param string keywords - desc:关键字 接口分组名称 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序 id name validate:optional
     * @param string sort - desc:升/降序 asc desc validate:optional
     * @return array list - desc:接口分组列表
     * @return int list[].id - desc:接口分组ID
     * @return string list[].name - desc:分组名称
     * @return int list[].create_time - desc:创建时间
     * @return array list[].server - desc:接口列表
     * @return int list[].server[].id - desc:接口ID
     * @return string list[].server[].name - desc:接口名称
     * @return int count - desc:接口分组总数
     */
    public function serverGroupList()
    {
        # 合并分页参数
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new ServerGroupModel())->serverGroupList($param)
        ];
       return json($result);
    }


    /**
     * 时间 2022-05-27
     * @title 新建接口分组
     * @desc 新建接口分组
     * @url /admin/v1/server/group
     * @method POST
     * @author hh
     * @version v1
     * @param string name - desc:分组名称 validate:required
     * @param array server_id - desc:接口ID validate:required
     * @return int id - desc:接口分组ID
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ServerGroupModel())->createServerGroup($param);

        return json($result);
    }

    /**
     * 时间 2022-05-27
     * @title 修改接口分组
     * @desc 修改接口分组
     * @url /admin/v1/server/group/:id
     * @method PUT
     * @author hh
     * @version v1
     * @param int id - desc:接口分组ID validate:required
     * @param string name - desc:分组名称 validate:required
     * @param array server_id - desc:接口ID validate:required
     */
    public function update()
    {
        $param = $this->request->param();
        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ServerGroupModel())->updateServerGroup($param);

        return json($result);
    }

    /**
     * 时间 2022-05-27
     * @title 删除接口分组
     * @desc 删除接口分组
     * @url /admin/v1/server/group/:id
     * @method DELETE
     * @author hh
     * @version v1
     * @param int id - desc:接口分组ID validate:required
     */
    public function delete()
    {
        $param = $this->request->param();

        $result = (new ServerGroupModel())->deleteServerGroup(intval($param['id']));

        return json($result);
    }
}

