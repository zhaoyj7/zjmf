<?php
namespace addon\idcsmart_help\controller\clientarea;

use app\event\controller\PluginBaseController;
use addon\idcsmart_help\model\IdcsmartHelpModel;
use addon\idcsmart_help\model\IdcsmartHelpTypeModel;

/**
 * @title 帮助中心
 * @desc 帮助中心
 * @use addon\idcsmart_help\controller\clientarea\IndexController
 */
class IndexController extends PluginBaseController
{
    /**
     * 时间 2022-06-21
     * @title 帮助中心首页
     * @desc 帮助中心首页
     * @author theworld
     * @version v1
     * @url /console/v1/help/index
     * @method GET
     * @return array index - desc:帮助中心首页列表
     * @return int index[].id - desc:帮助文档分类ID
     * @return string index[].name - desc:帮助文档分类名称
     * @return array index[].helps - desc:帮助文档列表
     * @return int index[].helps[].id - desc:帮助文档ID
     * @return string index[].helps[].title - desc:帮助文档标题
     */
    public function indexIdcsmartHelp()
    {
        // 实例化模型类
        $IdcsmartHelpTypeModel = new IdcsmartHelpTypeModel();

        // 获取帮助中心首页数据
        $data = $IdcsmartHelpTypeModel->indexIdcsmartHelp('home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-20
     * @title 帮助文档列表
     * @desc 帮助文档列表
     * @author theworld
     * @version v1
     * @url /console/v1/help
     * @method GET
     * @param string keywords - desc:关键字 搜索范围标题 validate:optional
     * @return array list - desc:帮助文档列表
     * @return int list[].id - desc:帮助文档分类ID
     * @return string list[].name - desc:帮助文档分类名称
     * @return array list[].helps - desc:帮助文档列表
     * @return int list[].helps[].id - desc:帮助文档ID
     * @return string list[].helps[].title - desc:帮助文档标题
     * @return boolean list[].helps[].search - desc:是否被关键字匹配 true为匹配到
     */
    public function idcsmartHelp()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartHelpTypeModel = new IdcsmartHelpTypeModel();

        // 获取帮助文档列表
        $data = $IdcsmartHelpTypeModel->idcsmartHelpTypeList($param, 'home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-20
     * @title 帮助文档详情
     * @desc 帮助文档详情
     * @author theworld
     * @version v1
     * @url /console/v1/help/:id
     * @method GET
     * @param int id - desc:帮助文档ID validate:required
     * @return object help - desc:帮助文档
     * @return int help.id - desc:帮助文档ID
     * @return string help.title - desc:标题
     * @return string help.content - desc:内容
     * @return string help.keywords - desc:关键字
     * @return string help.attachment - desc:附件
     * @return int help.create_time - desc:创建时间
     * @return int help.update_time - desc:更新时间
     * @return object help.prev - desc:上一篇文档
     * @return int help.prev.id - desc:文档ID
     * @return string help.prev.title - desc:标题
     * @return object help.next - desc:下一篇文档
     * @return int help.next.id - desc:文档ID
     * @return string help.next.title - desc:标题
     */
    public function idcsmartHelpDetail()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartHelpModel = new IdcsmartHelpModel();

        // 获取帮助文档
        $help = $IdcsmartHelpModel->idcsmartHelpDetail($param['id'], 'home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'help' => $help
            ]
        ];
        return json($result);
    }
}