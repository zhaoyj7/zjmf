<?php
namespace app\common\logic;

use app\common\model\ClientModel;
use think\facade\Cache;

/**
 * @title 安全验证逻辑类
 * @desc 通用安全验证，支持登录、重置密码、产品转移等场景
 * @use app\common\logic\SecurityVerifyLogic
 */
class SecurityVerifyLogic
{
    // 验证方式常量
    const METHOD_OPERATE_PASSWORD = 'operate_password';
    const METHOD_EMAIL_CODE = 'email_code';
    const METHOD_PHONE_CODE = 'phone_code';
    const METHOD_CERTIFICATION = 'certification';

    // 场景常量
    const SCENE_LOGIN = 'login';
    const SCENE_RESET_OPERATE_PASSWORD = 'reset_operate_password';
    const SCENE_CHANGE_PASSWORD = 'change_password';
    const SCENE_HOST_TRANSFER = 'host_transfer';

    /**
     * 时间 2024-11-25
     * @title 获取可用验证方式
     * @desc 根据后台配置和用户安全选项，返回实际可用的验证方式
     * @author wyh
     * @version v1
     * @param int $clientId 用户ID
     * @param string $scene 场景
     * @return array
     */
    public function getAvailableMethods($clientId, $scene = 'login')
    {
        // 获取后台配置的验证方式
        $configMethods = $this->getConfigMethods($scene);
        
        if (empty($configMethods)) {
            return [];
        }

        // 查询用户信息
        $client = ClientModel::find($clientId);
        if (empty($client)) {
            return [];
        }

        // 获取用户强制安全选项配置
        $enforceSafeMethods = $this->getEnforceSafeMethods($clientId);

        $availableMethods = [];

        foreach ($configMethods as $method) {
            $methodInfo = $this->checkMethodAvailable($client, $method, $enforceSafeMethods);
            if ($methodInfo) {
                $availableMethods[] = $methodInfo;
            }
        }

        return $availableMethods;
    }

    /**
     * 时间 2024-11-25
     * @title 验证操作密码
     * @desc 验证用户操作密码
     * @author wyh
     * @version v1
     * @param int $clientId 用户ID
     * @param string $password 操作密码
     * @return array
     */
    public function verifyOperatePassword($clientId, $password)
    {
        $client = ClientModel::find($clientId);
        if (empty($client)) {
            return ['status' => 400, 'msg' => lang('client_not_exist')];
        }

        if (empty($client->operate_password)) {
            return ['status' => 400, 'msg' => lang('operate_password_not_set')];
        }

        if (!idcsmart_password_compare($password, $client->operate_password)) {
            active_log(lang('log_client_security_verify_operate_password_error', [
                '{client}' => 'client#' . $client->id . '#' . $client->username . '#'
            ]), 'security_verify', $client->id);
            return ['status' => 400, 'msg' => lang('operate_password_error')];
        }

        active_log(lang('log_client_security_verify_operate_password_success', [
            '{client}' => 'client#' . $client->id . '#' . $client->username . '#'
        ]), 'security_verify', $client->id);

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2024-11-25
     * @title 验证验证码
     * @desc 验证邮箱或手机验证码
     * @author wyh
     * @version v1
     * @param int $clientId 用户ID
     * @param string $method 验证方式 email_code/phone_code
     * @param string $code 验证码
     * @param string $action 验证动作 update_password/update_operate_password/host_transfer
     * @return array
     */
    public function verifyCode($clientId, $method, $code, $action = 'update_password')
    {
        $client = ClientModel::find($clientId);
        if (empty($client)) {
            return ['status' => 400, 'msg' => lang('client_not_exist')];
        }

        // 构造缓存key（根据不同的动作使用不同的缓存key）
        if ($method === self::METHOD_EMAIL_CODE) {
            if (empty($client->email)) {
                return ['status' => 400, 'msg' => lang('user_not_bind_email')];
            }
            $cacheKey = 'verification_code_' . $action . '_' . $client->email;
        } else {
            if (empty($client->phone)) {
                return ['status' => 400, 'msg' => lang('user_not_bind_phone')];
            }
            $cacheKey = 'verification_code_' . $action . '_' . $client->phone_code . '_' . $client->phone;
        }

        // 获取缓存的验证码
        $cachedCode = Cache::get($cacheKey);
        
        if (empty($cachedCode)) {
            return ['status' => 400, 'msg' => lang('verification_code_expired')];
        }

        if ($cachedCode != $code) {
            return ['status' => 400, 'msg' => lang('verification_code_error')];
        }

        // 验证成功，删除缓存
        Cache::delete($cacheKey);

        $methodName = $method === self::METHOD_EMAIL_CODE ? '邮箱验证码' : '手机验证码';
        active_log(lang('log_client_security_verify_code_success', [
            '{client}' => 'client#' . $client->id . '#' . $client->username . '#',
            '{method}' => $methodName
        ]), 'security_verify', $client->id);

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2024-11-25
     * @title 创建实名认证
     * @desc 创建实名认证会话，返回二维码URL
     * @author wyh
     * @version v1
     * @param int $clientId 用户ID
     * @return array
     */
    public function createCertification($clientId)
    {
        $client = ClientModel::find($clientId);
        if (empty($client)) {
            return ['status' => 400, 'msg' => lang('client_not_exist')];
        }

        // 检查是否安装了实名认证插件
        if (!class_exists('\addon\idcsmart_certification\model\CertificationLogModel')) {
            return ['status' => 400, 'msg' => lang('certification_plugin_not_installed')];
        }

        // 查询用户历史实名记录
        $CertificationLogModel = new \addon\idcsmart_certification\model\CertificationLogModel();
        $lastLog = $CertificationLogModel
            ->where('client_id', $clientId)
            ->where('status', 1)
            ->order('id', 'desc')
            ->find();

        if (empty($lastLog)) {
            return ['status' => 400, 'msg' => lang('client_not_certified')];
        }

        // 调用阿里云实名认证接口
        if (in_array($lastLog['type'],[2,3])){
            $customFields = json_decode($lastLog['custom_fields_json'],true);
            $lastLog['card_name'] = $customFields['custom_fields1'] ?? '';
            $lastLog['card_number'] = $customFields['custom_fields2'] ?? '';
        }
        $postData = [
            'name' => $lastLog['card_name'],
            'card' => $lastLog['card_number'],
            'card_type' => $lastLog['card_type'],
            'special' => 1, // 特殊场景标识
        ];

        $certifiPlugin = configuration('exception_login_certification_plugin')?:'Idcsmartali';

        $result = plugin_reflection($certifiPlugin, $postData, 'certification', 'person');

        if (empty($result) || empty($result['certify_id'])) {
            return ['status' => 400, 'msg' => lang('certification_create_failed')];
        }

        // 将认证ID存入缓存
        $cacheKey = 'certification_exception_login_' . $clientId . '_' . $result['certify_id'];
        Cache::set($cacheKey, [
            'status' => 0,
            'create_time' => time()
        ], 600); // 10分钟有效期

        return [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'certify_id' => $result['certify_id'],
                'certify_url' => $result['url'] ?? '',
//                'card_name' => $this->maskName($lastLog['card_name']),
//                'card_number' => $this->maskIdCard($lastLog['card_number']),
            ]
        ];
    }

    /**
     * 时间 2024-11-25
     * @title 验证实名认证状态
     * @desc 查询并验证实名认证是否通过
     * @author wyh
     * @version v1
     * @param int $clientId 用户ID
     * @param string $certifyId 认证ID
     * @return array
     */
    public function verifyCertification($clientId, $certifyId)
    {
        $client = ClientModel::find($clientId);
        if (empty($client)) {
            return ['status' => 400, 'msg' => lang('client_not_exist')];
        }

        // 检查缓存
        $cacheKey = 'certification_exception_login_' . $clientId . '_' . $certifyId;
        $cacheData = Cache::get($cacheKey);

        if (empty($cacheData)) {
            return ['status' => 400, 'msg' => lang('certification_expired')];
        }

        // 如果已经验证通过
        if ($cacheData['status'] == 1) {
            return [
                'status' => 200,
                'msg' => lang('success_message'),
                'data' => ['verify_status' => 1]
            ];
        }

        // 调用阿里云接口查询状态
        try {

            $certifiPlugin = configuration('exception_login_certification_plugin')?:'Idcsmartali';

            $result = plugin_reflection($certifiPlugin, ['certify_id'=>$certifyId], 'certification', 'status');
            if (empty($result)){
                return ['status' => 400, 'msg' => lang('certification_plugin_not_installed')];
            }

//            if (!class_exists('\certification\idcsmartali\logic\IdcsmartaliLogic')) {
//                return ['status' => 400, 'msg' => lang('certification_plugin_not_installed')];
//            }
//            $IdcsmartaliLogic = new \certification\idcsmartali\logic\IdcsmartaliLogic();
//            $result = $IdcsmartaliLogic->getAliyunAuthStatusSpecial($certifyId);

            if ($result['code'] == 1) {
                // 认证通过，更新缓存
                Cache::set($cacheKey, [
                    'status' => 1,
                    'create_time' => $cacheData['create_time'],
                    'verify_time' => time()
                ], 600);

                active_log(lang('log_client_security_verify_certification_success', [
                    '{client}' => 'client#' . $client->id . '#' . $client->username . '#'
                ]), 'security_verify', $client->id);

                return [
                    'status' => 200,
                    'msg' => lang('success_message'),
                    'data' => ['verify_status' => 1]
                ];
            } else {
                return [
                    'status' => 200,
                    'msg' => lang('certification_pending'),
                    'data' => ['verify_status' => 0]
                ];
            }
        } catch (\Exception $e) {
            return ['status' => 400, 'msg' => lang('certification_query_failed') . ':' .$e->getMessage()];
        }
    }

    /**
     * 时间 2024-11-25
     * @title 检查实名认证是否已通过
     * @desc 仅检查状态，不调用远程接口
     * @author wyh
     * @version v1
     * @param int $clientId 用户ID
     * @param string $certifyId 认证ID
     * @return bool
     */
    public function isCertificationPassed($clientId, $certifyId)
    {
        $cacheKey = 'certification_exception_login_' . $clientId . '_' . $certifyId;
        $cacheData = Cache::get($cacheKey);

        return !empty($cacheData) && $cacheData['status'] == 1;
    }

    /**
     * 获取后台配置的验证方式
     * @param string $scene 场景
     * @return array
     */
    private function getConfigMethods($scene)
    {
        $configKey = $this->getConfigKey($scene);
        $config = configuration($configKey);

        if (empty($config)) {
            return [];
        }

        if (is_string($config)) {
            return array_filter(explode(',', $config));
        }

        return is_array($config) ? $config : [];
    }

    /**
     * 根据场景获取配置项Key
     * @param string $scene
     * @return string
     */
    private function getConfigKey($scene)
    {
        // 所有场景统一使用 home_login_ip_exception_verify 配置
        return 'home_login_ip_exception_verify';
    }

    /**
     * 获取用户强制安全选项
     * @param int $clientId
     * @return array
     */
    private function getEnforceSafeMethods($clientId)
    {
        $config = configuration('home_enforce_safe_method');
        
        if (empty($config)) {
            return [];
        }

        if (is_string($config)) {
            return array_filter(explode(',', $config));
        }

        return is_array($config) ? $config : [];
    }

    public static function clearSecurityVerifyToken($clientId){
        idcsmart_cache('security_verify_token_' . self::METHOD_OPERATE_PASSWORD . '_' . $clientId, null);
        idcsmart_cache('security_verify_token_' . self::METHOD_CERTIFICATION . '_' . $clientId, null);
        idcsmart_cache('security_verify_token_' . self::METHOD_EMAIL_CODE . '_' . $clientId, null);
        idcsmart_cache('security_verify_token_' . self::METHOD_PHONE_CODE . '_' . $clientId, null);
    }

    /**
     * 检查验证方式是否可用
     * @param object $client 用户对象
     * @param string $method 验证方式
     * @param array $enforceSafeMethods 强制安全选项
     * @return array|null
     */
    private function checkMethodAvailable($client, $method, $enforceSafeMethods)
    {
        // 验证token
        $token = md5(rand_str(12).time());
        idcsmart_cache('security_verify_token_' . $method . '_' . $client->id, $token, 3600);
        switch ($method) {
            case self::METHOD_OPERATE_PASSWORD:
                // 操作密码：检查用户是否设置了操作密码
                if (empty($client->operate_password)) {
                    return null;
                }
                // 检查强制安全选项
                if (!empty($enforceSafeMethods) && !in_array('operate_password', $enforceSafeMethods)) {
                    return null;
                }
                return [
                    'value' => self::METHOD_OPERATE_PASSWORD,
                    'label' => lang('security_verify_operate_password'),
                    'tip' => '',
                    'account' => '',
                    'phone_code' => '',
                    'security_verify_token' => $token,
                ];

            case self::METHOD_EMAIL_CODE:
                // 邮箱验证码：检查用户是否绑定邮箱
                if (empty($client->email)) {
                    return null;
                }
                // 检查强制安全选项
                if (!empty($enforceSafeMethods) && !in_array('email', $enforceSafeMethods)) {
                    return null;
                }
                return [
                    'value' => self::METHOD_EMAIL_CODE,
                    'label' => lang('security_verify_email_code'),
                    'tip' => lang('security_verify_send_to', ['account' => $this->maskEmail($client->email)]),
                    'account' => $client->email,
                    'phone_code' => '',
                    'security_verify_token' => $token,
                ];

            case self::METHOD_PHONE_CODE:
                // 手机验证码：检查用户是否绑定手机
                if (empty($client->phone)) {
                    return null;
                }
                // 检查强制安全选项
                if (!empty($enforceSafeMethods) && !in_array('phone', $enforceSafeMethods)) {
                    return null;
                }
                return [
                    'value' => self::METHOD_PHONE_CODE,
                    'label' => lang('security_verify_phone_code'),
                    'tip' => lang('security_verify_send_to', ['account' => $this->maskPhone($client->phone)]),
                    'account' => $client->phone,
                    'phone_code' => $client->phone_code,
                    'security_verify_token' => $token,
                ];

            case self::METHOD_CERTIFICATION:
                // 实名认证：检查用户是否已实名
                if (!check_certification($client->id)) {
                    return null;
                }
                // 检查强制安全选项
                if (!empty($enforceSafeMethods) && !in_array('certification', $enforceSafeMethods)) {
                    return null;
                }
                $certificationMethod = configuration('exception_login_certification_plugin')??'Idcsmartali';
                if (in_array($certificationMethod, ['Idcsmartali', 'Ali'])){
                    $tip = lang('security_verify_certification_tip');
                }else if ($certificationMethod == 'Wechat'){
                    $tip = lang('security_verify_certification_tip_wechat');
                }else{
                    $tip = lang('security_verify_certification_tip_other');
                }
                return [
                    'value' => self::METHOD_CERTIFICATION,
                    'label' => lang('security_verify_certification'),
                    'tip' => $tip,
                    'account' => $client->phone?:$client->email,
                    'phone_code' => $client->phone_code,
                    'security_verify_token' => $token,
                ];

            default:
                return null;
        }
    }

    /**
     * 脱敏邮箱
     * @param string $email
     * @return string
     */
    private function maskEmail($email)
    {
        if (empty($email)) {
            return '';
        }

        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];

        if (strlen($name) <= 2) {
            $maskedName = $name[0] . '***';
        } else {
            $maskedName = substr($name, 0, 2) . '***' . substr($name, -1);
        }

        return $maskedName . '@' . $domain;
    }

    /**
     * 脱敏手机号
     * @param string $phone
     * @return string
     */
    private function maskPhone($phone)
    {
        if (empty($phone) || strlen($phone) < 7) {
            return $phone;
        }

        return substr($phone, 0, 3) . '****' . substr($phone, -4);
    }

    /**
     * 脱敏姓名
     * @param string $name
     * @return string
     */
    private function maskName($name)
    {
        if (empty($name)) {
            return '';
        }

        $len = mb_strlen($name, 'UTF-8');
        if ($len <= 1) {
            return $name;
        }

        return mb_substr($name, 0, 1, 'UTF-8') . str_repeat('*', $len - 1);
    }

    /**
     * 脱敏身份证
     * @param string $idCard
     * @return string
     */
    private function maskIdCard($idCard)
    {
        if (empty($idCard) || strlen($idCard) < 10) {
            return $idCard;
        }

        return substr($idCard, 0, 4) . '**********' . substr($idCard, -4);
    }
}
