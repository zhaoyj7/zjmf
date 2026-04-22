<?php
namespace app\admin\controller;

use app\common\model\SeoModel;
use app\admin\validate\SeoValidate;

/**
 * @title 模板控制器-SEO
 * @desc 模板控制器-SEO
 * @use app\admin\controller\SeoController
 */
class SeoController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new SeoValidate();
    }

    /**
     * 时间 2024-04-08
     * @title 获取SEO
     * @desc 获取SEO
     * @author theworld
     * @version v1
     * @url /admin/v1/seo
     * @method GET
     * @param string language - desc:语言 zh-cn en-us validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序 id validate:optional
     * @param string sort - desc:升/降序 asc desc validate:optional
     * @return array list - desc:SEO列表
     * @return int list[].id - desc:SEOID
     * @return string list[].title - desc:标题
     * @return string list[].page_address - desc:页面地址
     * @return string list[].keywords - desc:关键字
     * @return string list[].description - desc:描述
     * @return int count - desc:SEO数量
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        $param['language'] = $param['language'] ?? 'zh-cn';
        
        // 实例化模型类
        $SeoModel = new SeoModel();

        // 获取SEO
        $data = $SeoModel->seoList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-08
     * @title 添加SEO
     * @desc 添加SEO
     * @author theworld
     * @version v1
     * @url /admin/v1/seo
     * @method POST
     * @param string language - desc:语言 zh-cn en-us validate:optional
     * @param string title - desc:标题 validate:required
     * @param string page_address - desc:页面地址 validate:required
     * @param string keywords - desc:关键字 validate:required
     * @param string description - desc:描述 validate:required
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
        $SeoModel = new SeoModel();
        
        // 添加SEO
        $result = $SeoModel->createSeo($param);

        return json($result);
    }

    /**
     * 时间 2024-04-08
     * @title 编辑SEO
     * @desc 编辑SEO
     * @author theworld
     * @version v1
     * @url /admin/v1/seo/:id
     * @method PUT
     * @param int id - desc:SEOID validate:required
     * @param string title - desc:标题 validate:required
     * @param string page_address - desc:页面地址 validate:required
     * @param string keywords - desc:关键字 validate:required
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
        $SeoModel = new SeoModel();
        
        // 编辑SEO
        $result = $SeoModel->updateSeo($param);

        return json($result);
    }

    /**
     * 时间 2024-04-08
     * @title 删除SEO
     * @desc 删除SEO
     * @author theworld
     * @version v1
     * @url /admin/v1/seo/:id
     * @method DELETE
     * @param int id - desc:SEOID validate:required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $SeoModel = new SeoModel();
        
        // 删除SEO
        $result = $SeoModel->deleteSeo($param['id']);

        return json($result);

    }

    
}