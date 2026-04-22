<?php
namespace addon\idcsmart_renew\model;

use app\common\logic\ModuleLogic;
use app\common\model\HostModel;
use app\common\model\OrderModel;
use app\common\model\ProductModel;
use app\common\model\UpgradeModel;
use think\db\Query;
use think\Model;

/*
 * @author wyh
 * @time 2022-06-02
 */
class IdcsmartRenewAutoModel extends Model
{
    protected $name = 'addon_idcsmart_renew_auto';

    // 设置字段信息
    protected $schema = [
        'host_id'   => 'int',
        'status'    => 'int',
    ];

    public $isAdmin = false;

    // 获取自动续费设置
    /**
     * 时间 2022-10-14
     * @title 获取自动续费设置
     * @desc 获取自动续费设置
     * @author theworld
     * @version v1
     * @param int id - 产品ID required
     * @param int status - 自动续费状态1开启,0关闭
     */
    public function getStatus($id)
    {
        $HostModel = new HostModel();
        $host = $HostModel->find($id);
        if (empty($host) || $host['is_delete']){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }
        $clientId = get_client_id();
        if ($this->isAdmin===false && $host->client_id != $clientId){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }
        $renewAuto = $this->where('host_id', $id)->find();
        $status = $renewAuto['status'] ?? 0;

        return ['status'=>200,'msg'=>lang_plugins('success_message'), 'data' => ['status' => $status]];
    }

    /**
     * 时间 2022-10-14
     * @title 自动续费设置
     * @desc 自动续费设置
     * @author theworld
     * @version v1
     * @param int id - 产品ID required
     * @param int status - 自动续费状态1开启,0关闭 required
     */
    public function updateStatus($param)
    {
        $HostModel = new HostModel();
        $host = $HostModel->find($param['id']);
        if (empty($host) || $host['is_delete']){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }
        // 一次性/按需,不支持
        if (in_array($host['billing_cycle'], ['onetime','on_demand'])){
            return ['status'=>400,'msg'=>lang_plugins('host_cannot_renew')];
        }
        $clientId = get_client_id();
        if ($this->isAdmin===false && $host->client_id != $clientId){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }

        $this->startTrans();
        try{
            $old = $this->where('host_id', $param['id'])->find();
            $this->where('host_id', $param['id'])->delete();
            $this->create([
                'host_id' => $param['id'],
                'status' => $param['status'],
            ]);

            $ProductModel = new ProductModel();
            $product = $ProductModel->find($host['product_id']);
            if ($this->isAdmin){
                if($param['status']==1){
                    if (!isset($old['status']) || $old['status']!=$param['status']){
                        active_log(lang_plugins('log_admin_open_auto_renew', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name . '#', '{host}'=>'host#'.$param['id'].'#'.$product['name'].'#']), 'host', $param['id']);
                    }
                }else{
                    if (!isset($old['status']) || $old['status']!=$param['status']){
                        active_log(lang_plugins('log_admin_close_auto_renew', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name . '#', '{host}'=>'host#'.$param['id'].'#'.$product['name'].'#']), 'host', $param['id']);
                    }
                }
            }else{
                if($param['status']==1){
                    active_log(lang_plugins('log_client_open_auto_renew', ['{client}'=>'user#'.get_client_id().'#'.request()->client_name . '#', '{host}'=>'host#'.$param['id'].'#'.$product['name'].'#']), 'host', $param['id']);
                }else{
                    active_log(lang_plugins('log_client_close_auto_renew', ['{client}'=>'user#'.get_client_id().'#'.request()->client_name . '#', '{host}'=>'host#'.$param['id'].'#'.$product['name'].'#']), 'host', $param['id']);
                }
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }
        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }   
}
