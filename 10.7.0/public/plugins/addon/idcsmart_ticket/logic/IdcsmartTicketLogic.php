<?php
namespace addon\idcsmart_ticket\logic;

use addon\idcsmart_ticket\model\IdcsmartTicketInternalModel;
use addon\idcsmart_ticket\model\IdcsmartTicketModel;
use addon\idcsmart_ticket\IdcsmartTicket;
use addon\idcsmart_ticket\model\IdcsmartTicketReplyModel;
use addon\idcsmart_ticket\model\IdcsmartTicketStatusModel;
use addon\idcsmart_ticket\model\IdcsmartTicketTypeModel;
use addon\idcsmart_ticket\model\IdcsmartTicketUpstreamModel;
use app\admin\model\PluginModel;

class IdcsmartTicketLogic
{
    # 默认配置
    public static function getDefaultConfig($name = '')
    {
        $fileConfig = include dirname(__DIR__) . '/config/config.php';

        $dbConfig = (new IdcsmartTicket())->getConfig();
        
        $config = array_merge($fileConfig?:[],$dbConfig?:[]);

        return isset($config[$name])?$config[$name]:$config;
    }

    # 工单号生成
    public function ticketNum($prefix='YHGD')
    {
        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $ticketNum = rand_str(7,'NUMBER');

        for ($i=0;$i<10;$i++){ # 至多10次比较
            $exist = $IdcsmartTicketModel->where('ticket_num',$ticketNum)->find();
            if (empty($exist)){
                break;
            }
            $ticketNum = rand_str(7,'NUMBER');
        }

        return [$ticketNum,$ticketNum];
    }

    public function setConfig($param)
    {
        $PluginModel = new PluginModel();

        $plugin = $PluginModel->where('name','IdcsmartTicket')->find();

        $config =  json_decode($plugin['config'],true);

        $config['refresh_time'] = $param['refresh_time']??($config['refresh_time']??3);
        $config['ticket_notice_open'] = $param['ticket_notice_open']??($config['ticket_notice_open']??1);
        if (isset($param['ticket_notice_description']) && !empty($param['ticket_notice_description'])){
            $config['ticket_notice_description'] = htmlspecialchars($param['ticket_notice_description']);
        }else{
            $config['ticket_notice_description'] = $config['ticket_notice_description']??"";
        }

        if (isset($param['ticket_type_id']) && !empty($param['ticket_type_id'])){
            $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();
            $ticketType = $IdcsmartTicketTypeModel->where('id',$param['ticket_type_id'])->find();
            if (empty($ticketType)){
                return ['status'=>400,'msg'=>lang_plugins('ticket_type_is_not_exist')];
            }
            $config['ticket_type_id'] = $param['ticket_type_id'];
        }
        $config['downstream_delivery'] = intval($param['downstream_delivery']??0);

        $config['ticket_close_switch'] = $param['ticket_close_switch']??($config['ticket_close_switch']??0);
        $config['ticket_close_hour'] = $param['ticket_close_hour']??($config['ticket_close_hour']??0);

        $plugin->save([
            'config' => json_encode($config)
        ]);

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message')
        ];
    }

    /**
     * 时间 2022-5-25
     * @title curl下载解压包到指定路径
     * @desc curl下载解压包到指定路径
     * @author theworld
     * @version v1
     * @param string url - 下载链接地址
     * @param string file_name - 目标路径
     * @return mixed
     */
    public static function curl_download($url, $file_name)
    {
        $ch = curl_init($url);
        //设置抓取的url
        $dir = $file_name;
        $fp = fopen($dir, "wb");
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $res=curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return $res;
    }

    public static function idcsmartApiCurlUploadFile($api_id,$file_path,$file_name)
    {
        $login = idcsmart_api_login($api_id);
        if ($login['status']!=200){
            return $login;
        }

        if($login['data']['supplier']['type']!='default'){
            return ['status'=>400,'msg'=>lang_plugins('ticket_supplier_not_support_delivery')];
        }

        // 下载对象存储的文件
        $fileUrl = getOssUrl([
            'file_path' => $file_path,
            'file_name' => $file_name
        ])['url'];

        if (strpos($file_name,'^')!==false){
            $file_name = explode('^',$file_name)[1];
        }

        $file = UPLOAD_DEFAULT.$file_name;

        self::curl_download($fileUrl, $file);

        if (!file_exists($file)){
            return ['status'=>400,'msg'=>lang_plugins('source_file_not_exist')];
        }

        $url = $login['data']['url'] . '/console/v1/upload';

        $header = [
            'Authorization: Bearer '.$login['data']['jwt']
        ];

        $file = realpath($file);

        $data = ['file' => new \CURLFile($file)];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        $ssl = substr($url, 0, 8) == 'https://' ? true : false;
        //if ($ssl) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        //}
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        if(!empty($header)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($file==UPLOAD_DEFAULT.$file_name){
            unlink($file);
        }

        if ($http_code!=200){
            return ['status'=>400, 'msg'=>lang_plugins('ticket_network_desertion')];
        }

        $response = json_decode($response,true);

        return $response;
    }

    /**
     * 时间 2024-06-18
     * @title 判断后台当前工单是否可操作
     * @desc 判断后台当前工单是否可操作，涉及：
     * @author wyh
     * @version v1
     * @param int ticketId - 工单ID required
     * @return boolean
     */
    public static function checkUpstreamTicket($ticketId)
    {
        // 考虑到目前逻辑是一个工单最多对应一个产品，所以这里直接拉取
        $IdcsmartTicketUpstreamModel = new IdcsmartTicketUpstreamModel();
        $ticketUpstream = $IdcsmartTicketUpstreamModel->where('ticket_id',$ticketId)->find();
        // 1、没有对应的上游工单，可操作
        if (empty($ticketUpstream)){
            return true;
        }
        // 2、对于多级传递来说，中间层不可操作(有上游，也有下游)
        $IdcsmartTicketModel = new IdcsmartTicketModel();
        $ticket = $IdcsmartTicketModel->find($ticketId);
        if ($ticket['downstream_ticket_id']>0){
            // 若本地已关闭传递，且下游已开启传递或者已关闭；则可操作
            if ($ticketUpstream['delivery_status']==0/* && $ticket['downstream_delivery_status']==1*/){
                return true;
            }
            return false;
        }

        // 3、已关闭传递，可操作
        if ($ticketUpstream['delivery_status']==0){
            return true;
        }

        return false;
    }

    /**
     * 时间 2024-06-18
     * @title 判断前台当前工单是否可操作
     * @desc 判断前台当前工单是否可操作，涉及：
     * @author wyh
     * @version v1
     * @param int ticketId - 工单ID required
     * @return boolean
     */
    public static function checkDownstreamTicket($ticketId)
    {
        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $ticket = $IdcsmartTicketModel->find($ticketId);

        if ($ticket['is_downstream']==1 && $ticket['downstream_delivery_status']==1){
            return false;
        }

        return true;
    }

    // 后台判断是否可以向下游推送
    public static function checkPushTicketToDownstream(IdcsmartTicketModel $ticket)
    {
        if ($ticket['is_downstream'] && $ticket['downstream_delivery_status']==1){
            return true;
        }

        return false;
    }

    // 下游验证签名
    public static function validateSign($params){
        $sign = $params['signature'];
        // 用这几个参数生成签名
        $data = [
            'id'=>(int)$params['id'], // 数据类型必须和生成签名时的类型一致
            'token'=>$params['token'],
            'rand_str'=>$params['rand_str'],
        ];
        ksort($data, SORT_STRING);
        $str = json_encode($data);
        $signature = md5($str);
        return strtoupper($signature) === $sign;
    }

    // 推送通用curl请求
    public static function commonCurl($url, $data = [], $timeout = 30, $request = 'POST', $header = [])
    {
        $result = curl($url,$data,$timeout,$request,$header);

        if($result['http_code'] != 200){
            return ['status'=>400, 'msg'=>lang('network_desertion'), 'content'=>$result['content']];
        }

        $result = json_decode($result['content'], true);

        if ($result['status']==401){
            $result['status']=400;
            $result['msg'] = lang('network_desertion');
        }

        return $result;
    }

    // 推送工单状态至下游
    public static function pushTicketStatus(IdcsmartTicketModel $ticket,IdcsmartTicketStatusModel $ticketStatus,$param)
    {
        if (self::checkPushTicketToDownstream($ticket)){
            // 非默认状态
            if (!in_array($param['status'],[1,2,3,4,5])){
                // 完结状态推送至下游，下游为已回复
                if ($ticketStatus['status']==1){
                    $param['status'] = 3;
                }else{
                    // 未完结状态推送至下游，下游为处理中
                    $param['status'] = 5;
                }
            }
            $data = [
                'status' => $param['status']
            ];
            $sign = create_sign(['id'=>$ticket['downstream_ticket_id']],$ticket['downstream_token']);
            $data = array_merge($data,$sign);
            $res = IdcsmartTicketLogic::commonCurl(rtrim($ticket['downstream_url'],'/')."/console/v1/api/ticket/{$ticket['downstream_ticket_id']}/status", $data, 30, 'PUT');
            if ($res['status']==200){
                active_log(lang_plugins('ticket_log_admin_update_downstream_ticket_status_success',
                    ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{ticket}'=>'ticket#'.$ticket['ticket_num'],'{status}'=>$ticketStatus['name'],'{url}'=>$ticket['downstream_url']]),
                    'addon_idcsmart_ticket', $ticket['id']);
            }else{
                active_log(lang_plugins('ticket_log_admin_update_downstream_ticket_status_fail',
                    ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{ticket}'=>'ticket#'.$ticket['ticket_num'],'{status}'=>$ticketStatus['name'],'{url}'=>$ticket['downstream_url'],'{reason}'=>$res['msg']]),
                    'addon_idcsmart_ticket', $ticket['id']);
            }
        }

        return true;
    }

    // 推送工单回复内容更改至下游
    public static function pushTicketReply(IdcsmartTicketModel $ticket,IdcsmartTicketReplyModel $ticketReply,$param)
    {
        if (self::checkPushTicketToDownstream($ticket)){
            $data = [
                'ticket_reply_id' => $ticketReply['downstream_ticket_reply_id'],
                'content' => $param['content']??""
            ];
            $sign = create_sign(['id'=>$ticket['downstream_ticket_id']],$ticket['downstream_token']);
            $data = array_merge($data,$sign);
            $res = IdcsmartTicketLogic::commonCurl(rtrim($ticket['downstream_url'],'/')."/console/v1/api/ticket/{$ticket['downstream_ticket_id']}/reply", $data, 30, 'PUT');
            if ($res['status']==200){
                active_log(lang_plugins('ticket_log_admin_update_downstream_ticket_reply_success',
                    ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{ticket}'=>'ticket#'.$ticket['ticket_num'],'{ticket_reply_id}'=>$ticketReply['id'],'{url}'=>$ticket['downstream_url']]),
                    'addon_idcsmart_ticket', $ticket['id']);
            }else{
                active_log(lang_plugins('ticket_log_admin_update_downstream_ticket_reply_fail',
                    ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{ticket}'=>'ticket#'.$ticket['ticket_num'],'{ticket_reply_id}'=>$ticketReply['id'],'{url}'=>$ticket['downstream_url'],'{reason}'=>$res['msg']]),
                    'addon_idcsmart_ticket', $ticket['id']);
            }
        }

        return true;
    }

    // 推送工单回复删除至下游
    public static function pushTicketReplyDelete(IdcsmartTicketModel $ticket,IdcsmartTicketReplyModel $ticketReply,$param)
    {
        if (self::checkPushTicketToDownstream($ticket)){
            $data = [
                'ticket_reply_id' => $ticketReply['downstream_ticket_reply_id'],
            ];
            $sign = create_sign(['id'=>$ticket['downstream_ticket_id']],$ticket['downstream_token']);
            $data = array_merge($data,$sign);
            $res = IdcsmartTicketLogic::commonCurl(rtrim($ticket['downstream_url'],'/')."/console/v1/api/ticket/{$ticket['downstream_ticket_id']}/reply", $data, 30, 'DELETE');
            if ($res['status']==200){
                active_log(lang_plugins('ticket_log_admin_update_downstream_ticket_reply_delete_success',
                    ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{ticket}'=>'ticket#'.$ticket['ticket_num'],'{ticket_reply_id}'=>$ticketReply['id'],'{url}'=>$ticket['downstream_url']]),
                    'addon_idcsmart_ticket', $ticket['id']);
            }else{
                active_log(lang_plugins('ticket_log_admin_update_downstream_ticket_reply_delete_fail',
                    ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{ticket}'=>'ticket#'.$ticket['ticket_num'],'{ticket_reply_id}'=>$ticketReply['id'],'{url}'=>$ticket['downstream_url'],'{reason}'=>$res['msg']]),
                    'addon_idcsmart_ticket', $ticket['id']);
            }
        }

        return true;
    }

    // 推送工单回复至下游
    public static function pushTicketReplyCreate(IdcsmartTicketModel $ticket,IdcsmartTicketReplyModel $ticketReply,$param)
    {
        if (self::checkPushTicketToDownstream($ticket)){
            $data = [
                'content' => htmlspecialchars_decode($ticketReply['content']),
                'upstream_ticket_reply_id' => $ticketReply['id'],
                'attachment' => [],
            ];
            $sign = create_sign(['id'=>$ticket['downstream_ticket_id']],$ticket['downstream_token']);
            $data = array_merge($data,$sign);
            $res = IdcsmartTicketLogic::commonCurl(rtrim($ticket['downstream_url'],'/')."/console/v1/api/ticket/{$ticket['downstream_ticket_id']}/reply", $data, 30, 'POST');
            if ($res['status']==200){
                $ticketReply->save([
                    'is_downstream' => 1,
                    'downstream_ticket_reply_id' => $res['data']['ticket_reply_id'],
                    'update_time' => time()
                ]);
                active_log(lang_plugins('ticket_log_admin_update_downstream_ticket_reply_create_success',
                    ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{ticket}'=>'ticket#'.$ticket['ticket_num'],'{ticket_reply_id}'=>$ticketReply['id'],'{url}'=>$ticket['downstream_url']]),
                    'addon_idcsmart_ticket', $ticket['id']);
            }else{
                active_log(lang_plugins('ticket_log_admin_update_downstream_ticket_reply_create_fail',
                    ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{ticket}'=>'ticket#'.$ticket['ticket_num'],'{ticket_reply_id}'=>$ticketReply['id'],'{url}'=>$ticket['downstream_url'],'{reason}'=>$res['msg']]),
                    'addon_idcsmart_ticket', $ticket['id']);
            }
        }

        return true;
    }

}