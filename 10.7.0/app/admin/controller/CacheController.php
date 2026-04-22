<?php
namespace app\admin\controller;

use app\common\logic\CacheLogic;

/**
 * @title 缓存管理控制器
 * @desc  缓存管理控制器
 * @use app\admin\controller\CacheController
 */
class CacheController
{
    /**
     * @title 清除所有缓存
     * @desc 清除所有系统缓存
     * @url admin/cache/clear_all
     * @method POST
     * @author wyh
     * @version v1
     */
    public function clearAll()
    {
        $result = CacheLogic::clearAllCache();
        return json($result);
    }
    
    /**
     * @title 清除插件缓存
     * @desc 清除插件相关缓存 插件列表 钩子 多语言
     * @url admin/cache/clear_plugin
     * @method POST
     * @author wyh
     * @version v1
     */
    public function clearPlugin()
    {
        $result = CacheLogic::clearPluginCache();
        return json($result);
    }
    
    /**
     * @title 清除配置缓存
     * @desc 清除系统配置缓存
     * @url admin/cache/clear_config
     * @method POST
     * @author wyh
     * @version v1
     */
    public function clearConfig()
    {
        $result = CacheLogic::clearConfigCache();
        return json($result);
    }
    
    /**
     * @title 清除语言缓存
     * @desc 清除多语言缓存
     * @url admin/cache/clear_lang
     * @method POST
     * @author wyh
     * @version v1
     */
    public function clearLang()
    {
        $result = CacheLogic::clearLangCache();
        return json($result);
    }
    
    /**
     * @title 清除路由缓存
     * @desc 清除路由缓存文件和降级标志
     * @url admin/cache/clear_route
     * @method POST
     * @author wyh
     * @version v1
     */
    public function clearRoute()
    {
        $result = CacheLogic::clearRouteCache();
        return json($result);
    }
    
    /**
     * @title 获取缓存统计
     * @desc 查看各类缓存的状态
     * @url admin/cache/stats
     * @method GET
     * @author wyh
     * @version v1
     */
    public function stats()
    {
        $result = CacheLogic::getCacheStats();
        return json($result);
    }
}

