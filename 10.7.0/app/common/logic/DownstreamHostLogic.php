<?php 
namespace app\common\logic;

use app\common\model\HostModel;
use app\common\model\UpstreamProductModel;
use app\common\model\UpstreamHostModel;

use app\admin\model\PluginModel;

use addon\idcsmart_sub_account\model\IdcsmartSubAccountHostModel;
use addon\idcsmart_sub_account\model\IdcsmartSubAccountModel;

/**
 * @title 下游产品操作类
 * @use   app\common\logic\DownstreamHostLogic
 */
class DownstreamHostLogic
{
    // 是否下游API访问
    protected $downstreamRequest = false;

	// 对应上游产品ID
	protected $upstreamHostId = 0;

    // 请求上游时,下游用户ID
    protected $downStreamClientId = 0;

    // 当前产品信息
    protected $host = NULL;

    // 上游商品信息
    protected $upstreamProduct = NULL;

    // 供应商ID,为0时非代理商品
    protected $supplierId = 0;

    protected $timeout = 60;

    /**
     * @时间 2024-08-12
     * @title 初始化
     * @desc  初始化
     * @author hh
     * @version v1
     * @param   HostModel $host - 产品实例
     */
    public function __construct(HostModel $host){
        $this->downstreamRequest = (bool)request()->is_api;

        $this->host = $host;

        // 是代理商品
        $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
        if(!empty($upstreamProduct)){
            $this->upstreamProduct = $upstreamProduct;
            $this->supplierId = $upstreamProduct->supplier_id;

            $this->downStreamClientId = $this->getDownstreamClientId();
            $this->upstreamHostId = UpstreamHostModel::where('host_id', $host['id'])->value('upstream_host_id');
        }
    }

    /**
     * @时间 2024-08-12
     * @title 是否是下游
     * @desc  是否是下游
     * @author hh
     * @version v1
     * @return  boolean
     */
    public function isDownstream(){
        return !empty($this->supplierId);
    }

    /**
     * @时间 2024-12-11
     * @title 是否下游同步
     * @desc  是否下游同步
     * @author hh
     * @version v1
     * @return  boolean
     */
    public function isDownstreamSync(){
        return $this->isDownstream() && $this->upstreamProduct['mode'] == 'sync';
    }

    /**
     * @时间 2024-08-12
     * @title 设置超时时间
     * @desc  设置超时时间
     * @author hh
     * @version v1
     * @param  int timeout - 超时时间
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * 时间 2024-02-20
     * @title 获取下游用户ID
     * @desc  获取下游用户ID,如果是下游访问,转换为当前系统用户ID
     * @author hh
     * @version v1
     * @return int
     */
    protected function getDownstreamClientId()
    {
        $downStreamClientId = $this->host['client_id'];
        $enable = PluginModel::where('name', 'IdcsmartSubAccount')->where('module', 'addon')->where('status',1)->find();
        if(!empty($enable) && class_exists('addon\idcsmart_sub_account\model\IdcsmartSubAccountHostModel')){
            // 是否是子账户
            $IdcsmartSubAccountHostModel = IdcsmartSubAccountHostModel::where('host_id', $this->host['id'])->find();
            if(!empty($IdcsmartSubAccountHostModel)){
                $IdcsmartSubAccountModel = IdcsmartSubAccountModel::find($IdcsmartSubAccountHostModel['addon_idcsmart_sub_account_id']);
                if(!empty($IdcsmartSubAccountModel)){
                    $downStreamClientId = $IdcsmartSubAccountModel['client_id'];
                }
            }
        }
        return (int)$downStreamClientId;
    }

    /**
     * 时间 2023-02-16
     * @title 请求上游curl
     * @desc  请求上游curl
     * @author hh
     * @version v1
     * @param   string path - 请求地址路由 require
     * @param   array data - 请求参数
     * @param   string request POST 请求方式(POST,GET,DELETE,PUT)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  array data - 其他数据
     */
    public function curl($path, $data = [], $request = 'POST')
    {
        // 非代理商品
        if(empty($this->supplierId)){
            return ['status'=>400, 'msg'=>lang('') ];
        }
        // 附加参数请求
        $data['downstream_client_id'] = $this->downStreamClientId;
        return idcsmart_api_curl($this->supplierId, $path, $data, $this->timeout, $request);
    }

    /**
     * 时间 2025-03-12
     * @title 支付(余额支付失败后回退信用额支付)
     * @desc  先尝试余额支付,失败后自动尝试信用额支付
     * @author wyh
     * @version v1
     * @param   array payData - 支付参数(id,gateway) require
     * @return  array
     */
    public function payWithFallback($payData)
    {
        // 余额支付
        $result = $this->curl('/console/v1/pay', $payData, 'POST');
        // 余额支付失败，尝试信用额支付
        if ($result['status'] != 200) {
            // 先取消余额占用
            $this->curl('/console/v1/credit', [
                'id' => $payData['id'] ?? 0,
                'use' => 0,
            ], 'POST');
            // 再尝试信用额支付
            $creditLimitPayData = [
                'id' => $payData['id'] ?? 0,
            ];
            $creditLimitResult = $this->curl('/console/v1/credit_limit/pay', $creditLimitPayData, 'POST');
            if ($creditLimitResult['status'] == 200) {
                return $creditLimitResult;
            }
        }
        return $result;
    }
}