<?php 
namespace app\common\logic;

use app\common\model\ProductModel;
use app\common\model\UpstreamProductModel;

use app\admin\model\PluginModel;

use addon\idcsmart_sub_account\model\IdcsmartSubAccountHostModel;
use addon\idcsmart_sub_account\model\IdcsmartSubAccountModel;

/**
 * @title 下游商品操作类
 * @use   app\common\logic\DownstreamProductLogic
 */
class DownstreamProductLogic
{
    // 预留 是否下游API访问
    protected $downstreamRequest = false;

	// 对应上游商品ID
	protected $upstreamProductId = 0;

    // 预留 请求上游时,下游用户ID
    protected $downStreamClientId = 0;

    // 当前商品信息
    protected $product = NULL;

    // 上游商品信息
    protected $upstreamProduct = NULL;

    // 供应商ID,为0时非代理商品
    protected $supplierId = 0;

    // 是否是下游
    protected $isDownstream = false;

    // 是否下游同步模式
    protected $isDownstreamSync = false;

    // 超时时间
    protected $timeout = 30;

    /**
     * @时间 2024-08-14
     * @title 初始化
     * @desc  初始化
     * @author hh
     * @version v1
     * @param   ProductModel|int product - 商品实例/商品ID
     */
    public function __construct($product){
        $this->downstreamRequest = (bool)request()->is_api;

        if($product instanceof ProductModel){
            $this->product = $product;
        }else if(is_numeric($product)){
            $this->product = ProductModel::find($product);
        }

        if(!empty($this->product)){
            // 是代理商品
            $upstreamProduct = UpstreamProductModel::where('product_id', $this->product['id'])->find();
            if(!empty($upstreamProduct)){
                $this->upstreamProduct = $upstreamProduct;
                $this->supplierId = $upstreamProduct->supplier_id;
                $this->upstreamProductId = $upstreamProduct->upstream_product_id;
                $this->isDownstream = true;
                $this->isDownstreamSync = $upstreamProduct['mode'] == 'sync';
            }
        }
    }

    public function __get($name)
    {
        return isset($this->$name) ? $this->$name : NULL;
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
        // TODO
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
     * @param   bool curlFile - 是否上传文件
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  array data - 其他数据
     */
    public function curl($path, $data = [], $request = 'POST', $curlFile = false)
    {
        // 非代理商品
        if(empty($this->supplierId)){
            return ['status'=>400, 'msg'=>lang('') ];
        }
        // 附加参数请求
        // $data['downstream_client_id'] = $this->downStreamClientId;
        return idcsmart_api_curl($this->supplierId, $path, $data, $this->timeout, $request, 'json', $curlFile);
    }


}