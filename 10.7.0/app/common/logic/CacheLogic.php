<?php
namespace app\common\logic;

/**
 * @title 缓存管理逻辑类
 * @desc  统一管理系统缓存，方便清理和维护
 * @use app\common\logic\CacheLogic
 */
class CacheLogic
{
    /**
     * 缓存键定义（统一使用system_前缀）
     */
    const CACHE_PLUGIN_LIST = 'system_plugin_list'; // 插件列表
    const CACHE_PLUGIN_HOOKS = 'system_plugin_hooks_v2'; // 插件钩子
    const CACHE_CONFIGURATION = 'system_configuration_all'; // 系统配置
    const CACHE_LANG_PREFIX = 'system_plugin_lang_'; // 多语言前缀
    const CACHE_ROUTE_DIR = 'runtime/route/'; // 路由缓存目录
    
    /**
     * @title 清除插件相关缓存
     * @desc 在插件安装、卸载、启用、禁用时调用
     */
    public static function clearPluginCache()
    {
        idcsmart_cache(self::CACHE_PLUGIN_LIST, null);
        idcsmart_cache(self::CACHE_PLUGIN_HOOKS, null);
        // 清除多语言缓存
        $langs = ['zh-cn', 'en-us', 'zh-hk'];
        foreach ($langs as $lang) {
            idcsmart_cache(self::CACHE_LANG_PREFIX . $lang, null);
        }
        
        return ['status' => 200, 'msg' => '插件缓存已清除'];
    }
    
    /**
     * @title 清除配置缓存
     * @desc 在修改系统配置时调用
     */
    public static function clearConfigCache()
    {
        idcsmart_cache(self::CACHE_CONFIGURATION, null);
        return ['status' => 200, 'msg' => '配置缓存已清除'];
    }
    
    /**
     * @title 清除多语言缓存
     * @desc 在修改语言包时调用
     */
    public static function clearLangCache()
    {
        $langs = ['zh-cn', 'en-us', 'zh-hk'];
        foreach ($langs as $lang) {
            idcsmart_cache(self::CACHE_LANG_PREFIX . $lang, null);
        }
        return ['status' => 200, 'msg' => '语言缓存已清除'];
    }
    
    /**
     * @title 清除路由缓存
     * @desc 清除路由缓存文件和降级标志
     */
    public static function clearRouteCache()
    {
        $routeDir = IDCSMART_ROOT . self::CACHE_ROUTE_DIR;
        $stats = [
            'cache_files' => 0,
            'fallback_files' => 0,
            'temp_files' => 0,
            'total_size' => 0,
            'errors' => []
        ];
        
        try {
            if (!is_dir($routeDir)) {
                return ['status' => 200, 'msg' => '路由缓存目录不存在', 'data' => $stats];
            }
            
            $files = glob($routeDir . '*');
            if (empty($files)) {
                return ['status' => 200, 'msg' => '没有路由缓存文件需要清除', 'data' => $stats];
            }
            
            foreach ($files as $file) {
                if (!is_file($file)) {
                    continue;
                }
                
                $filename = basename($file);
                $fileSize = filesize($file);
                
                // 保留错误日志文件
                if ($filename === 'cache_error.log') {
                    continue;
                }
                
                try {
                    // 删除路由缓存文件
                    if (preg_match('/^merged_fqn_[a-f0-9]{32}\.php$/', $filename)) {
                        if (@unlink($file)) {
                            $stats['cache_files']++;
                            $stats['total_size'] += $fileSize;
                        } else {
                            $stats['errors'][] = "删除缓存文件失败: {$filename}";
                        }
                    }
                    // 删除降级标志文件
                    elseif (preg_match('/^fallback_[a-f0-9]{32}\.lock$/', $filename)) {
                        if (@unlink($file)) {
                            $stats['fallback_files']++;
                            $stats['total_size'] += $fileSize;
                        } else {
                            $stats['errors'][] = "删除降级标志失败: {$filename}";
                        }
                    }
                    // 删除临时文件
                    elseif (strpos($filename, '.tmp') !== false) {
                        if (@unlink($file)) {
                            $stats['temp_files']++;
                            $stats['total_size'] += $fileSize;
                        } else {
                            $stats['errors'][] = "删除临时文件失败: {$filename}";
                        }
                    }
                } catch (\Exception $e) {
                    $stats['errors'][] = "处理文件 {$filename} 时出错: " . $e->getMessage();
                }
            }
            
            $totalFiles = $stats['cache_files'] + $stats['fallback_files'] + $stats['temp_files'];
            $sizeText = self::formatFileSize($stats['total_size']);
            
            if ($totalFiles > 0) {
                $msg = "路由缓存清除成功，共清除 {$totalFiles} 个文件，释放空间 {$sizeText}";
            } else {
                $msg = "没有找到需要清除的路由缓存文件";
            }
            
            return [
                'status' => 200, 
                'msg' => $msg,
                'data' => $stats
            ];
            
        } catch (\Exception $e) {
            $stats['errors'][] = "清除路由缓存时发生异常: " . $e->getMessage();
            return [
                'status' => 400, 
                'msg' => '路由缓存清除失败',
                'data' => $stats
            ];
        }
    }
    
    /**
     * @title 获取路由缓存统计信息
     * @desc 统计路由缓存文件状态
     */
    public static function getRouteCacheStats()
    {
        $routeDir = IDCSMART_ROOT . self::CACHE_ROUTE_DIR;
        $stats = [
            'cache_files' => 0,
            'fallback_files' => 0,
            'temp_files' => 0,
            'total_size' => 0,
            'last_generated' => null,
            'status' => '未缓存'
        ];
        
        try {
            if (!is_dir($routeDir)) {
                return $stats;
            }
            
            $files = glob($routeDir . '*');
            if (empty($files)) {
                return $stats;
            }
            
            $latestTime = 0;
            foreach ($files as $file) {
                if (!is_file($file)) {
                    continue;
                }
                
                $filename = basename($file);
                $fileSize = filesize($file);
                $fileTime = filemtime($file);
                
                if (preg_match('/^merged_fqn_[a-f0-9]{32}\.php$/', $filename)) {
                    $stats['cache_files']++;
                    $stats['total_size'] += $fileSize;
                    if ($fileTime > $latestTime) {
                        $latestTime = $fileTime;
                    }
                } elseif (preg_match('/^fallback_[a-f0-9]{32}\.lock$/', $filename)) {
                    $stats['fallback_files']++;
                    $stats['total_size'] += $fileSize;
                } elseif (strpos($filename, '.tmp') !== false) {
                    $stats['temp_files']++;
                    $stats['total_size'] += $fileSize;
                }
            }
            
            if ($stats['cache_files'] > 0) {
                $stats['status'] = '已缓存';
                $stats['last_generated'] = date('Y-m-d H:i:s', $latestTime);
            } elseif ($stats['fallback_files'] > 0) {
                $stats['status'] = '降级模式';
            }
            
        } catch (\Exception $e) {
            $stats['status'] = '检查失败';
        }
        
        return $stats;
    }
    
    /**
     * @title 格式化文件大小
     * @desc 将字节数转换为可读的文件大小格式
     */
    private static function formatFileSize($bytes)
    {
        if ($bytes == 0) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor(log($bytes, 1024));
        
        return sprintf('%.2f %s', $bytes / pow(1024, $factor), $units[$factor]);
    }
    
    /**
     * @title 清除所有系统缓存
     * @desc 一键清除所有系统级缓存
     */
    public static function clearAllCache()
    {
        $results = [];
        
        // 清除插件缓存
        $results['plugin'] = self::clearPluginCache();
        
        // 清除配置缓存
        $results['config'] = self::clearConfigCache();
        
        // 清除语言缓存
        $results['lang'] = self::clearLangCache();
        
        // 清除路由缓存
        $results['route'] = self::clearRouteCache();
        
        // 统计总体结果
        $totalErrors = 0;
        $messages = [];
        
        foreach ($results as $type => $result) {
            if (isset($result['data']['errors'])) {
                $totalErrors += count($result['data']['errors']);
            }
            $messages[] = $result['msg'];
        }
        
        if ($totalErrors > 0) {
            return [
                'status' => 400, 
                'msg' => '部分缓存清除失败，请检查详细信息',
                'data' => $results
            ];
        } else {
            return [
                'status' => 200, 
                'msg' => '所有缓存已清除',
                'data' => $results
            ];
        }
    }
    
    /**
     * @title 获取缓存统计信息
     * @desc 显示各类缓存的状态
     */
    public static function getCacheStats()
    {
        $stats = [
            '插件' => idcsmart_cache(self::CACHE_PLUGIN_LIST) ? '已缓存' : '未缓存',
            '配置' => idcsmart_cache(self::CACHE_CONFIGURATION) ? '已缓存' : '未缓存',
            '路由' => self::getRouteCacheStats()['cache_files']>0 ? '已缓存' : '未缓存',
        ];
        
        $langs = ['zh-cn', 'en-us', 'zh-hk'];
        $stats['多语言'] = '未缓存';
        foreach ($langs as $lang) {
            $key = self::CACHE_LANG_PREFIX . $lang;
            if (idcsmart_cache($key)){
                $stats['多语言'] = '已缓存';
            }
        }
        
        return ['status' => 200, 'data' => $stats];
    }
}
