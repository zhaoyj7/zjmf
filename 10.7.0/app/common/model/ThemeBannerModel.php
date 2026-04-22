<?php
namespace app\common\model;

use think\Model;

/**
 * @title 主题轮播图模型
 * @desc 主题轮播图模型
 * @use app\common\model\ThemeBannerModel
 */
class ThemeBannerModel extends Model
{
    protected $name = 'theme_banner';
    protected $pk = 'id';

    /**
     * 获取轮播图列表
     * @param array $param 查询参数
     * @return array
     */
    public function themeBannerList($param = [])
    {
        $where = [];
        
        // 主题筛选
        if (!empty($param['theme'])) {
            $where[] = ['theme', '=', $param['theme']];
        }

        $list = $this->where($where)
            ->order('order', 'asc')
            ->order('id', 'desc')
            ->select()
            ->toArray();

        return [
            'list' => $list
        ];
    }

    /**
     * 创建轮播图
     * @param array $param 轮播图数据
     * @return array
     */
    public function createThemeBanner($param)
    {
        try {
            // 获取当前主题的最大排序值
            $maxOrder = $this->where('theme', $param['theme'])->max('order');
            
            $data = [
                'theme' => $param['theme'],
                'img' => $param['img'],
                'url' => $param['url'],
                'start_time' => $param['start_time'],
                'end_time' => $param['end_time']+86400-1,
                'show' => $param['show'],
                'notes' => $param['notes'] ?? '',
                'order' => ($maxOrder ?? 0) + 1,
                'create_time' => time(),
                'update_time' => time()
            ];

            $this->save($data);

            return [
                'status' => 200,
                'msg' => lang('theme_banner_create_success')
            ];
        } catch (\Exception $e) {
            return [
                'status' => 400,
                'msg' => lang('create_fail')
            ];
        }
    }

    /**
     * 更新轮播图
     * @param array $param 轮播图数据
     * @return array
     */
    public function updateThemeBanner($param)
    {
        try {
            $banner = $this->find($param['id']);
            if (!$banner) {
                return [
                    'status' => 400,
                    'msg' => lang('id_error')
                ];
            }

            $banner->theme = $param['theme'];
            $banner->img = $param['img'];
            $banner->url = $param['url'];
            $banner->start_time = $param['start_time'];
            $banner->end_time = $param['end_time']+86400-1;
            $banner->show = $param['show'];
            $banner->notes = $param['notes'] ?? '';
            $banner->update_time = time();
            $banner->save();

            return [
                'status' => 200,
                'msg' => lang('theme_banner_update_success')
            ];
        } catch (\Exception $e) {
            return [
                'status' => 400,
                'msg' => lang('update_fail')
            ];
        }
    }

    /**
     * 删除轮播图
     * @param int $id 轮播图ID
     * @return array
     */
    public function deleteThemeBanner($id)
    {
        try {
            $banner = $this->find($id);
            if (!$banner) {
                return [
                    'status' => 400,
                    'msg' => lang('id_error')
                ];
            }

            $banner->delete();

            return [
                'status' => 200,
                'msg' => lang('theme_banner_delete_success')
            ];
        } catch (\Exception $e) {
            return [
                'status' => 400,
                'msg' => lang('delete_fail')
            ];
        }
    }

    /**
     * 切换轮播图显示状态
     * @param array $param 参数
     * @return array
     */
    public function showThemeBanner($param)
    {
        try {
            $banner = $this->find($param['id']);
            if (!$banner) {
                return [
                    'status' => 400,
                    'msg' => lang('id_error')
                ];
            }

            $banner->show = $param['show'];
            $banner->update_time = time();
            $banner->save();

            return [
                'status' => 200,
                'msg' => lang('update_success')
            ];
        } catch (\Exception $e) {
            return [
                'status' => 400,
                'msg' => lang('update_fail')
            ];
        }
    }

    /**
     * 轮播图排序
     * @param array $param 排序数据
     * @return array
     */
    public function orderThemeBanner($param)
    {
        try {
            if (!isset($param['id']) || !is_array($param['id'])) {
                return [
                    'status' => 400,
                    'msg' => lang('param_error')
                ];
            }

            foreach ($param['id'] as $order => $id) {
                $this->where('id', $id)->update([
                    'order' => $order + 1,
                    'update_time' => time()
                ]);
            }

            return [
                'status' => 200,
                'msg' => lang('theme_banner_order_success')
            ];
        } catch (\Exception $e) {
            return [
                'status' => 400,
                'msg' => lang('update_fail')
            ];
        }
    }

    /**
     * 获取轮播图详情
     * @param int $id 轮播图ID
     * @return array|null
     */
    public function getThemeBannerById($id)
    {
        $banner = $this->find($id);
        return $banner ? $banner->toArray() : null;
    }

    /**
     * 获取指定主题的有效轮播图
     * @param string $theme 主题名称
     * @return array
     */
    public function getActiveThemeBanners($theme)
    {
        $currentTime = time();
        
        return $this->where('theme', $theme)
            ->where('show', 1)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->order('order', 'asc')
            ->select()
            ->toArray();
    }
}
