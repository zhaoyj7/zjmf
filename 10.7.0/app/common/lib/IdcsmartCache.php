<?php

namespace app\common\lib;

use app\common\logic\CacheLogic;
use think\facade\Db;

/**
 * @desc
 * @author wyh
 * @time 2023-02-13
 * @use app\common\lib\IdcsmartCache
 */
class IdcsmartCache
{
    /**
     * 缓存操作
     * @param string $key 缓存键名
     * @param mixed $value 缓存值，空字符串表示读取，null表示删除
     * @param int|null $timeout 过期时间（秒），null或负数表示永不过期
     * @return mixed
     */
    public static function cache($key, $value = '', $timeout = null)
    {
        // 判断是否安装redis扩展
        if (extension_loaded('redis') && defined('REDIS_HOST')) {
            $Redis = RedisPool::getRedis('redis');
            // 删除缓存
            if (is_null($value)) {
                return $Redis->del($key);
            }
            
            // 读取缓存
            if ($value === '') {
                $data = $Redis->get($key);
                // 兼容旧版本错误处理
                $exist = Db::name('configuration')->where('setting','redis_error_setting')->where('value','1')->find();
                if (empty($exist)){
                    Db::name('configuration')->insert([
                        'setting'=>'redis_error_setting',
                        'value'=>'1',
                        'description'=>'Redis错误处理',
                    ]);
                    //删除缓存，返回空
                    if (class_exists("app\\common\\logic\\CacheLogic")){
                        $Redis->del(CacheLogic::CACHE_PLUGIN_LIST);
                        $Redis->del(CacheLogic::CACHE_PLUGIN_HOOKS);
                        $Redis->del(CacheLogic::CACHE_CONFIGURATION);
                        $Redis->del(CacheLogic::CACHE_LANG_PREFIX);
                        $Redis->del(CacheLogic::CACHE_ROUTE_DIR);
                    }
                    return '';
                }
                if ($data === false) {
                    return null;
                }
                if ($data=='Array'){
                    return "";
                }
                // 尝试JSON解码，兼容旧数据
                $decoded = json_decode($data, true);
                return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $data;
            }
            
            // 写入缓存（序列化处理）
            $data = json_encode($value, JSON_UNESCAPED_UNICODE);
            if (empty($timeout) || $timeout < 0) {
                return $Redis->set($key, $data); // 永不过期
            }
            return $Redis->set($key, $data, (float)$timeout);
        }
        
        return cache($key, $value, $timeout);
    }
}
