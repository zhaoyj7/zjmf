<?php
namespace addon\promo_code\controller\clientarea;

use addon\promo_code\model\PromoCodeModel;
use app\event\controller\PluginBaseController;
use addon\promo_code\validate\PromoCodeValidate;

/**
 * @title 优惠码插件(基础版)
 * @desc 优惠码插件(基础版)
 * @use addon\promo_code\controller\clientarea\IndexController
 */
class IndexController extends PluginBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new PromoCodeValidate();
    }

    /**
     * 时间 2022-10-20
     * @title 应用优惠码
     * @desc 应用优惠码 新购续费升降级等 可使用此接口对优惠码进行验证
     * @author theworld
     * @version v1
     * @url /console/v1/promo_code/apply
     * @method POST
     * @param string scene - desc:优惠码应用场景 new新购 renew续费 upgrade升降级 change_billing_cycle按需转包年包月 validate:required
     * @param string promo_code - desc:优惠码 新购时必传 validate:optional
     * @param int host_id - desc:产品ID validate:optional
     * @param int product_id - desc:商品ID validate:required
     * @param int qty - desc:数量 新购时必传 validate:optional
     * @param int amount - desc:单价 validate:required
     * @param int billing_cycle_time - desc:周期时间 validate:required
     * @return float discount - desc:折扣金额
     * @return int id - desc:优惠码ID
     * @return int loop - desc:循环折扣 0否 1是
     * @return int renew - desc:续费优惠 0否 1是
     * @return int exclude_with_client_level - desc:不与用户等级同享 0否 1是
     */
    public function apply()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('apply')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $PromoCodeModel = new PromoCodeModel();

        $result = $PromoCodeModel->apply($param);

        return json($result);
    }

    /**
     * 时间 2025-09-26
     * @title 批量应用优惠码
     * @desc 批量应用优惠码 续费使用
     * @author wyh
     * @version v1
     * @url /console/v1/promo_code/apply_batch
     * @method POST
     * @param array promo_codes - desc:[{"host_id":1,"promo_code":"aAJDF1KA212","product_id":1,"amount":10,"billing_cycle_time":1000},...]，优惠码数组 元素包含host_id产品ID promo_code优惠码 product_id商品ID amount单价 billing_cycle_time周期时间 validate:required
     * @return float discount - desc:折扣金额
     */
    public function applyBatch()
    {
        $param = $this->request->param();

        $PromoCodeModel = new PromoCodeModel();

        $result = $PromoCodeModel->applyBatch($param);

        return json($result);
    }

    /**
     * 时间 2022-10-20
     * @title 产品内页获取优惠码信息
     * @desc 产品内页获取优惠码信息
     * @author theworld
     * @version v1
     * @url /console/v1/promo_code/host/:id/promo_code
     * @method GET
     * @param int id - desc:产品ID validate:required
     * @return array promo_code - desc:优惠码列表
     */
    public function hostPromoCode()
    {
        $param = $this->request->param();

        $PromoCodeModel = new PromoCodeModel();

        $result = $PromoCodeModel->hostPromoCode($param);

        return json($result);
    }

}