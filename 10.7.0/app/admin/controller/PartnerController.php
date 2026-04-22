<?php
namespace app\admin\controller;

use app\common\model\PartnerModel;
use app\admin\validate\PartnerValidate;

/**
 * @title 合作伙伴
 * @desc 合作伙伴
 * @use app\admin\controller\PartnerController
 */
class PartnerController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new PartnerValidate();
    }

    /**
     * 时间 2023-02-28
     * @title 获取合作伙伴
     * @desc 获取合作伙伴
     * @url /admin/v1/partner
     * @method GET
     * @author theworld
     * @version v1
     * @return array list - desc:合作伙伴列表
     * @return int list[].id - desc:合作伙伴ID
     * @return string list[].name - desc:名称
     * @return string list[].img - desc:图片地址
     * @return string list[].description - desc:描述
     */
    public function list()
    { 
        // 实例化模型类
        $PartnerModel = new PartnerModel();

        // 获取合作伙伴
        $data = $PartnerModel->partnerList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 添加合作伙伴
     * @desc 添加合作伙伴
     * @url /admin/v1/partner
     * @method POST
     * @author theworld
     * @version v1
     * @param string name - desc:名称 validate:required
     * @param string img - desc:图片地址 validate:required
     * @param string description - desc:描述 validate:required
     */
    public function create()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $PartnerModel = new PartnerModel();
        
        // 新建合作伙伴
        $result = $PartnerModel->createPartner($param);

        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 编辑合作伙伴
     * @desc 编辑合作伙伴
     * @url /admin/v1/partner/:id
     * @method PUT
     * @author theworld
     * @version v1
     * @param int id - desc:合作伙伴ID validate:required
     * @param string name - desc:名称 validate:required
     * @param string img - desc:图片地址 validate:required
     * @param string description - desc:描述 validate:required
     */
    public function update()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $PartnerModel = new PartnerModel();
        
        // 修改合作伙伴
        $result = $PartnerModel->updatePartner($param);

        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 删除合作伙伴
     * @desc 删除合作伙伴
     * @url /admin/v1/partner/:id
     * @method DELETE
     * @author theworld
     * @version v1
     * @param int id - desc:合作伙伴ID validate:required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $PartnerModel = new PartnerModel();
        
        // 删除合作伙伴
        $result = $PartnerModel->deletePartner($param['id']);

        return json($result);

    }

    
}