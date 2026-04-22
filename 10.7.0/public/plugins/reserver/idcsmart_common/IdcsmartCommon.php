<?php 
namespace reserver\idcsmart_common;

use app\common\model\UpstreamOrderModel;
use app\common\model\UpstreamProductModel;
use think\facade\Db;
use reserver\idcsmart_common\logic\RouteLogic;
use app\admin\model\PluginModel;
use app\common\model\UpstreamHostModel;
use app\common\model\HostModel;
use addon\idcsmart_renew\model\IdcsmartRenewAutoModel;

/**
 * 通用reserver
 */
class IdcsmartCommon
{
	/**
	 * 时间 2022-06-28
	 * @title 基础信息
	 * @author hh
	 * @version v1
	 */
	public function metaData()
	{
		return ['display_name'=>'通用代理', 'version'=>'4.0.0'];
	}

	/**
	 * 时间 2023-02-13
	 * @title 升降级后调用
	 * @author hh
	 * @version v1
	 */
	public function changePackage($params)
	{
		$hostId = $params['host']['id'];
		$custom = $params['custom'];
        $orderId = $params['order_id']??0;

		if($custom['type'] == 'reidcsmart_common_upgrade_config'){
			// 先在上游创建订单
			try{
				$RouteLogic = new RouteLogic();
				$RouteLogic->routeByHost($hostId);

				$result = $RouteLogic->curl( sprintf('console/v1/reidcsmart_common/host/%d/upgrade_config', $RouteLogic->upstream_host_id), ['configoption'=>$custom['configoption'] ?? [], 'cascade_configoption'=>$custom['cascade_configoption'] ?? []], 'POST');
				if($result['status'] == 200){
                    $UpstreamOrderModel = new UpstreamOrderModel();
                    $UpstreamOrderModel->where('order_id',$orderId)
                        ->where('host_id',$hostId)
                        ->update([
                            'profit' => Db::raw('amount-'.($result['data']['amount']??0)),
                        ]);

					$creditData = [
                        'id'    => $result['data']['id'],
                        'use'   => 1,
                    ];
                    # 使用余额
                    $result1 = $RouteLogic->curl('/console/v1/credit', $creditData, 'POST');
                    if ($result1['status'] == 200 || ($result1['status']==400 && !empty($result1['data']['no_credit']))){
                        // 使用平台币支付
                        if (!empty($result1['data']['customfields']['agent_use_coin'])){
                            $resultCoin = $RouteLogic->curl('/console/v1/coin/pay',[
                                'auto'=>1,
                                'coin_coupon_ids'=>[],
                                'order_id' => $result1['data']['id'],
                                'use' => 1,
                            ],'POST');
                            if ($resultCoin['status']==200){
                                $result['data']['amount'] = $resultCoin['data']['amount']??0;
                            }
                        }
                        
                        // 亏本交易拦截检查
                        $checkResult = checkLossTrade($orderId, $result['data']['amount']??0, 'upgrade', '', $hostId);
                        if (!$checkResult['pass']) {
                            return ['status'=>400, 'msg'=>$checkResult['msg']];
                        }
                        
                        $payData = [
                            'id' => $result['data']['id'],
                            'gateway' => 'credit'
                        ];
                        # 支付(余额支付失败后回退信用额支付)
                        $result = $RouteLogic->payWithFallback($payData);
                        return $result;
                    }
                    return $result;
				}else{
					// 记录失败日志
					return $result;
				}
			}catch(\Exception $e){
				return ['status'=>400, 'msg'=>$e->getMessage()];
			}
		}
		return ['status'=>200];
	}

    /**
     * 时间 2022-06-29
     * @title 前台产品内页输出
     * @author hh
     * @version v1
     */
    public function clientArea()
    {
        if (use_mobile()){ // 手机端
            $homeHostThemeMobile = configuration('home_host_theme_mobile');
            $homeHostThemeMobile = json_decode($homeHostThemeMobile ?? '{}', true) ?: [];
            $mobileTheme = $homeHostThemeMobile['idcsmart_common'] ?? 'default';

            $type = 'mobile';
            // 1、配置主题没有走默认的
            if (!file_exists(__DIR__."/template/clientarea/mobile/{$mobileTheme}/product_detail.html")){
                $mobileTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/{$type}/{$mobileTheme}/product_detail.html"
            ];
        }else{ // pc端
            $homeHostTheme = configuration('home_host_theme');
            $homeHostTheme = json_decode($homeHostTheme ?? '{}', true) ?: [];
            $clientareaTheme = $homeHostTheme['idcsmart_common'] ?? 'default';

            if (!file_exists(__DIR__."/template/clientarea/pc/{$clientareaTheme}/product_detail.html")){
                $clientareaTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/pc/{$clientareaTheme}/product_detail.html"
            ];
        }

        return $res;
    }

    /**
     * 时间 2022-10-13
     * @title 产品列表
     * @author hh
     * @version v1
     */
    public function hostList($param)
    {
        if (use_mobile()){ // 手机端
            $homeHostThemeMobile = configuration('home_host_theme_mobile');
            $homeHostThemeMobile = json_decode($homeHostThemeMobile ?? '{}', true) ?: [];
            $mobileTheme = $homeHostThemeMobile['idcsmart_common'] ?? 'default';

            $type = 'mobile';
            // 1、配置主题没有走默认的
            if (!file_exists(__DIR__."/template/clientarea/mobile/{$mobileTheme}/product_list.html")){
                $mobileTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/{$type}/{$mobileTheme}/product_list.html"
            ];
        }else{ // pc端
            $homeHostTheme = configuration('home_host_theme');
            $homeHostTheme = json_decode($homeHostTheme ?? '{}', true) ?: [];
            $clientareaTheme = $homeHostTheme['idcsmart_common'] ?? 'default';
            if (!file_exists(__DIR__."/template/clientarea/pc/{$clientareaTheme}/product_list.html")){
                $clientareaTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/pc/{$clientareaTheme}/product_list.html"
            ];
        }

        return $res;
    }

    /**
     * 时间 2022-10-13
     * @title 前台商品购买页面输出
     * @author hh
     * @version v1
     */
    public function clientProductConfigOption($param)
    {
        if (use_mobile()){ // 手机端
            $mobileTheme = configuration('cart_theme_mobile');
            $type = 'mobile';
            // 1、配置主题没有走默认的
            if (!file_exists(__DIR__."/template/cart/mobile/{$mobileTheme}/goods.html")){
                $mobileTheme = "default";
            }
            $res = [
                'template' => "template/cart/{$type}/{$mobileTheme}/goods.html"
            ];
        }else{ // pc端
            $cartTheme = configuration('cart_theme');
            if (!file_exists(__DIR__."/template/cart/pc/{$cartTheme}/goods.html")){
                $cartTheme = "default";
            }
            $res = [
                'template' => "template/cart/pc/{$cartTheme}/goods.html"
            ];
        }

        return $res;
    }


}


