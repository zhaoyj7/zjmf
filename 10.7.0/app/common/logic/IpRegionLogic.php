<?php
namespace app\common\logic;

/**
 * @title IP地理位置逻辑类
 * @desc IP地理位置解析，用于判断IP所属省份
 * @use app\common\logic\IpRegionLogic
 */
class IpRegionLogic
{
    /**
     * 时间 2024-11-25
     * @title 获取IP地理位置信息
     * @desc 获取IP的国家、省份、城市等信息
     * @author wyh
     * @version v1
     * @param string $ip IP地址
     * @return array ['country', 'region', 'province', 'city', 'isp', 'full']
     */
    public function getIpRegion($ip)
    {
        // 特殊IP处理
        if (empty($ip) || $ip === '127.0.0.1' || $ip === 'localhost' || $this->isPrivateIp($ip)) {
            return [
                'country' => '本地',
                'region' => '',
                'province' => '本地',
                'city' => '',
                'isp' => '',
                'full' => '本地'
            ];
        }

        try {
            // 优先使用 ip2region 库
            $region = $this->getRegionByIp2region($ip);
            if ($region) {
                return $this->parseRegionString($region);
            }

            // 备选方案：使用在线API
            $region = $this->getRegionByApi($ip);
            if ($region) {
                return $region;
            }
        } catch (\Exception $e) {
            // 记录错误日志
        }

        return [
            'country' => '未知',
            'region' => '',
            'province' => '未知',
            'city' => '',
            'isp' => '',
            'full' => '未知'
        ];
    }

    /**
     * 时间 2024-11-25
     * @title 判断两个IP是否在同一省份
     * @desc 比较两个IP的省份是否相同
     * @author wyh
     * @version v1
     * @param string $ip1 第一个IP
     * @param string $ip2 第二个IP
     * @return bool
     */
    public function isSameProvince($ip1, $ip2)
    {
        // 如果任意一个IP为空，视为同省份（不触发验证）
        if (empty($ip1) || empty($ip2)) {
            return true;
        }

        $region1 = $this->getIpRegion($ip1);
        $region2 = $this->getIpRegion($ip2);

        // 如果任意一个是本地或未知，视为同省份
        if (in_array($region1['province'], ['本地', '未知']) || in_array($region2['province'], ['本地', '未知'])) {
            return true;
        }

        return $region1['province'] === $region2['province'];
    }

    /**
     * 时间 2024-11-25
     * @title 从完整地理信息提取省份
     * @desc 解析格式：中国|华南|广东省|深圳市|电信
     * @author wyh
     * @version v1
     * @param string $regionFull 完整地理信息
     * @return string 省份名称
     */
    public function extractProvince($regionFull)
    {
        if (empty($regionFull)) {
            return '未知';
        }

        $parts = explode('|', $regionFull);
        return $parts[2] ?? '未知';
    }

    /**
     * 使用ip2region库查询
     * @param string $ip
     * @return string|null
     */
    private function getRegionByIp2region($ip)
    {
        $dbPath = root_path() . 'extend/ip2region/ip2region.xdb';
        
        if (!file_exists($dbPath)) {
            return null;
        }

        try {
            // 使用 ip2region 库
            if (class_exists('\Ip2Region')) {
                $searcher = \Ip2Region::newWithFileOnly($dbPath);
                return $searcher->search($ip);
            }

            // 如果没有安装composer包，尝试使用纯PHP实现
            $ip2region = new \ip2region($dbPath);
            $result = $ip2region->btreeSearch($ip);
            return $result['region'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 使用在线API查询
     * @param string $ip
     * @return array|null
     */
    private function getRegionByApi($ip)
    {
        try {
            // 使用太平洋IP查询API（免费）
            $url = "http://whois.pconline.com.cn/ipJson.jsp?ip={$ip}&json=true";
            $response = @file_get_contents($url);
            
            if ($response) {
                $response = iconv('gbk', 'utf-8', $response);
                $data = json_decode($response, true);
                
                if ($data && isset($data['pro'])) {
                    return [
                        'country' => '中国',
                        'region' => $data['region'] ?? '',
                        'province' => $data['pro'] ?? '未知',
                        'city' => $data['city'] ?? '',
                        'isp' => '',
                        'full' => ($data['pro'] ?? '') . '|' . ($data['city'] ?? '')
                    ];
                }
            }
        } catch (\Exception $e) {
            // 忽略错误
        }

        return null;
    }

    /**
     * 解析ip2region返回的地理信息字符串
     * @param string $region 格式：中国|华南|广东省|深圳市|电信
     * @return array
     */
    private function parseRegionString($region)
    {
        $parts = explode('|', $region);

        return [
            'country' => $parts[0] ?? '未知',
            'region' => $parts[1] ?? '',
            'province' => $parts[2] ?? '未知',
            'city' => $parts[3] ?? '',
            'isp' => $parts[4] ?? '',
            'full' => $region
        ];
    }

    /**
     * 判断是否为内网IP
     * @param string $ip
     * @return bool
     */
    private function isPrivateIp($ip)
    {
        $privateRanges = [
            '10.',
            '172.16.', '172.17.', '172.18.', '172.19.',
            '172.20.', '172.21.', '172.22.', '172.23.',
            '172.24.', '172.25.', '172.26.', '172.27.',
            '172.28.', '172.29.', '172.30.', '172.31.',
            '192.168.',
        ];

        foreach ($privateRanges as $range) {
            if (strpos($ip, $range) === 0) {
                return true;
            }
        }

        return false;
    }
}
