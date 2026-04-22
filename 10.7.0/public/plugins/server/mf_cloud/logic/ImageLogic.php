<?php 
namespace server\mf_cloud\logic;

use think\facade\Db;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use server\mf_cloud\model\ImageModel;
use server\mf_cloud\model\ImageGroupModel;
use server\mf_cloud\model\ResourcePackageModel;
use server\mf_cloud\model\ConfigModel;
use server\mf_cloud\idcsmart_cloud\IdcsmartCloud;
use think\facade\Cache;
use app\common\logic\DownstreamProductLogic;
use app\common\model\SupplierModel;

class ImageLogic
{
	/**
	 * 时间 2022-06-29
	 * @title 拉取镜像
	 * @desc 拉取镜像
	 * @author hh
	 * @version v1
	 * @param   int productId - 商品ID require
	 * @return  int status - 状态(200=成功,400=失败)
	 * @return  string msg - 信息
	 */
	public static function getProductImage($productId)
	{
		$result = ['status'=>200, 'msg'=>lang_plugins('success_message')];

		$cacheKey = 'SYNC_MF_CLOUD_IMAGE_'.$productId;
		if(Cache::has($cacheKey)){
			return $result;
		}
		Cache::set($cacheKey, 1, 180);

		$ProductModel = ProductModel::find($productId);
		if(empty($ProductModel)){
			Cache::delete($cacheKey);
			return ['status'=>400, 'msg'=>lang_plugins('product_id_error')];
		}
		if($ProductModel->getModule() != 'mf_cloud'){
			Cache::delete($cacheKey);
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
		if($ProductModel['type'] == 'server_group'){
			Cache::delete($cacheKey);
			return ['status'=>400, 'msg'=>lang_plugins('only_link_server_can_sync_image')];
		}

		$DownstreamProductLogic = new DownstreamProductLogic($ProductModel);
		if($DownstreamProductLogic->isDownstreamSync){
			$path = sprintf('/api/v1/product/%d', $DownstreamProductLogic->upstreamProductId);
			$res = $DownstreamProductLogic->curl($path, ['mode'=>'sync'], 'GET');
			if($res['status'] != 200){
				Cache::delete($cacheKey);
				return $res;
			}
			$otherParams = $res['data']['other_params'];
			$supplier = SupplierModel::find($DownstreamProductLogic->supplierId);

			$rate = $supplier['rate']??1;
	        $isSyncPrice = false; // 是否同步价格

	        if ($DownstreamProductLogic->upstreamProduct['profit_type']==1){ // 自定义金额

	        }else{ // 百分比
	            $rate = bcdiv($rate*$DownstreamProductLogic->upstreamProduct['profit_percent'], 100, 2);
	            $isSyncPrice = true;
	        }
	        $time = time();

			// 镜像分组
            $imageGroup = ImageGroupModel::where('product_id', $productId)->select()->toArray();
            $oldImageGroupId = array_column($imageGroup, 'id');
            $imageGroupIdArr = array_column($imageGroup, 'id', 'upstream_id');

            foreach($otherParams['image_group'] as $v){
                if(isset($imageGroupIdArr[ $v['id'] ])){
                    // 修改
                    $update = [
                        'name'          => $v['name'],
                        'icon'          => $v['icon'],
                        'order'         => $v['order'],
                    ];
                    ImageGroupModel::where('id', $imageGroupIdArr[ $v['id'] ])->update($update);
                }else{
                    $upstreamId = $v['id'];
                    unset($v['id']);

                    $v['product_id'] = $productId;
                    $v['create_time'] = $time;
                    $v['upstream_id'] = $upstreamId;

                    $imageGroup = ImageGroupModel::create($v);
                    $imageGroupIdArr[ $upstreamId ] = $imageGroup->id;
                }
            }
            $allImageGroupId = array_values($imageGroupIdArr);
            $deleteImageGroupId = array_diff($oldImageGroupId, $imageGroupIdArr);
            if(!empty($deleteImageGroupId)){
                ImageGroupModel::whereIn('id', $deleteImageGroupId)->delete();
            }
            
            // 镜像
            $image = ImageModel::where('product_id', $productId)->select()->toArray();
            $oldImageId = array_column($image, 'id');
            $imageIdArr = array_column($image, 'id', 'upstream_id');
            
            foreach($otherParams['image'] as $v){
                if(isset($imageIdArr[ $v['id'] ])){
                    // 修改
                    $update = [
                        'image_group_id'    => $imageGroupIdArr[ $v['image_group_id'] ] ?? 0,
                        'name'              => $v['name'],
                        'charge'            => $v['charge'],
                        'rel_image_id'      => 0,
                        'order'             => $v['order'],
                        'is_market'         => $v['is_market'] ?? 0,
                    ];
                    if($isSyncPrice){
                        if($update['charge'] == 1){
                            $update['price'] = bcmul($v['price'],$rate,2);
                        }else{
                            $update['price'] = 0;
                        }
                    }
                    ImageModel::where('id', $imageIdArr[ $v['id'] ])->update($update);
                }else{
                    $upstreamId = $v['id'];
                    unset($v['id']);

                    $v['product_id'] = $productId;
                    $v['upstream_id'] = $upstreamId;
                    $v['image_group_id'] = $imageGroupIdArr[ $v['image_group_id'] ] ?? 0;
                    // 收费时
                    if($v['charge'] == 1){
                        $v['price'] = bcmul($v['price'],$rate,2);
                    }else{
                        $v['price'] = 0;
                    }
                    // 隐藏ID
                    $v['rel_image_id'] = 0;
                    // 镜像市场
                    $v['is_market'] = $v['is_market'] ?? 0;

                    $image = ImageModel::create($v);
                    $imageIdArr[ $upstreamId ] = $image->id;
                }
            }
            $allImageId = array_values($imageIdArr);
            $deleteImageId = array_diff($oldImageId, $allImageId);
            if(!empty($deleteImageId)){
                ImageModel::whereIn('id', $deleteImageId)->delete();
            }
            Cache::delete($cacheKey);
            return $result;
		}

		$ServerModel = ServerModel::find($ProductModel['rel_id']);
		$ServerModel['password'] = aes_password_decode($ServerModel['password']);
		$IdcsmartCloud = new IdcsmartCloud($ServerModel);

		$hash = ToolLogic::formatParam($ServerModel['hash']);
        $isAgent = isset($hash['account_type']) && $hash['account_type'] == 'agent';
        $IdcsmartCloud->setIsAgent($isAgent);

        if($isAgent){
        	// 代理商的镜像
        	$userInfo = $IdcsmartCloud->userInfo();
        	if($userInfo['status'] != 200){
        		Cache::delete($cacheKey);
				return ['status'=>400, 'msg'=>lang_plugins('sync_image_failed')];
			}
			// 获取设置的资源包
			$rid = ResourcePackageModel::where('product_id', $productId)->column('rid');
			if(empty($rid)){
				Cache::delete($cacheKey);
				return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_please_set_resource_package_first')];
			}
			if(!isset($userInfo['data']['resource_package'])){
				Cache::delete($cacheKey);
				return ['status'=>400, 'msg'=>lang_plugins('sync_image_failed')];
			}
			$remoteImage = [];
			foreach($userInfo['data']['resource_package'] as $v){
				if(in_array($v['id'], $rid)){
					$remoteImage = array_merge($remoteImage, $v['image']);
				}
			}
			if(!isset($remoteImage[0]['name'])){
				Cache::delete($cacheKey);
				return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_vendors_cannot_sync_image')];
			}

			$ConfigModel = new ConfigModel();
        	$config = $ConfigModel->indexConfig(['product_id'=>$productId]);
	        if($config['data']['manual_manage']==1){
	        	$ImageModel = new ImageModel();
	            $ImageGroupModel = new ImageGroupModel();
	            $ImageModel->where('product_id', $productId)->delete();
	            $ImageGroupModel->where('product_id', $productId)->delete();
	            $ConfigModel->where('product_id', $productId)->update(['manual_manage' => 0]);
	        }

			$imageGroup = ImageGroupModel::field('id,name')->where('product_id', $productId)->select()->toArray();
			$imageGroup = array_column($imageGroup, 'id', 'name') ?? [];

			$ImageModel = new ImageModel();
			$image = $ImageModel->field('id,rel_image_id')->where('product_id', $productId)->select()->toArray();
			$image = array_column($image, 'id', 'rel_image_id');

			$data = [];
			$imageIds = [];
			foreach($remoteImage as $v){
				if(!isset($imageGroup[ $v['image_group_name'] ])){
					$ImageGroupModel = ImageGroupModel::create(['product_id'=>$productId, 'name'=>$v['image_group_name'], 'icon'=>$v['image_group_name'] ]);
					$imageGroup[ $v['image_group_name'] ] = $ImageGroupModel->id;
				}
				if(!isset($image[$v['image_id']])){
					$one = [
						'image_group_id'	=> $imageGroup[ $v['image_group_name'] ],
						'name'				=> $v['name'],
						'enable'			=> 1,
						'charge'			=> 0,
						'price'				=> 0.00,
						'product_id'		=> $productId,
						'rel_image_id'		=> $v['image_id'],
						'is_market'			=> $v['is_market'] ?? 0,
					];

					$data[] = $one;
				}else{
					// 更新已存在的镜像的is_market字段
					$ImageModel->where('id', $image[$v['image_id']])->update(['is_market' => $v['is_market'] ?? 0]);
				}
				$imageIds[] = $v['image_id'];
			}
			if(!empty($data)){
				$ImageModel->insertAll($data);
			}
			// 不存在的远程镜像ID直接禁用
			if(!empty($imageIds)){
				$ImageModel->where('product_id', $productId)->where('rel_image_id', 'NOT IN', $imageIds)->update(['enable'=>0]);
			}else{
				$ImageModel->where('product_id', $productId)->update(['enable'=>0]);
			}

			Cache::delete($cacheKey);
			return $result;
        }

		// 先获取镜像分组
		$remoteImageGroup = $IdcsmartCloud->getImageGroup(['per_page'=>50]);
		if($remoteImageGroup['status'] != 200){
			return ['status'=>400, 'msg'=>lang_plugins('sync_image_failed')];
		}

		$ConfigModel = new ConfigModel();
    	$config = $ConfigModel->indexConfig(['product_id'=>$productId]);
        if($config['data']['manual_manage']==1){
        	$ImageModel = new ImageModel();
            $ImageGroupModel = new ImageGroupModel();
            $ImageModel->where('product_id', $productId)->delete();
            $ImageGroupModel->where('product_id', $productId)->delete();
            $ConfigModel->where('product_id', $productId)->update(['manual_manage' => 0]);
        }
	        
		$imageLink = [];
		foreach($remoteImageGroup['data']['data'] as $v){
			$imageLink[$v['id']] = $v['name'];
		}

		// 添加组
		$imageGroup = ImageGroupModel::field('id,name')->where('product_id', $productId)->select()->toArray();
		$imageGroup = array_column($imageGroup, 'id', 'name') ?? [];
		foreach($imageLink as $v){
			if(empty($imageGroup[$v])){
				$ImageGroupModel = ImageGroupModel::create(['product_id'=>$productId, 'name'=>$v, 'icon'=>$v]);
				$imageGroup[ $v ] = $ImageGroupModel->id;
			}
		}

		$res = $IdcsmartCloud->getImageList(['per_page'=>9999, 'status'=>1]);

		if($res['status'] == 200){
			// 获取当前产品已填加的镜像
			$ImageModel = new ImageModel();
			$image = $ImageModel->field('id,rel_image_id')->where('product_id', $productId)->select()->toArray();
			$image = array_column($image, 'id', 'rel_image_id');

			$data = [];
			$imageIds = [];
			foreach($res['data']['data'] as $v){
				$status = array_column($v['info'], 'status');
				if(!in_array(1, $status) && !in_array(2, $status)){
					continue;
				}
				if(!isset($imageGroup[ $imageLink[$v['image_group_id']] ])){
					$ImageGroupModel = ImageGroupModel::create(['product_id'=>$productId, 'name'=>$imageLink[$v['image_group_id']] ]);
					$imageGroup[ $imageLink[$v['image_group_id']] ] = $ImageGroupModel->id;
				}
				if(!isset($image[$v['id']])){
					$one = [
						'image_group_id'=>$imageGroup[ $imageLink[$v['image_group_id']] ],
						'name'=>$v['name'],
						'enable'=>1,
						'charge'=>0,
						'price'=>0.00,
						'product_id'=>$productId,
						'rel_image_id'=>$v['id'],
						'is_market'=>$v['is_market'] ?? 0,
					];

					$data[] = $one;
				}else{
					// 更新已存在的镜像的is_market字段
					$ImageModel->where('id', $image[$v['id']])->update(['is_market' => $v['is_market'] ?? 0]);
				}
				$imageIds[] = $v['id'];
			}
			if(!empty($data)){
				$ImageModel->insertAll($data);
			}
			// 不存在的远程镜像ID直接禁用
			if(!empty($imageIds)){
				$ImageModel->where('product_id', $productId)->where('rel_image_id', 'NOT IN', $imageIds)->update(['enable'=>0]);
			}else{
				$ImageModel->where('product_id', $productId)->update(['enable'=>0]);
			}
		}
		Cache::delete($cacheKey);
		return $result;
	}

}