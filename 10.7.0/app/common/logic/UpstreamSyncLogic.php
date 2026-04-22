<?php
namespace app\common\logic;

use app\common\model\ProductModel;
use app\common\model\ServerModel;
use app\common\model\UpstreamProductModel;

/**
 * @title 上游管理公共类
 * @desc 上游管理公共类
 * @time 2024-08-02
 * @use app\common\logic\UpstreamSyncLogic
 */
class UpstreamSyncLogic
{

    public function sync($param, $otherParams, ProductModel $productModel,UpstreamProductModel $upstreamProductModel)
    {
        // 1、更改代理模式
        $upstreamProductModel->save([
            //'upstream_version' => '',
            'mode' => 'sync',
        ]);

        // 2、服务器接口
        $ServerModel = new ServerModel();

        $server = $ServerModel->where('upstream_use',1)
            ->where('module',$upstreamProductModel['res_module'])
            ->find();

        if (empty($server)){
            $result = $ServerModel->createServer([
                'name' => $upstreamProductModel['res_module'].'同步代理接口(勿删)',
                'module' => $upstreamProductModel['res_module'],
                'url' => 'localhost',
                'status' => 1,
                'upstream_use' => 1,
            ]);
            $serverId = $result['data']['id']??0;
        }else{
            $serverId = $server['id'];
        }

        $productModel->save([
            'type' => 'server',
            'rel_id' => $serverId,
        ]);

        // 3、同步其他数据
        return (new ModuleLogic())->syncOtherParams($upstreamProductModel['product_id'], $param, $otherParams, $upstreamProductModel);
    }

}