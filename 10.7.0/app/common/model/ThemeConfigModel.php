<?php
namespace app\common\model;

use think\Model;

/**
 * @title 主题配置模型
 * @desc 主题配置模型
 * @use app\common\model\ThemeConfigModel
 */
class ThemeConfigModel extends Model
{
    protected $name = 'theme_config';
    protected $pk = 'id';

    /**
     * 获取指定主题配置
     * @param string $theme 主题名称
     * @return array
     */
    public function getThemeConfig($theme)
    {
        $config = $this->where('theme', $theme)->find();
        
        if (!$config) {
            // 如果配置不存在，创建默认配置
            $defaultConfig = [
                'theme' => $theme,
                'display_one' => 'ticket',
                'display' => 'announcement',
                'create_time' => time(),
                'display_time' => 1,
            ];
            $this->save($defaultConfig);
            return $defaultConfig;
        }
        
        return $config->toArray();
    }

    /**
     * 更新主题配置
     * @param string $theme 主题名称
     * @param array $param 配置参数
     * @return array
     */
    public function updateThemeConfig($theme, $param)
    {
        try {
            $config = $this->where('theme', $theme)->find();
            
            if ($config) {
                // 更新现有配置
                if (isset($param['display_one'])) {
                    $config->display_one = $param['display_one'];
                }
                if (isset($param['display'])) {
                    $config->display = $param['display'];
                }
                if (isset($param['display_time'])) {
                    $config->display_time = $param['display_time'];
                }
                $config->save();
            } else {
                // 创建新配置
                $this->save([
                    'theme' => $theme,
                    'display_one' => $param['display_one'] ?? 'ticket',
                    'display' => $param['display'] ?? 'announcement',
                    'create_time' => time(),
                    'display_time' => $param['display_time'] ?? 1,
                ]);
            }

            return [
                'status' => 200,
                'msg' => lang('theme_config_update_success')
            ];
        } catch (\Exception $e) {
            return [
                'status' => 400,
                'msg' => lang('update_fail')
            ];
        }
    }

    /**
     * 获取所有主题的配置
     * @return array
     */
    public function getAllThemeConfigs()
    {
        return $this->select()->toArray();
    }
}
