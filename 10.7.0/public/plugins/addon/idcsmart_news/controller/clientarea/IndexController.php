<?php
namespace addon\idcsmart_news\controller\clientarea;

use app\event\controller\PluginBaseController;
use addon\idcsmart_news\model\IdcsmartNewsModel;
use addon\idcsmart_news\model\IdcsmartNewsTypeModel;

/**
 * @title 新闻中心
 * @desc 新闻中心
 * @use addon\idcsmart_news\controller\clientarea\IndexController
 */
class IndexController extends PluginBaseController
{
    /**
     * 时间 2022-06-21
     * @title 获取新闻分类
     * @desc 获取新闻分类
     * @author theworld
     * @version v1
     * @url /console/v1/news/type
     * @method GET
     * @return array list - desc:新闻分类列表
     * @return int list[].id - desc:新闻分类ID
     * @return string list[].name - desc:名称
     * @return int list[].news_num - desc:新闻数量
     * @return int count - desc:全部新闻数量
     */
    public function idcsmartNewsTypeList()
    {
        // 实例化模型类
        $IdcsmartNewsTypeModel = new IdcsmartNewsTypeModel();

        // 获取新闻列表
        $data = $IdcsmartNewsTypeModel->idcsmartNewsTypeList('home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-10-13
     * @title 会员中心首页新闻列表
     * @desc 会员中心首页新闻列表
     * @author theworld
     * @version v1
     * @url /console/v1/news/index
     * @method GET
     * @param string keywords - desc:关键字 搜索范围标题 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 id validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:新闻列表
     * @return int list[].id - desc:新闻ID
     * @return string list[].title - desc:标题
     * @return string list[].img - desc:新闻缩略图
     * @return string list[].type - desc:类型
     * @return int list[].create_time - desc:创建时间
     */
    public function indexIdcsmartNewsList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        $param['limit'] = 5;

        // 实例化模型类
        $IdcsmartNewsModel = new IdcsmartNewsModel();

        // 获取新闻列表
        $data = $IdcsmartNewsModel->idcsmartNewsList($param, 'index');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 新闻列表
     * @desc 新闻列表
     * @author theworld
     * @version v1
     * @url /console/v1/news
     * @method GET
     * @param int addon_idcsmart_news_type_id - desc:分类ID validate:optional
     * @param string keywords - desc:关键字 搜索范围标题 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 id validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:新闻列表
     * @return int list[].id - desc:新闻ID
     * @return string list[].title - desc:标题
     * @return string list[].img - desc:新闻缩略图
     * @return int list[].create_time - desc:创建时间
     */
    public function idcsmartNewsList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $IdcsmartNewsModel = new IdcsmartNewsModel();

        // 获取新闻列表
        $data = $IdcsmartNewsModel->idcsmartNewsList($param, 'home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 新闻详情
     * @desc 新闻详情
     * @author theworld
     * @version v1
     * @url /console/v1/news/:id
     * @method GET
     * @param int id - desc:新闻ID validate:required
     * @return object news - desc:新闻
     * @return int news.id - desc:新闻ID
     * @return int news.addon_idcsmart_news_type_id - desc:分类ID
     * @return string news.type - desc:分类名
     * @return string news.title - desc:标题
     * @return string news.content - desc:内容
     * @return string news.keywords - desc:关键字
     * @return string news.attachment - desc:附件
     * @return int news.create_time - desc:创建时间
     * @return int news.update_time - desc:更新时间
     * @return object news.prev - desc:上一条新闻
     * @return int news.prev.id - desc:新闻ID
     * @return string news.prev.title - desc:标题
     * @return object news.next - desc:下一条新闻
     * @return int news.next.id - desc:新闻ID
     * @return string news.next.title - desc:标题
     */
    public function idcsmartNewsDetail()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartNewsModel = new IdcsmartNewsModel();

        // 获取新闻
        $news = $IdcsmartNewsModel->idcsmartNewsDetail($param['id'], 'home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'news' => $news
            ]
        ];
        return json($result);
    }
}