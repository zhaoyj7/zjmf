<?php
namespace app\api\controller;

use app\common\logic\UpstreamLogic;
use app\common\model\ClientModel;
use app\common\model\HostModel;
use app\common\model\NoticeSettingModel;
use app\common\model\UpstreamHostModel;
use function Symfony\Component\String\u;
use app\common\model\HostIpModel;
use app\common\model\HostAdditionModel;

/*
 * API鉴权登录
 *
 * */
class AuthController
{
    /**
     * 时间 2023-02-16
     * @title API鉴权登录
     * @desc API鉴权登录
     * @author wyh
     * @version v1
     * @url /api/v1/auth
     * @method POST
     * @param string username - desc:用户名 用户注册时的邮箱或手机号 validate:required
     * @param string password - desc:密码 api信息的token validate:required
     * @return array data - desc:返回数据
     * @return string data.jwt - desc:jwt授权
     */
    public function auth()
    {
        $param = request()->param();

        $validate = new \think\Validate([
            'username' => 'require|length:4,20',
            'password' => 'require'
        ]);
        $validate->message([
            'username.require' => lang('user_cannot_empty'),
            'username.length' => lang('username_4_20_digits'),
            'password.require' => lang('password_cannot_empty'),
        ]);
        if (!$validate->check($param)) {
            return json(['status' => 400, 'msg' => lang('auth_failed')]);
        }

        $ClientModel = new ClientModel();

        $result = $ClientModel->apiAuth($param);

        return json($result);
    }

    /**
     * 时间 2023-02-16
     * @title API同步信息
     * @desc API同步信息：上游同步产品信息至本地
     * @author wyh
     * @version v1
     * @url /api/host/sync
     * @method POST
     */
    public function hostSync()
    {
        $param = request()->param();

        $id = $param['id']??0;

        if (empty($param['signature'])){
            return json(['status'=>400,'msg'=>lang('signature_error')]);
        }

        $HostModel = new HostModel();
        $host = $HostModel->alias('h')
            ->field('h.id,uh.upstream_info,h.status,h.name,h.client_id,h.due_time,h.suspend_type,h.suspend_reason,h.product_id')
            ->leftJoin('upstream_host uh','uh.host_id=h.id')
            ->where('h.id',$id)
            ->where('h.is_delete', 0)
            ->find();
        $stream_info = json_decode($host['upstream_info'], true);
        $token = $stream_info['token'];
        if(empty($token)){
            $result['status'] = 400;
            $result['msg'] = lang('host_cannot_use_this_interface');
            return json($result);
        }

        $param['password'] = html_entity_decode($param['password'], ENT_QUOTES);
        $param['token'] = $token;
        $UpstreamLogic = new UpstreamLogic();

        if(!$UpstreamLogic->validateSign($param, $param['signature'])){
            $result['status'] = 400;
            $result['msg'] = lang('signature_verification_failed');
            return json($result);
        }

        $hostStatus = $host['status'];

        $update = [
            'name'=>$param['domain'],
            'update_time' => time(),
        ];

        $hostSyncDueTimeOpen = configuration('host_sync_due_time_open');
        $hostSyncDueTimeApplyRange = configuration('host_sync_due_time_apply_range');
        if($hostSyncDueTimeApplyRange==1){
            $hostSyncDueTimeProductIds = array_filter(explode(',', configuration('host_sync_due_time_product_ids')));
            if(!in_array($host['product_id'], $hostSyncDueTimeProductIds)){
                $hostSyncDueTimeOpen = 0;
            }
        }

        if ($hostSyncDueTimeOpen==1 && !empty($param['nextduedate'])){
            $update['due_time'] = $param['nextduedate'];# wyh 20210817 上游推送 到期时间
        }
        if(isset($param['domainstatus']) && in_array($param['domainstatus'], ['Pending','Active','Cancelled','Deleted','Suspended'])){
            $update['status'] = $param['domainstatus'];
        }
        if(isset($param['suspendreason'])){
            $reason = explode("-",$param['suspendreason'])??[];
            if (!empty($reason[0])){
                if ($reason[0]=='due'){
                    $update['suspend_type'] = 'overdue';
                }elseif ($reason[0]=='flow'){
                    $update['suspend_type'] = 'overtraffic';
                }elseif ($reason[0]=='uncertifi'){
                    $update['suspend_type'] = 'certification_not_complete';
                }else{
                    $update['suspend_type'] = 'other';
                }

                $update['suspend_reason'] = $reason[1]??'';
            }

        }
        $updateResult = $HostModel->where('id',$id)->update($update);

        // 同步附加信息
        if(isset($param['os']) && isset($param['os_url']) && isset($param['username']) && isset($param['password'])){
            $HostAdditionModel = new HostAdditionModel();
            $HostAdditionModel->hostAdditionSave($id, [
                'image_icon'    => $param['os_url'],
                'image_name'    => $param['os'],
                'username'      => $param['username'],
                'password'      => $param['password'],
                'port'          => $param['port'] ?? '',
            ]);
        }

        if (isset($param['type']) && $param['type']=='create'){
            // 一开始为开通状态
            if($hostStatus=="Active"){
                // 同步IP信息
                if(isset($param['dedicatedip'])){
                    $HostIpModel = new HostIpModel();
                    $HostIpModel->hostIpSave([
                        'host_id'       => $id,
                        'dedicate_ip'   => $param['dedicatedip'],
                        'assign_ip'     => $param['assignedips'] ?? '',
                    ]);
                }

                $result['status'] = 200;
                $result['msg'] = lang('sync_success');
                return json($result);
            }else{
                // 发送开通通知
                if ($param['domainstatus']=='Active' && $hostStatus=='Pending'){

//                    system_notice([
//                        'name'              => 'host_active',
//                        'email_description' => lang('host_create_success_send_mail'),
//                        'sms_description'   => lang('host_create_success_send_sms'),
//                        'task_data' => [
//                            'client_id' => $host['client_id'],
//                            'host_id' => $id,
//                        ],
//                    ]);

                    $description = lang('log_module_create_account_success', [
                        '{host}'=> 'host#'.$id.'#'.$host['name'].'#',
                    ]);
                    active_log($description, 'host', $id);
                }elseif ($param['domainstatus']=="Pending"){ // 开通失败
                    $description = lang('log_module_create_account_failed', [
                        '{host}'=>'host#'.$host->id.'#'.$host['name'].'#',
                        '{reason}'=> lang('failed_to_open_upstream'),
                    ]);
                    active_log($description, 'host', $id);
                }else{
                    $result['status'] = 400;
                    $result['msg'] = lang('sync_failed');
                    return json($result);
                }
            }
        }
        if ($updateResult){
            $map = [
                'name' => lang('host_name'),
                'status' => lang('host_status'),
                'due_time' => lang('host_due_time'),
                'suspend_type' => lang('suspend_type'),
                'suspend_reason' => lang('suspend_reason'),
            ];
            $desc = "";
            foreach ($update as $k=>$v){
                if (isset($host[$k]) && $host[$k]!=$v){
                    $desc .= $map[$k] . lang('change_into') . $v . ',';
                }
            }
            active_log(rtrim($desc,','), 'host', $id);
        }

        // 同步IP信息
        if(isset($param['dedicatedip'])){
            $HostIpModel = new HostIpModel();
            $HostIpModel->hostIpSave([
                'host_id'       => $id,
                'dedicate_ip'   => $param['dedicatedip'],
                'assign_ip'     => $param['assignedips'] ?? '',
            ]);
        }

        active_log(lang("upstream_host_update",["{id}"=>$id,"{status}"=>$param['domainstatus']??""]),"host",$id);

        // wyh 20240315 继续同步至下游v10
        $UpstreamHostModel = new UpstreamHostModel();
        $upstreamHostUpstream = $UpstreamHostModel->where('upstream_host_id', $id)->find();

        if ($upstreamHostUpstream){
            // ['Pending','Active','Cancelled','Deleted','Suspended']
            if (isset($param['type']) && $param['type']=='create'){
                $action = 'module_create';
            }elseif (isset($param['domainstatus']) && $param['domainstatus']=='Suspended'){
                $action = "module_suspend";
            }elseif (isset($param['domainstatus']) && $param['domainstatus']=='Active'){
                $action = "module_unsuspend";
            }elseif (isset($param['domainstatus']) && $param['domainstatus']=='Deleted'){
                $action = "module_terminate";
            }else{
                $action = "update_host";
            }
            $HostModel->upstreamSyncHost($id,$action);
        }

        $result['status'] = 200;
        $result['msg'] = lang('sync_success');
        return json($result);
    }

    // 同步数据至下游（魔方财务）
    public function syncDownStreamHost()
    {
        $param = request()->param();

        $HostModel = new HostModel();

        $host = $HostModel->where('is_delete', 0)->find($param['id']??0);

        $data = $HostModel->syncDownStreamHost($host);

        return json([
            'status'=>200,
            'msg'=>lang('sync_success'),
            'data' => $data
        ]);
    }
}