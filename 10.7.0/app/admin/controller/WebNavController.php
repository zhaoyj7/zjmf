<?php
namespace app\admin\controller;

use app\common\model\WebNavModel;
use app\admin\validate\WebNavValidate;

/**
 * @title 模板控制器-导航
 * @desc 模板控制器-导航
 * @use app\admin\controller\WebNavController
 */
class WebNavController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new WebNavValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 导航列表
     * @desc 导航列表
     * @author theworld
     * @version v1
     * @url /admin/v1/web_nav
     * @method GET
     * @param string theme - desc:主题标识 不传递时默认为当前系统设置的主题 validate:optional
     * @param string language - desc:语言 zh-cn en-us validate:optional
     * @return array list - desc:一级导航
     * @return int list[].id - desc:一级导航ID
     * @return string list[].name - desc:名称
     * @return string list[].file_address - desc:文件地址
     * @return int list[].show - desc:是否展示
     * @return int list[].blank - desc:是否打开新窗口
     * @return array list[].children - desc:二级导航
     * @return int list[].children[].id - desc:二级导航ID
     * @return int list[].children[].parent_id - desc:父导航ID
     * @return string list[].children[].name - desc:名称
     * @return string list[].children[].description - desc:描述
     * @return string list[].children[].file_address - desc:文件地址
     * @return string list[].children[].icon - desc:图标
     * @return int list[].children[].show - desc:是否展示
     * @return int list[].children[].blank - desc:是否打开新窗口
     */
    public function list()
    {
        // 接收参数
        $param = $this->request->param();
        $param['language'] = $param['language'] ?? 'zh-cn';

        // 实例化模型类
        $WebNavModel = new WebNavModel();

        // 导航列表
        $data = $WebNavModel->navList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 创建导航
     * @desc 创建导航
     * @author theworld
     * @version v1
     * @url /admin/v1/web_nav
     * @method POST
     * @param string theme - desc:主题标识 不传递时默认为当前系统设置的主题 validate:optional
     * @param string language - desc:语言 zh-cn en-us validate:optional
     * @param int parent_id - desc:父导航ID validate:optional
     * @param string name - desc:名称 validate:required
     * @param string description - desc:描述 validate:optional
     * @param string file_address - desc:文件地址 validate:optional
     * @param string icon - desc:导航图标 validate:optional
     * @param int show - desc:是否展示 0否 1是 validate:required
     * @param int blank - desc:是否打开新窗口 validate:required
     */
    public function create()
    {
        // 接收参数
        $param = $this->request->param();
        $param['language'] = $param['language'] ?? 'zh-cn';

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $WebNavModel = new WebNavModel();
        
        // 创建导航
        $result = $WebNavModel->createNav($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 编辑导航
     * @desc 编辑导航
     * @author theworld
     * @version v1
     * @url /admin/v1/web_nav/:id
     * @method PUT
     * @param int id - desc:导航ID validate:required
     * @param int parent_id - desc:父导航ID validate:optional
     * @param string name - desc:名称 validate:required
     * @param string description - desc:描述 validate:optional
     * @param string file_address - desc:文件地址 validate:optional
     * @param string icon - desc:导航图标 validate:optional
     * @param int show - desc:是否展示 0否 1是 validate:required
     * @param int blank - desc:是否打开新窗口 validate:required
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
        $WebNavModel = new WebNavModel();
        
        // 编辑导航
        $result = $WebNavModel->updateNav($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除导航
     * @desc 删除导航
     * @author theworld
     * @version v1
     * @url /admin/v1/web_nav/:id
     * @method DELETE
     * @param int id - desc:导航ID validate:required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $WebNavModel = new WebNavModel();
        
        // 删除导航
        $result = $WebNavModel->deleteNav($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 导航显示
     * @desc 导航显示
     * @author theworld
     * @version v1
     * @url /admin/v1/web_nav/:id/show
     * @method PUT
     * @param int id - desc:导航ID validate:required
     * @param int show - desc:是否展示 0否 1是 validate:required
     */
    public function show()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('show')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $WebNavModel = new WebNavModel();
        
        // 导航显示
        $result = $WebNavModel->navShow($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 导航打开新窗口开关
     * @desc 导航打开新窗口开关
     * @author theworld
     * @version v1
     * @url /admin/v1/web_nav/:id/blank
     * @method PUT
     * @param int id - desc:导航ID validate:required
     * @param int blank - desc:是否打开新窗口 0否 1是 validate:required
     */
    public function blank()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('blank')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $WebNavModel = new WebNavModel();
        
        // 导航打开新窗口开关
        $result = $WebNavModel->navBlank($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 一级导航排序
     * @desc 一级导航排序
     * @author theworld
     * @version v1
     * @url /admin/v1/first_web_nav/order
     * @method PUT
     * @param string theme - desc:主题标识 不传递时默认为当前系统设置的主题 validate:optional
     * @param string language - desc:语言 zh-cn en-us validate:optional
     * @param array id - desc:一级导航ID数组 validate:required
     */
    public function firstNavOrder()
    {
        // 接收参数
        $param = $this->request->param();
        $param['language'] = $param['language'] ?? 'zh-cn';

        // 实例化模型类
        $WebNavModel = new WebNavModel();
        
        // 一级导航排序
        $result = $WebNavModel->firstNavOrder($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 二级导航排序
     * @desc 二级导航排序
     * @author theworld
     * @version v1
     * @url /admin/v1/second_web_nav/order
     * @method PUT
     * @param int parent_id - desc:父导航ID validate:required
     * @param array id - desc:二级导航ID数组 validate:required
     */
    public function secondNavOrder()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('order')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $WebNavModel = new WebNavModel();
        
        // 二级导航排序
        $result = $WebNavModel->secondNavOrder($param);

        return json($result);
    }
}