<?php
namespace addon\idcsmart_ticket\model;

use addon\idcsmart_ticket\logic\IdcsmartTicketLogic;
use app\admin\model\AdminModel;
use app\admin\model\AdminRoleLinkModel;
use app\admin\model\AdminRoleModel;
use app\admin\model\PluginModel;
use app\common\logic\UploadLogic;
use app\common\model\ClientModel;
use app\common\model\FileLogModel;
use app\common\model\HostModel;
use app\common\model\OrderModel;
use app\common\model\ProductModel;
use app\common\model\SupplierModel;
use app\common\model\SystemLogModel;
use app\common\model\UpstreamHostModel;
use think\db\Query;
use think\Model;

/*
 * @author wyh
 * @time 2022-06-20
 */
class IdcsmartTicketModel extends Model
{
    protected $name = 'addon_idcsmart_ticket';

    # 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'ticket_num'                       => 'string',
        'num'                              => 'int',
        'client_id'                        => 'int',
        'title'                            => 'string',
        'ticket_type_id'                   => 'int',
        'content'                          => 'string',
        'status'                           => 'int',
        'attachment'                       => 'string',
        'last_reply_time'                  => 'int',
        'post_time'                        => 'int',
        'notes'                            => 'string',
        'admin_id'                         => 'int',
        'create_time'                      => 'int',
        'update_time'                      => 'int',
        'last_reply_admin_id'              => 'int',
        'post_admin_id'                    => 'int',
        'is_downstream'                    => 'int',
        'downstream_delivery'              => 'int',
        'downstream_source'                => 'string',
        'downstream_token'                 => 'string',
        'downstream_url'                   => 'string',
        'downstream_ticket_id'             => 'int',
        'downstream_delivery_status'       => 'int',
        'token'                            => 'string',
    ];

    # 是否后台
    public $isAdmin = false;

    # 检查是否工单所属部门的管理人员
    private function checkAdmin($id)
    {
        if (!$this->isAdmin){
            return true;
        }

        # 超级管理员查看所有?目前
        if (get_admin_id() == 1){
            return true;
        }

        $ticket = $this->find($id);
        if (empty($ticket)){
            return false;
        }

        $allowAdmin = IdcsmartTicketTypeAdminLinkModel::where('ticket_type_id', $ticket['ticket_type_id'])->column('admin_id');
        if(!in_array(get_admin_id(), $allowAdmin)){
            return false;
        }
        return true;
    }

    /**
     * 时间 2022-06-20
     * @title 工单列表
     * @desc 工单列表
     * @author wyh
     * @version v1
     * @param string keywords - 关键字
     * @param int status - 状态搜索(/console/v1/ticket/status get获取状态列表)
     * @param int ticket_type_id - 工单类型搜索(/console/v1/ticket/type get获取类型列表)
     * @param int client_ -
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list - 工单列表
     * @return int list[].id - ID
     * @return string list[].ticket_num - 工单号
     * @return string list[].title - 标题
     * @return string list[].name - 类型
     * @return int list[].post_time - 提交时间
     * @return string list[].client_id - 用户ID
     * @return string list[].username - 客户名称
     * @return array list[].hosts - 关联产品,数组
     * @return array list[].host_ids - 关联产品ID,数组(作跳转使用)
     * @return int list[].last_reply_time - 最近回复时间
     * @return string list[].status - 状态
     * @return string list[].color - 状态颜色
     * @return string list[].admin_name - 跟进人,为null时显示-
     * @return int list[].ticket_internal - 是否有内部工单插件:1是(显示新建内部工单按钮),0否
     * @return int list[].client_level -  客户等级客户ID
     * @return int list[].last_time -  最近操作时间
     * @return int list[].downstream_delivery -  是否下游传递
     * @return int list[].upstream_ticket_id - 大于0时，为上游工单ID，显示[向上传递]
     * @return int count - 工单总数
     */
    public function ticketList($param)
    {
        $where = function (Query $query) use ($param){

            if (!$this->isAdmin){
                $query->where('t.client_id',get_client_id());
            }else{
                if (get_admin_id() != 1){ # 超级管理员查看所有?目前
                    $ticketTypeId = IdcsmartTicketTypeAdminLinkModel::where('admin_id', get_admin_id())->column('ticket_type_id');
                    $ticketTypeId = array_unique($ticketTypeId);

                    if(!empty($ticketTypeId)){
                        $query->whereIn('t.ticket_type_id', $ticketTypeId);
                    }else{
                        $query->where('t.id', 0);
                    }
                }

            }
            if (isset($param['status']) && $param['status'] && is_array($param['status'])){
                $query->whereIn('t.status',$param['status']);
            }else{
                if ($this->isAdmin){
                    $query->whereNotIn('t.status',[3,4]);
                }
            }

            if (isset($param['keywords']) && !empty($param['keywords'])){
                $query->where('t.ticket_num|t.title','like',"%{$param['keywords']}%");
            }

            if (isset($param['ticket_type_id']) && !empty($param['ticket_type_id'])){
                $query->where('t.ticket_type_id',$param['ticket_type_id']);
            }

            if ($this->isAdmin){
                if (isset($param['client_id']) && !empty($param['client_id'])){
                    $query->where('t.client_id',$param['client_id']);
                }
                if (isset($param['admin_id']) && !is_null($param['admin_id']) && strlen($param['admin_id'])>0){
                    if (!empty($param['admin_id'])){
                        $query->where('t.last_reply_admin_id',$param['admin_id']);
                    }else{
                        // 筛选上游处理工单
                        $query->where('tu.upstream_ticket_id','>',0);
                    }

                }
                if (isset($param['host_id']) && !empty($param['host_id'])){
                    $query->where('h.id',$param['host_id']);
                }
            }

        };

        if ($this->isAdmin){
            // wyh 20230510 增加 关联订单 子商品订单或父商品订单
            $whereOr = function (Query $query)use($param){
                if (!empty($param['host_id'])){
                    if (class_exists('server\idcsmart_common_dcim\model\IdcsmartCommonSonHost')){
                        $IdcsmartCommonSonHost = new \server\idcsmart_common_dcim\model\IdcsmartCommonSonHost();
                        $links = $IdcsmartCommonSonHost->where('host_id',$param['host_id'])
                            ->whereOr('son_host_id',$param['host_id'])
                            ->select()->toArray();
                        $dcimOrderIds = array_column($links,'order_id');
                        $dcimHostIds = array_column($links,'host_id');
                        $dcimSonHostIds = array_column($links,'son_host_id');
                    }
                    if (class_exists('server\idcsmart_common_finance\model\IdcsmartCommonSonHost')){
                        $IdcsmartCommonSonHost = new \server\idcsmart_common_finance\model\IdcsmartCommonSonHost();
                        $links = $IdcsmartCommonSonHost->where('host_id',$param['host_id'])
                            ->whereOr('son_host_id',$param['host_id'])
                            ->select()->toArray();
                        $financeOrderIds = array_column($links,'order_id');
                        $financeHostIds = array_column($links,'host_id');
                        $financeSonHostIds = array_column($links,'son_host_id');
                    }
                    if (class_exists('server\idcsmart_common_cloud\model\IdcsmartCommonSonHost')){
                        $IdcsmartCommonSonHost = new \server\idcsmart_common_cloud\model\IdcsmartCommonSonHost();
                        $links = $IdcsmartCommonSonHost->where('host_id',$param['host_id'])
                            ->whereOr('son_host_id',$param['host_id'])
                            ->select()->toArray();
                        $cloudOrderIds = array_column($links,'order_id');
                        $cloudHostIds = array_column($links,'host_id');
                        $cloudSonHostIds = array_column($links,'son_host_id');
                    }
                    if (class_exists('server\idcsmart_common_business\model\IdcsmartCommonSonHost')){
                        $IdcsmartCommonSonHost = new \server\idcsmart_common_business\model\IdcsmartCommonSonHost();
                        $links = $IdcsmartCommonSonHost->where('host_id',$param['host_id'])
                            ->whereOr('son_host_id',$param['host_id'])
                            ->select()->toArray();
                        $businessOrderIds = array_column($links,'order_id');
                        $businessHostIds = array_column($links,'host_id');
                        $businessSonHostIds = array_column($links,'son_host_id');
                    }
                    // 续费 和 升降级订单
                    $hostIds = array_merge($dcimHostIds??[],$dcimSonHostIds??[],$financeHostIds??[],$financeSonHostIds??[],$cloudHostIds??[],$cloudSonHostIds??[],
                        $businessHostIds??[],$businessSonHostIds??[]);

                    if (!empty($hostIds)){
                        $query->whereIn('h.id',$hostIds);
                    }
                }
            };

            $PluginModel = new PluginModel();
            $plugin = $PluginModel->where('status',1)
                ->where('name','IdcsmartTicketInternal')
                ->where('module','addon')
                ->find();

            $tickets = $this->alias('t')
                ->field('t.id,t.client_id,t.ticket_num,t.title,tt.name,t.post_time,c.username,GROUP_CONCAT(p.name Separator \'^#@^\') as hosts,GROUP_CONCAT(h.id Separator \'^#@^\') as host_ids,t.last_reply_time,ts.name as status,ts.color,a.name as admin_name,c.id as client_level,tt.id as ticket_internal,(CASE WHEN t.last_reply_time=0 THEN t.post_time WHEN t.last_reply_time>0 THEN t.last_reply_time END) last_time,
                t.is_downstream,t.downstream_delivery,tu.upstream_ticket_id')
                ->leftJoin('addon_idcsmart_ticket_type tt','t.ticket_type_id=tt.id')
                ->leftJoin('addon_idcsmart_ticket_status ts','ts.id=t.status')
                ->leftJoin('admin a','a.id=t.last_reply_admin_id')
                ->leftJoin('client c','c.id=t.client_id')
                ->leftJoin('addon_idcsmart_ticket_host_link thl','t.id=thl.ticket_id')
                ->leftJoin('host h','h.id=thl.host_id AND h.is_delete=0')
                ->leftJoin('product p','h.product_id=p.id')
                ->leftJoin('addon_idcsmart_ticket_upstream tu','tu.ticket_id=t.id')
                ->where($where)
                ->whereOr($whereOr)
                ->withAttr('hosts',function ($value){
                    if (!is_null($value)){
                        return explode('^#@^',$value);
                    }
                    return [];
                })
                ->withAttr('host_ids',function ($value){
                    if (!is_null($value)){
                        return explode('^#@^',$value);
                    }
                    return [];
                })
                ->withAttr('client_level',function ($value){
                    $hookResults = hook_one('client_level',['id'=>$value]);
                    if (!empty($hookResults)){
                        return $hookResults->background_color??"";
                    }else{
                        return '';
                    }
                })
                ->withAttr('ticket_internal',function ($value) use ($plugin){
                    if (!empty($plugin)){
                        return 1;
                    }
                    return 0;
                })
                ->withAttr('upstream_ticket_id',function ($value){
                    return intval($value);
                })
                ->group('t.id')
                ->limit($param['limit'])
                ->page($param['page'])
                ->order('last_time','desc')
                ->order('t.last_reply_time','desc')
                ->order('t.post_time','desc')
                ->order('t.id','desc')
                ->select()
                ->toArray();

            $count = $this->alias('t')
                ->leftJoin('addon_idcsmart_ticket_type tt','t.ticket_type_id=tt.id')
                ->leftJoin('addon_idcsmart_ticket_status ts','ts.id=t.status')
                ->leftJoin('admin a','a.id=t.last_reply_admin_id')
                ->leftJoin('client c','c.id=t.client_id')
                ->leftJoin('addon_idcsmart_ticket_host_link thl','t.id=thl.ticket_id')
                ->leftJoin('host h','h.id=thl.host_id AND h.is_delete=0')
                ->leftJoin('product p','h.product_id=p.id')
                ->leftJoin('addon_idcsmart_ticket_upstream tu','tu.ticket_id=t.id')
                ->where($where)
                ->whereOr($whereOr)
                ->group('t.id')
                ->count();

        }else{
            $tickets = $this->alias('t')
                ->field('t.id,t.ticket_num,t.title,tt.name,t.post_time,t.last_reply_time,ts.name as status,ts.color,ts.id as status_id')
                ->leftJoin('addon_idcsmart_ticket_type tt','t.ticket_type_id=tt.id')
                ->leftJoin('addon_idcsmart_ticket_status ts','ts.id=t.status')
                ->where($where)
                ->group('t.id')
                ->limit($param['limit'])
                ->page($param['page'])
                ->order('t.status','desc')
                ->order('t.last_reply_time','desc')
                ->select()
                ->toArray();

            foreach($tickets as $k=>$v){
                $tickets[$k]['last_urge_time'] = cache('ticket_urge_time_limit_'.$v['id']) ?? '0';
            }

            $count = $this->alias('t')
                ->leftJoin('addon_idcsmart_ticket_type tt','t.ticket_type_id=tt.id')
                ->where($where)
                ->group('t.id')
                ->count();
        }

        $data = [
            'list' => $tickets,
            'count' => $count
        ];

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    /**
     * 时间 2022-06-21
     * @title 工单统计
     * @desc 工单统计
     * @author wyh
     * @version v1
     * @return int 1 - 待接单数量
     * @return int 2 - 待回复数量
     * @return int 3 - 已回复数量
     * @return int 5 - 处理中数量
     */
    public function statisticTicket()
    {
        $status = [1,2,3,5];

        $tickets = $this->where('client_id',get_client_id())
            ->whereIn('status',$status)
            ->column('status');

        $data = [];

        $statistics = array_count_values($tickets);
        foreach ($status as $item){
            $data[strtolower($item)] = $statistics[$item]??0;
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    /**
     * 时间 2022-06-20
     * @title 查看工单
     * @desc 查看工单
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     * @return object ticket - 工单详情
     * @return int ticket.client_id - 用户ID
     * @return int ticket.id - 工单ID
     * @return string ticket.title - 工单标题
     * @return string ticket.content - 内容
     * @return int ticket.ticket_type_id - 类型ID
     * @return string ticket.status - 状态,直接显示,结合color
     * @return string ticket.color - 状态颜色
     * @return int ticket.create_time - 创建时间
     * @return array ticket.attachment - 工单附件,数组,返回所有附件(附件以^符号分割,取最后一个值获取文件原名)
     * @return int ticket.last_reply_time - 工单最后回复时间
     * @return string ticket.username - 用户名
     * @return array ticket.host_ids - 关联产品ID,数组
     * @return int ticket.can_operate - 是否可操作
     * @return int ticket.upstream_ticket_id - 上游工单ID，大于0时，为上游工单ID，显示[向上传递]
     * @return int ticket.delivery_status - 传递状态：1已开启传递，0已关闭传递
     * @return int ticket.delivery_operate - 传递操作:0不显示按钮,1发起传递,2开启传递,3终止传递
     * @return int ticket.downstream_delivery -  是否下游传递
     * @return int ticket.upstream_ticket_id - 大于0时，为上游工单ID，显示[向上传递]
     * @return array ticket.replies - 沟通记录,数组
     * @return string ticket.replies[].content - 内容
     * @return array ticket.replies[].attachment - 附件访问地址,数组
     * @return int ticket.replies[].create_time - 时间
     * @return string ticket.replies[].type - 类型:Client用户回复,Admin管理员回复
     * @return string ticket.replies[].client_name - 用户名,type==Client时用此值
     * @return string ticket.replies[].admin_name - 管理员名,type==Admin时用此值
     */
    public function indexTicket($id)
    {
        if (!$this->checkAdmin($id)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_current_admin_cannot_operate')];
        }

        // wyh 20240506 bug修改
        $where = function (Query $query) use ($id){
            $query->where('t.id',$id);
            if (!$this->isAdmin){
                $query->where('t.client_id',get_client_id());
            }
        };

        $ticket = $this->alias('t')
            ->field('t.id,t.ticket_num,t.client_id,t.title,t.content,t.ticket_type_id,ts.name as status,ts.color,
            t.create_time,t.attachment,t.last_reply_time,c.username,t.post_admin_id,a.name as admin_name,tu.upstream_ticket_id,
            tu.delivery_status,uh.upstream_host_id,t.downstream_delivery')
            ->leftJoin('client c','c.id=t.client_id')
            ->leftJoin('admin a','t.post_admin_id=a.id')
            ->leftJoin('addon_idcsmart_ticket_status ts','ts.id=t.status')
            ->leftJoin('addon_idcsmart_ticket_host_link thl','thl.ticket_id=t.id')
            ->leftJoin('upstream_host uh','uh.host_id=thl.host_id')
            ->leftJoin('addon_idcsmart_ticket_upstream tu','tu.ticket_id=t.id') // 考虑到一个工单最多对应一个产品
            ->withAttr('content',function ($value){
                if (!empty($value)){
                    return htmlspecialchars_decode($value);
                }
                return $value;
            })
            ->where($where)
            ->find();
        if (empty($ticket)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_is_not_exist')];
        }

        if (!empty($ticket['upstream_host_id'])){
            if (!empty($ticket['upstream_ticket_id'])){
                if (!empty($ticket['delivery_status'])){
                    $ticket['delivery_operate'] = 3; // 终止传递
                }else{
                    $ticket['delivery_operate'] = 2; // 开启传递
                }
            }else{
                $ticket['delivery_operate'] = 1; // 发起传递
            }
        }else{
            $ticket['delivery_operate'] = 0; // 不显示按钮
        }

        if (!$this->isAdmin){
            unset($ticket['upstream_host_id'],$ticket['delivery_status'],$ticket['upstream_host_id'],$ticket['delivery_operate']);
        }

        $IdcsmartTicketLogic = new IdcsmartTicketLogic();
        $config = $IdcsmartTicketLogic->getDefaultConfig();

        $attachments = !empty($ticket->attachment)?explode(',',$ticket->attachment):[];
        $attachmentsFilter = [];
        foreach ($attachments as &$attachment){
            // 1、旧方式
            // $attachment = $config['get_ticket_upload'] . $attachment;
            // 2、对象存储
            // 使用保存的地址会有个问题，当切换存储接口时，需要改所有数据的地址
            /*if (!empty($fileLog['url']) && is_image($tmp = urldecode($fileLog['url']))){
                $attachment = $tmp;
            }else{
                $result = plugin_reflection($ossMethod,[
                    'file_path' => $config['ticket_upload'],
                    'file_name' => $attachment
                ],'oss','download');
                if (isset($result['data']['url']) && !empty($result['data']['url'])){
                    $attachment = $result['data']['url'];
                }else{
                    $attachment = '';
                }
            }*/
            $attachmentsFilter[] = getOssUrl([
                'file_path' => $config['ticket_upload'],
                'file_name' => $attachment
            ]);
            // 3、这个是转一次的方式
            /*$FileLogModel = new FileLogModel();
            $fileLog = $FileLogModel->where('save_name',$attachment)->find();
            $res = generate_signature(['fid'=>$fileLog['uuid']],AUTHCODE);
            $attachment = request()->domain(). "/console/v1/resource/".$fileLog['name']."?fid=".$fileLog['uuid']."&rand_str=". $res['rand_str'] ."&sign=".$res['signature'];*/
        }
        $ticket->attachment = $attachmentsFilter;

        $IdcsmartTicketHostLinkModel = new IdcsmartTicketHostLinkModel();
        $ticket['host_ids'] = $IdcsmartTicketHostLinkModel->where('ticket_id',$id)->column('host_id');

        $IdcsmartTicketReplyModel = new IdcsmartTicketReplyModel();

        if ($this->isAdmin){
            $field = 'tr.id,tr.content,tr.attachment,tr.create_time,tr.type,c.username as client_name,a.name as admin_name,c.id as client_id,tr.upstream_ticket_reply_id,tr.downstream_ticket_reply_id,tr.quote_reply_id';
        }else{
            $field = 'tr.id,tr.content,tr.attachment,tr.create_time,tr.type,c.username as client_name,a.nickname as admin_name,c.id as client_id,tr.quote_reply_id';
        }

        $ticketReplies = $IdcsmartTicketReplyModel->alias('tr')
            ->field($field)
            ->leftJoin('client c','c.id=tr.rel_id AND tr.type=\'Client\'')
            ->leftJoin('admin a','a.id=tr.rel_id AND tr.type=\'Admin\'')
            ->withAttr('attachment',function ($value) use ($config){
                if (!empty($value)){
                    $attachments = explode(',',$value);
                }else{
                    $attachments = [];
                }
                $attachmentsFilter = [];
                if (!empty($attachments)){
                    foreach ($attachments as &$attachment){
                        // 使用保存的地址会有个问题，当切换存储接口时，需要改所有数据的地址
//                        if (!empty($fileLog['url']) && is_image($tmp = urldecode($fileLog['url']))){
//                            $attachment = $tmp;
//                        }else{
//                            $result = plugin_reflection($ossMethod,[
//                                'file_path' => $config['ticket_upload'],
//                                'file_name' => $attachment
//                            ],'oss','download');
//                            if (isset($result['data']['url']) && !empty($result['data']['url'])){
//                                $attachment = $result['data']['url'];
//                            }else{
//                                $attachment = '';
//                            }
//                        }
                        $attachmentsFilter[] = getOssUrl([
                            'file_path' => $config['ticket_upload'],
                            'file_name' => $attachment
                        ]);
                        // $attachment = $config['get_ticket_upload'] . $attachment;
                        //  对象存储
                        /*$FileLogModel = new FileLogModel();
                        $fileLog = $FileLogModel->where('save_name',$attachment)->find();
                        $res = generate_signature(['fid'=>$fileLog['uuid']],AUTHCODE);
                        $attachment = request()->domain(). "/console/v1/resource/".$fileLog['name']."?fid=".$fileLog['uuid']."&rand_str=". $res['rand_str'] ."&sign=".$res['signature'];*/
                    }
                }
                return $attachmentsFilter;
            })
            ->withAttr('content',function ($value){
                if (!empty($value)){
                    return htmlspecialchars_decode($value);
                }
                return $value;
            })
            ->withAttr('admin_name',function ($value,$data){
                if (isset($data['upstream_ticket_reply_id']) && $data['upstream_ticket_reply_id']>0){
                    return lang_plugins('ticket_upstream_admin');
                }
                /*if (isset($data['downstream_ticket_reply_id']) && $data['downstream_ticket_reply_id']>0){
                    return lang_plugins('ticket_downstream_admin');
                }*/
                if (is_null($value)){
                    return '';
                }

                return $value;
            })
            ->withAttr('client_name',function ($value){
                if (is_null($value)){
                    return '';
                }

                return $value;
            })
            ->where('tr.ticket_id',$id)
            ->order('tr.create_time','desc')
            ->select()->toArray();

        // 处理引用回复信息
        foreach ($ticketReplies as &$reply) {
            $reply['quote_info'] = null;
            if (!empty($reply['quote_reply_id'])) {
                $reply['quote_info'] = $IdcsmartTicketReplyModel->getQuoteReplyInfo($reply['quote_reply_id']);
            }
        }

        array_push($ticketReplies,['id'=>0,'content'=>$ticket->content,'attachment'=>$ticket->attachment,'create_time'=>$ticket->create_time,'type'=>'Client','client_name'=>$ticket->post_admin_id?$ticket['admin_name']:$ticket->username,'admin_name'=>'','client_id'=>$ticket['client_id'],'quote_reply_id'=>0,'quote_info'=>null]);

        $ticket['replies'] = $ticketReplies;

        // wyh 20240619 新增：工单传递，一些动作是否可操作
        if ($this->isAdmin){
            $ticket['can_operate'] = IdcsmartTicketLogic::checkUpstreamTicket($id)?1:0;
        }else{
            $ticket['can_operate'] = IdcsmartTicketLogic::checkDownstreamTicket($id)?1:0;
        }

        if (!empty($ticket['host_ids'][0])){
            $hostId = $ticket['host_ids'][0];
            $host = HostModel::where('id',$hostId)->find();
            $product = ProductModel::where('id',$host['product_id']??0)->find();
            if (!empty($product)){
                $module = $product->getModule();
                if ($module=='idcsmart_common_cloud'){
                    $IdcsmartCommonSonHost = new \server\idcsmart_common_cloud\model\IdcsmartCommonSonHost();
                    $parentHostId = $IdcsmartCommonSonHost->where('son_host_id',$hostId)->value('host_id');
                    if (!empty($parentHostId)){
                        $parentHost = HostModel::where('id',$parentHostId)->find();
                        if (!empty($parentHost)){
                            $IdcsmartCommonProductConfigoptionModel = new \server\idcsmart_common_cloud\model\IdcsmartCommonProductConfigoptionModel();
                            $cid = $IdcsmartCommonProductConfigoptionModel->where('product_id',$parentHost['product_id'])
                                ->where('option_param','max_node')
                                ->value('id');
                            $IdcsmartCommonHostConfigoptionModel = new \server\idcsmart_common_cloud\model\IdcsmartCommonHostConfigoptionModel();
                            $qty = $IdcsmartCommonHostConfigoptionModel->where('host_id',$parentHostId)
                                ->where('configoption_id',$cid)
                                ->value('qty');
                            if (!empty($qty)){
                                $ticket['node_num'] = $qty;
                            }
                        }
                    }
                }elseif ($module=='idcsmart_common_dcim'){
                    $IdcsmartCommonSonHost = new \server\idcsmart_common_dcim\model\IdcsmartCommonSonHost();
                    $parentHostId = $IdcsmartCommonSonHost->where('son_host_id',$hostId)->value('host_id');
                    if (!empty($parentHostId)){
                        $parentHost = HostModel::where('id',$parentHostId)->find();
                        if (!empty($parentHost)){
                            $IdcsmartCommonProductConfigoptionModel = new \server\idcsmart_common_dcim\model\IdcsmartCommonProductConfigoptionModel();
                            $cid = $IdcsmartCommonProductConfigoptionModel->where('product_id',$parentHost['product_id'])
                                ->where('option_param','authorized_num')
                                ->value('id');
                            $IdcsmartCommonHostConfigoptionModel = new \server\idcsmart_common_dcim\model\IdcsmartCommonHostConfigoptionModel();
                            $qty = $IdcsmartCommonHostConfigoptionModel->where('host_id',$parentHostId)
                                ->where('configoption_id',$cid)
                                ->value('qty');
                            if (!empty($qty)){
                                $ticket['node_num'] = $qty;
                            }
                        }
                    }
                }
            }
        }

        $IdcsmartTicketForwardModel = new IdcsmartTicketForwardModel();
        $forwards = $IdcsmartTicketForwardModel->alias('tf')
            ->field('a.nickname,aa.nickname forward_nickname,tf.notes,tf.create_time,tf.id,itt.name ticket_type_name')
            ->leftJoin('admin a','a.id=tf.admin_id')
            ->leftJoin('admin aa','aa.id=tf.forward_admin_id')
            ->leftJoin('addon_idcsmart_ticket it','it.id=tf.ticket_id')
            ->leftJoin('addon_idcsmart_ticket_type itt','itt.id=it.ticket_type_id')
            ->where('tf.ticket_id',$id)
            ->order('tf.id','desc')
            ->select()->toArray();
        $ticket['forwards'] = $forwards;

        $data = [
            'ticket' => $ticket
        ];

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    /**
     * 时间 2022-06-20
     * @title 创建工单
     * @desc 创建工单
     * @author wyh
     * @version v1
     * @param string title - 工单标题 required
     * @param int ticket_type_id - 工单类型ID,/console/v1/ticket/type接口获取 required
     * @param array host_ids - 关联产品ID,数组(id从产品列表接口获取)
     * @param string content - 问题描述
     * @param array attachment - 附件,数组(后台调admin/v1/upload(前台调console/v1/upload)上传文件,取返回值save_name)
     */
    public function createTicket($param)
    {
        $this->startTrans();

        try{
            $IdcsmartTicketLogic = new IdcsmartTicketLogic();
            $ticketNum = $IdcsmartTicketLogic->ticketNum();

            $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();
            $idcsmartTicketType = $IdcsmartTicketTypeModel->find($param['ticket_type_id']);

            $clientId = $this->isAdmin?$param['client_id']:get_client_id();

            $data = [
                'ticket_num' => $ticketNum[0],
                'num' => $ticketNum[1],
                'client_id' => $clientId,
                'title' => $param['title'],
                'ticket_type_id' => $param['ticket_type_id'],
                'content' => isset($param['content'])?htmlspecialchars($param['content']):'',
                'status' => $this->isAdmin?3:1, // 后台直接已回复
                'attachment' => (isset($param['attachment']) && !empty($param['attachment']))?implode(',',$param['attachment']):'',
                'last_reply_time' => $this->isAdmin?time():0,
                'create_time' => time(),
                'post_time' => time(),
                'notes' => $param['notes']??'',
                'post_admin_id' => get_admin_id()??0,
            ];

            // wyh 20240617 前台，下游传递
            if (!$this->isAdmin && isset($param['is_downstream']) && $param['is_downstream']==1){
                $config = IdcsmartTicketLogic::getDefaultConfig();
                if (isset($config['ticket_type_id']) && !empty($config['ticket_type_id'])){
                    $data['ticket_type_id'] = $config['ticket_type_id'];
                }else{
                    $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();
                    $ticketType = $IdcsmartTicketTypeModel->order('id','asc')->find();
                    $data['ticket_type_id'] = $ticketType['id'];
                }
                $data['is_downstream'] = $param['is_downstream'];
                $data['downstream_delivery'] = $param['downstream_delivery'];
                $data['downstream_source'] = $param['downstream_source'];
                $data['downstream_token'] = $param['downstream_token'];
                $data['downstream_url'] = $param['downstream_url'];
                $data['downstream_ticket_id'] = $param['downstream_ticket_id'];
            }

            $ticket = $this->create($data);

            $IdcsmartTicketHostLinkModel = new IdcsmartTicketHostLinkModel();
            $hostIds = $param['host_ids']?:[];
            $insert = [];
            foreach ($hostIds as $item){
                $insert[] = [
                    'ticket_id' => $ticket->id,
                    'host_id' => $item
                ];
            }
            if (!empty($insert)){
                $IdcsmartTicketHostLinkModel->insertAll($insert);
            }

            # 移动附件
            $UploadLogic = new UploadLogic($IdcsmartTicketLogic->getDefaultConfig('ticket_upload'));
            if (isset($param['attachment']) && !empty($param['attachment'])){
                $result = $UploadLogic->moveTo($param['attachment'],'','ticket');
                if (isset($result['error'])){
                    throw new \Exception($result['error']);
                }
            }

            if (!$this->isAdmin){
                # 记录日志
                active_log(lang_plugins('ticket_log_client_create_ticket', ['{client}'=>'client#'.$clientId.'#' .request()->client_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);
            }else{
                # 管理员创建工单日志
                active_log(lang_plugins('ticket_log_admin_create_ticket', ['{admin}'=>'admin#'.get_admin_id().'#' .request()->admin_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);
            }

            $this->commit();

        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage().$e->getLine()];
        }

        if(!$this->isAdmin){
            system_notice([
                'name' => 'client_create_ticket',
                'email_description' => lang_plugins('client_create_ticket_send_mail'),
                'sms_description' => lang_plugins('client_create_ticket_send_sms'),
                'task_data' => [
                    'client_id'=>get_client_id(),//客户ID
                    'template_param'=>[
                        'subject' => $param['title'],//工单名称
                        'ticket_num' => $ticket->ticket_num,
                        'ticket_create_time' => date('Y-m-d H:i:s',$ticket->create_time),
                    ],
                ],
            ]);
        }

        # wyh 20240614 传递工单至上游
        if (!empty($hostIds)){
            $IdcsmartTicketDeliveryModel = new IdcsmartTicketDeliveryModel();
            $IdcsmartTicketUpstreamModel = new IdcsmartTicketUpstreamModel();
            $UpstreamHostModel = new UpstreamHostModel();
            $ticketUpstream = [];
            foreach ($hostIds as $hostId){
                $result = $IdcsmartTicketDeliveryModel->delivery([
                    'host_id' => $hostId,
                    'ticket_id' => $ticket->id,
                ]);
                if ($result['status']==200){
                    $upstreamHost = $UpstreamHostModel->where('host_id',$hostId)->find();
                    $ticketUpstream[] = [
                        'host_id' => $hostId,
                        'upstream_host_id' => $upstreamHost['upstream_host_id']??0,
                        'ticket_id' => $ticket->id,
                        'upstream_ticket_id' => $result['data']['id'],
                        'create_time' => time(),
                        'delivery_status' => 1,
                    ];
                }
            }
            if (!empty($ticketUpstream)){
                $IdcsmartTicketUpstreamModel->insertAll($ticketUpstream);
            }
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['id'=>$ticket->id]];
    }

    /**
     * 时间 2022-06-21
     * @title 回复工单
     * @desc 回复工单
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     * @param string content - 回复内容,不超过3000个字符 required
     * @param array attachment - 附件,数组(后台调admin/v1/upload(前台调console/v1/upload)上传文件,取返回值save_name)
     * @return int ticket_reply_id - 回复ID
     */
    public function replyTicket($param)
    {
        $this->startTrans();

        try{
            $time = time();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticket = $this->find($id);
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            if ($this->isAdmin){
                // wyh 20240619 新增：对于后台，最上游才可以回复
                if (!IdcsmartTicketLogic::checkUpstreamTicket($id)){
                    throw new \Exception(lang_plugins('ticket_upstream_cannot_operate'));
                }
                $clientId = $ticket->client_id;
            }else{
                if (!(isset($param['is_downstream']) && $param['is_downstream']==1)){
                    if (!IdcsmartTicketLogic::checkDownstreamTicket($id)){
                        throw new \Exception(lang_plugins('ticket_downstream_cannot_operate'));
                    }
                }

                $clientId = get_client_id();
                if ($clientId != $ticket->client_id){
                    throw new \Exception(lang_plugins('ticket_is_not_exist'));
                }
                // 20240122 新增my的逻辑：根据关联产品的到期时间判断，到期后用户无法再回复
                if (class_exists('server\idcsmart_common_finance\model\IdcsmartCommonSonHost')){
                    $IdcsmartTicketHostLinkModel = new IdcsmartTicketHostLinkModel();
                    $hostId = $IdcsmartTicketHostLinkModel->where('ticket_id',$id)
                        ->order("ticket_id","desc")
                        ->value("host_id");
                    $HostModel = new HostModel();
                    $host = $HostModel->find($hostId);
                    if ((!empty($host) && $host['billing_cycle']!="onetime" && $host['due_time']<=$time && $host['is_delete'] == 0) || (!empty($host) && $host['status']=='Deleted')){
                        throw new \Exception(lang_plugins("ticket_host_due_can_not_reply"));
                    }
                    if ($ticket['status']==4){
                        throw new \Exception("工单已关闭，不能回复");
                    }
                }
            }

            # 移动附件
            $IdcsmartTicketLogic = new IdcsmartTicketLogic();
            $UploadLogic = new UploadLogic($IdcsmartTicketLogic->getDefaultConfig('ticket_upload'));
            if (isset($param['attachment']) && !empty($param['attachment'])){
                $result = $UploadLogic->moveTo($param['attachment'],'','ticket');
                if (isset($result['error'])){
                    throw new \Exception($result['error']);
                }
            }

            $data = [
                'ticket_id' => $id,
                'type' => $this->isAdmin?'Admin':'Client',
                'rel_id' => $this->isAdmin?get_admin_id():$clientId,
                'content' => htmlspecialchars($param['content']),
                'attachment' => (isset($param['attachment']) && !empty($param['attachment']))?implode(',',$param['attachment']):'',
                'create_time' => $time,
                'quote_reply_id' => intval($param['quote_reply_id'] ?? 0)
            ];

            if (isset($param['is_downstream']) && $param['is_downstream']==1){
                $data['is_downstream'] = $param['is_downstream'];
                $data['downstream_ticket_reply_id'] = $param['downstream_ticket_reply_id']??0;
                // 下游传递的工单，不保留引用关系
                $data['quote_reply_id'] = 0;
            }

            $IdcsmartTicketReplyModel = new IdcsmartTicketReplyModel();
            $ticketReply = $IdcsmartTicketReplyModel->create($data);

            # 状态逻辑
            $status = $ticket->status;
            if ($this->isAdmin){
                $status = 3;
            }else{
                if (!in_array($ticket->status,[1,2])){ # 待接收(处理中)工单用户回复,不改变状态
                    $status = 2;
                }
            }

            $update = [
                'last_reply_time' => $time,
                'status' => $status
            ];

            if ($this->isAdmin){
                $update['last_reply_admin_id'] = get_admin_id(); # 最近一次回复管理员ID(跟进人)
            }

            $ticket->save($update);

            # 记录日志
            if ($this->isAdmin){
                active_log(lang_plugins('ticket_log_admin_reply_ticket_admin', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);
                active_log(lang_plugins('ticket_log_admin_reply_ticket', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);
            }else{
                $IdcsmartTicketReplyModel = new IdcsmartTicketReplyModel();

                active_log(lang_plugins('ticket_log_client_reply_ticket', ['{client}'=>'client#'.get_client_id() .'#' .request()->client_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#','{content}'=>$ticketReply->content]), 'addon_idcsmart_ticket', $ticket->id);
            }
			
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        // 1、客户回复，向上游传递
        if (!$this->isAdmin){
            system_notice([
                'name' => 'client_reply_ticket',
                'email_description' => lang_plugins('client_reply_ticket_send_mail'),
                'task_data' => [
                    'client_id'=>$clientId,//客户ID
                    'template_param'=>[
                        'subject' => $ticket['title'],//工单名称
                        'ticket_num'    => $ticket->ticket_num,
                        'admin_last_reply_time' => date('Y-m-d H:i:s', $IdcsmartTicketReplyModel->getAdminLastReplyTime($ticket->id) ?: $ticket->create_time),
                        'ticket_create_time' => date('Y-m-d H:i:s', $ticket->create_time),
                    ],
                ],
            ]);

            $IdcsmartTicketDeliveryModel = new IdcsmartTicketDeliveryModel();
            $result = $IdcsmartTicketDeliveryModel->deliveryReply([
                'ticket_id' => $id,
                'ticket_reply_id' => $ticketReply->id
            ]);
            if ($result['status']==200){
                $ticketReply->save([
                    'upstream_ticket_reply_id' => $result['data']['ticket_reply_id'],
                    'update_time' => $time
                ]);
            }
        }else{
            system_notice([
                'name' => 'admin_reply_ticket',
                'email_description' => lang_plugins('admin_reply_ticket_send_mail'),
                'sms_description' => lang_plugins('admin_reply_ticket_send_sms'),
                'task_data' => [
                    'client_id'=>$clientId,//客户ID
                    'template_param'=>[
                        'subject'       => $ticket['title'],//工单名称
                        'ticket_num'    => $ticket->ticket_num,
                        'admin_last_reply_time'=> date('Y-m-d H:i:s', $time),
                        'ticket_create_time'=> date('Y-m-d H:i:s', $ticket->create_time),
                    ],
                ],
            ]);

            // 2、管理员回复，向下游推送
            IdcsmartTicketLogic::pushTicketReplyCreate($ticket,$ticketReply,$param);
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['ticket_reply_id'=>$ticketReply->id]];
    }

    /**
     * 时间 2022-06-21
     * @title 催单
     * @desc 催单
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     */
    public function urgeTicket($param)
    {
        $this->startTrans();

        try{
            $time = time();
            $clientId = get_client_id();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticket = $this->where('id',$id)->where('client_id',$clientId)->find();
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            if (!$this->isAdmin){
                if (!(isset($param['is_downstream']) && $param['is_downstream']==1)){
                    if (!IdcsmartTicketLogic::checkDownstreamTicket($id)){
                        throw new \Exception(lang_plugins('ticket_downstream_cannot_operate'));
                    }
                }
            }

            $lastUrgeTime = cache('ticket_urge_time_limit_'.$id);

            if ($lastUrgeTime && ($lastUrgeTime+15*60)>$time){
                throw new \Exception(lang_plugins('ticket_urge_time_limit_15_m'));
            }

            if ($ticket->status == 1){
                $ticket->save([
                    'post_time' => $time,
                    'update_time' => $time
                ]);
                cache('ticket_urge_time_limit_'.$id,$time);
            }elseif (in_array($ticket->status,[2,3])){
                # 发送站内通知

                cache('ticket_urge_time_limit_'.$id,$time);
            }else{ # 已解决或已关闭不可催单
                throw new \Exception(lang_plugins('ticket_status_is_not_allowed_urge'));
            }

            active_log(lang_plugins('ticket_log_client_urge_ticket', ['{client}'=>'client#'.get_client_id() .'#' .request()->client_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        $IdcsmartTicketDeliveryModel = new IdcsmartTicketDeliveryModel();
        $IdcsmartTicketDeliveryModel->deliveryUrge([
            'ticket_id' => $id,
        ]);

        return ['status'=>200,'msg'=>lang_plugins('ticket_urge_success')];
    }

    /**
     * 时间 2022-06-21
     * @title 关闭工单
     * @desc 关闭工单
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     */
    public function closeTicket($param)
    {
        $this->startTrans();

        try{
            $time = time();
            $clientId = get_client_id();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticket = $this->where('id',$id)->where('client_id',$clientId)->find();
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            if (!$this->isAdmin && class_exists('server\idcsmart_common_finance\model\IdcsmartCommonSonHost')){
                $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();
                $ticketType = $IdcsmartTicketTypeModel->where('id',$ticket['ticket_type_id'])->find();
                if (!empty($ticketType) && $ticketType['name']=='开发者工单'){
                    throw new \Exception(lang_plugins('ticket_cannot_close'));
                }
            }

            if (!$this->isAdmin){
                if (!(isset($param['is_downstream']) && $param['is_downstream'])){
                    if (!IdcsmartTicketLogic::checkDownstreamTicket($id)){
                        throw new \Exception(lang_plugins('ticket_downstream_cannot_operate'));
                    }
                }
            }

            if ($ticket->status == 4){
                throw new \Exception(lang_plugins('ticket_is_closed'));
            }

            $data = [
                'status' => 4,
                'update_time' => $time
            ];

            $ticket->save($data);

            $IdcsmartTicketReplyModel = new IdcsmartTicketReplyModel();

            active_log(lang_plugins('ticket_log_client_close_ticket', ['{client}'=>'client#'.get_client_id() .'#' .request()->client_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);
			
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        system_notice([
            'name' => 'client_close_ticket',
            'email_description' => lang_plugins('client_close_ticket_send_mail'),
            'sms_description' => lang_plugins('client_close_ticket_send_sms'),
            'task_data' => [
                'client_id'=>$clientId,//客户ID
                'template_param'=>[
                    'subject' => $ticket['title'],//工单名称
                    'ticket_num'    => $ticket->ticket_num,
                    'admin_last_reply_time' => date('Y-m-d H:i:s', $IdcsmartTicketReplyModel->getAdminLastReplyTime($ticket->id) ?: $ticket->create_time),
                    'ticket_create_time' => date('Y-m-d H:i:s', $ticket->create_time),
                ],
            ],
        ]);

        // wyh 20240619 最下游前台用户正常关闭工单，非终止传递，注意和上面区分
        $IdcsmartTicketDeliveryModel = new IdcsmartTicketDeliveryModel();
        $IdcsmartTicketDeliveryModel->deliveryClose([
            'ticket_id' => $param['id'],
        ]);

        return ['status'=>200,'msg'=>lang_plugins('ticket_close_success')];
    }

    /**
     * 时间 2022-06-21
     * @title 工单终止传递
     * @desc 工单终止传递
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     */
    public function terminateTicket($param)
    {
        $this->startTrans();

        try{
            $time = time();
            $clientId = get_client_id();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticket = $this->where('id',$id)->where('client_id',$clientId)->find();
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            if (!$this->isAdmin){
                // is_downstream_other
                if (!(isset($param['is_downstream']) && $param['is_downstream'])){
                    if (!IdcsmartTicketLogic::checkDownstreamTicket($id)){
                        throw new \Exception(lang_plugins('ticket_downstream_cannot_operate'));
                    }
                }
            }

            if ($ticket->status == 4){
                throw new \Exception(lang_plugins('ticket_is_closed'));
            }

            $data = [
                'status' => 4,
                'update_time' => $time
            ];

            // 下游工单已关闭传递
            if (isset($param['is_downstream']) && $param['is_downstream']){
                $data['downstream_delivery_status'] = 0;
            }

            $ticket->save($data);

            $IdcsmartTicketReplyModel = new IdcsmartTicketReplyModel();

            active_log(lang_plugins('ticket_log_client_close_ticket', ['{client}'=>'client#'.get_client_id() .'#' .request()->client_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);
            
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        system_notice([
            'name' => 'client_close_ticket',
            'email_description' => lang_plugins('client_close_ticket_send_mail'),
            'sms_description' => lang_plugins('client_close_ticket_send_sms'),
            'task_data' => [
                'client_id'=>$clientId,//客户ID
                'template_param'=>[
                    'subject' => $ticket['title'],//工单名称
                    'ticket_num'    => $ticket->ticket_num,
                    'admin_last_reply_time' => date('Y-m-d H:i:s', $IdcsmartTicketReplyModel->getAdminLastReplyTime($ticket->id) ?: $ticket->create_time),
                    'ticket_create_time' => date('Y-m-d H:i:s', $ticket->create_time),
                ],
            ],
        ]);

        // wyh 20240618 最下游终止传递，继续向上游发起关闭
//        $IdcsmartTicketUpstreamModel = new IdcsmartTicketUpstreamModel();
//        $ticketUpstream = $IdcsmartTicketUpstreamModel->where('ticket_id',$param['id'])->find();
//        if (!empty($ticketUpstream)){
//            $UpstreamHostModel = new UpstreamHostModel();
//            $upstreamHost = $UpstreamHostModel->where('host_id',$ticketUpstream['host_id'])->find();
//            if (!empty($upstreamHost)){
//                $result = idcsmart_api_curl($upstreamHost['supplier_id'],"console/v1/ticket/{$ticketUpstream['upstream_ticket_id']}/terminate",[
//                    'is_downstream' => 1,
//                ],30,'PUT','json');
//                if ($result['status']==200){
//                    $ticketUpstream->save([
//                        'delivery_status' => 0,
//                        'update_time' => time()
//                    ]);
//                }
//            }
//        }

        return ['status'=>200,'msg'=>lang_plugins('ticket_close_success')];
    }

    /**
     * 时间 2022-06-22
     * @title 接收工单
     * @desc 接收工单
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     */
    public function receiveTicket($param)
    {
        $this->startTrans();

        try{
            $time = time();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticket = $this->where('id',$id)->find();
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            // wyh 20240619 新增：对于后台，最上游才可以回复
            if (!IdcsmartTicketLogic::checkUpstreamTicket($id)){
                throw new \Exception(lang_plugins('ticket_upstream_cannot_operate'));
            }

            if ($ticket->status != 1){
                throw new \Exception(lang_plugins('ticket_is_pending_can_handling'));
            }

            $ticket->save([
                'status' => 2,
                'update_time' => $time
            ]);

            active_log(lang_plugins('ticket_log_admin_receive_ticket', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        // 推送工单状态至下游
        $param['status'] = 2;
        $IdcsmartTicketStatusModel = new IdcsmartTicketStatusModel();
        $ticketStatus = $IdcsmartTicketStatusModel->find($param['status']);
        IdcsmartTicketLogic::pushTicketStatus($ticket,$ticketStatus,$param);

        return ['status'=>200,'msg'=>lang_plugins('ticket_handle_success')];
    }

    /**
     * 时间 2022-06-22
     * @title 已解决工单
     * @desc 已解决工单
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     */
    public function resolvedTicket($param)
    {
        $this->startTrans();

        try{
            $time = time();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticket = $this->where('id',$id)->find();
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            // wyh 20240619 新增：对于后台，最上游才可以回复
            if (!IdcsmartTicketLogic::checkUpstreamTicket($id)){
                throw new \Exception(lang_plugins('ticket_upstream_cannot_operate'));
            }

            if ($ticket->status == 1){
                throw new \Exception(lang_plugins('ticket_is_pending_cannot_resolved'));
            }

            if ($ticket->status == 4){
                throw new \Exception(lang_plugins('cannot_repeat_opreate'));
            }

            $ticket->save([
                'status' => 4,
                'update_time' => $time
            ]);

            active_log(lang_plugins('ticket_log_admin_resolved_ticket', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        // 推送工单状态至下游
        $param['status'] = 4;
        $IdcsmartTicketStatusModel = new IdcsmartTicketStatusModel();
        $ticketStatus = $IdcsmartTicketStatusModel->find($param['status']);
        IdcsmartTicketLogic::pushTicketStatus($ticket,$ticketStatus,$param);

        return ['status'=>200,'msg'=>lang_plugins('ticket_resolved_success')];
    }

    /**
     * 时间 2022-07-22
     * @title 工单附件下载
     * @desc 工单附件下载
     * @author wyh
     * @version v1
     * @param string name - 附件名称 required
     */
    public function download($param)
    {
        if (!isset($param['name']) || empty($param['name'])){
            return json(['status'=>400,'msg'=>lang_plugins('ticket_attachment_name_require')]);
        }
        $filename = $param['name'];

        $address = IdcsmartTicketLogic::getDefaultConfig('ticket_upload');

        $file = $address . $filename;
        if (!file_exists($file)){
            return json(['status'=>400,'msg'=>lang_plugins('ticket_attachment_is_not_exist')]);
        }

        $orginName = explode('^',$filename)[1]?explode('^',$filename)[1]:$filename;

        return download($file,$orginName);
    }

    /**
     * 时间 2022-09-21
     * @title 转内部工单
     * @desc 转内部工单
     * @author wyh
     * @version v1
     * @param int ticket_id - 工单ID(转内部工单时需要传此参数)
     * @param string title - 内部工单标题 required
     * @param int ticket_type_id - 内部工单类型ID(调admin/v1/ticket/internal/type获取列表) required
     * @param string priority - 紧急程度:medium一般,high紧急 required
     * @param int client_id - 关联用户
     * @param int admin_role_id - 指定部门 required
     * @param int admin_id - 管理员ID
     * @param array host_ids - 关联产品ID,数组(/admin/v1/host?client_id= 获取所选客户的产品列表,取产品ID)
     * @param string content - 问题描述
     * @param array attachment - 附件,数组(后台调admin/v1/upload(前台调console/v1/upload)上传文件,取返回值save_name)
     */
    public function convert($param)
    {
        $ticket = $this->find($param['ticket_id']);

        $ticketAttachments = explode(',',$ticket->attachment??"")[0]?explode(',',$ticket->attachment):[];

        $param['attachment_old'] = $ticketAttachments;

        $IdcsmartTicketLogic = new IdcsmartTicketLogic();

        $param['ticket_upload'] = $IdcsmartTicketLogic->getDefaultConfig('ticket_upload');

        $result = plugin_api('idcsmart_ticket_internal','TicketInternal','create',$param);

        return $result;
    }

    /**
     * 时间 2022-06-21
     * @title 内部工单类型列表
     * @desc 内部工单类型列表
     * @author wyh
     * @version v1
     * @return array list - 工单类型列表
     * @return int list[].id - 工单类型ID
     * @return int list[].name - 工单类型名称
     * @return int list[].role_name - 默认接受部门
     */
    public function ticketInternalType()
    {
        $result = plugin_api('idcsmart_ticket_internal','TicketInternalType','ticketTypeList',[]);

        return $result;
    }

    /**
     * 时间 2022-09-23
     * @title 转交工单
     * @desc 转交工单
     * @author wyh
     * @version v1
     * @param int admin_id - 管理员ID required
     * @param int notes - 备注 required
     * @param int ticket_type_id - 部门ID required
     */
    public function forward($param)
    {
        $this->startTrans();

        try{
            $id = $param['id']??'';

            $ticket = $this->find($id);
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }
            $adminId = IdcsmartTicketTypeAdminLinkModel::where('ticket_type_id', $param['ticket_type_id'])->column('admin_id');
            if(!in_array($param['admin_id'], $adminId)){
                throw new \Exception(lang_plugins('ticket_admin_is_not_exist'));
            }
            $ticket->save([
                'admin_id' => $param['admin_id'],
                'notes' => $param['notes']??'',
                'ticket_type_id' => $param['ticket_type_id'],
                'update_time' => time()
            ]);

            $IdcsmartTicketForwardModel = new IdcsmartTicketForwardModel();
            $IdcsmartTicketForwardModel->insert([
                'ticket_id' => $id,
                'admin_id' => get_admin_id(),
                'forward_admin_id' =>  $param['admin_id'],
                'ticket_type_id' =>  $param['ticket_type_id'],
                'notes' => $param['notes']??'',
                'create_time' => time()
            ]);

            $IdcsmartTicketTypeModel = IdcsmartTicketTypeModel::find($param['ticket_type_id']);

            active_log(lang_plugins('ticket_log_admin_ticket_forward', ['{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#','{admin}'=>request()->admin_name,'{admin_role}'=>$IdcsmartTicketTypeModel['name']]), 'addon_idcsmart_ticket', $ticket->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-09-23
     * @title 修改工单状态
     * @desc 修改工单状态
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     * @param int status - 状态ID required
     * @param int ticket_type_id - 工单类型ID
     * @param array host_ids - 产品ID,数组
     */
    public function status($param)
    {
        $this->startTrans();

        try{
            $id = $param['id']??'';

            $ticket = $this->find($id);
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            $IdcsmartTicketStatusModel = new IdcsmartTicketStatusModel();
            $ticketStatus = $IdcsmartTicketStatusModel->where('id',$param['status']??0)->find();
            if (empty($ticketStatus)){
                throw new \Exception(lang_plugins('ticket_status_is_not_exist'));
            }

            $oldStatus = $ticket['status'];

            $IdcsmartTicketHostLinkModel = new IdcsmartTicketHostLinkModel();

            if ($this->isAdmin){
                $hostIdsInDb = $IdcsmartTicketHostLinkModel->where('ticket_id',$id)->column('host_id');
                if (($oldStatus!=$param['status'] || count(array_diff($hostIdsInDb,$param['host_ids']))!=0
                    || count(array_diff($param['host_ids'],$hostIdsInDb))!=0) && !IdcsmartTicketLogic::checkUpstreamTicket($id)){
                    throw new \Exception(lang_plugins('ticket_upstream_cannot_operate'));
                }
                // 更改状态接口特殊判断
                $IdcsmartTicketUpstreamModel = new IdcsmartTicketUpstreamModel();
                $ticketUpstream = $IdcsmartTicketUpstreamModel->where('ticket_id',$id)->find();
                if ((count(array_diff($hostIdsInDb,$param['host_ids']))!=0
                    || count(array_diff($param['host_ids'],$hostIdsInDb))!=0) && !empty($ticketUpstream) && $ticketUpstream['delivery_status']==0){
                    throw new \Exception(lang_plugins('ticket_upstream_cannot_operate'));
                }
            }

            $oldType = $ticket['ticket_type_id'];

            $ticket->save([
                'status' => $param['status'],
                'ticket_type_id' => $param['ticket_type_id']??0,
                'update_time' => time()
            ]);

            $IdcsmartTicketHostLinkModel->where('ticket_id',$id)->delete();
            $hostIds = $param['host_ids']?:[];
            $insert = [];
            foreach ($hostIds as $item){
                $insert[] = [
                    'ticket_id' => $ticket->id,
                    'host_id' => $item
                ];
            }
            $IdcsmartTicketHostLinkModel->insertAll($insert);

            if ($oldStatus!=$param['status']){
                active_log(lang_plugins('ticket_log_admin_update_ticket_status', ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{ticket}'=>'ticket#'.$ticket['ticket_num'],'{status}'=>$ticketStatus['name']]), 'addon_idcsmart_ticket', $id);
            }

            if ($oldType!=$param['ticket_type_id']){
                $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();
                $ticketType = $IdcsmartTicketTypeModel->where('id',$param['ticket_type_id'])->find();
                active_log(lang_plugins('ticket_log_admin_update_ticket_type', ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{ticket}'=>'ticket#'.$ticket['ticket_num'],'{type}'=>$ticketType['name']]), 'addon_idcsmart_ticket', $id);
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        // 推送工单状态至下游
        IdcsmartTicketLogic::pushTicketStatus($ticket,$ticketStatus,$param);

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-09-23
     * @title 工单日志
     * @desc 工单日志
     * @author wyh
     * @version v1
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list -
     * @return int list[].id - ID
     * @return int list[].create_time - 记录时间
     * @return int list[].description - 描述
     */
    public function ticketLog($param)
    {
        $ticketId = $param['id']??0;
        $SystemLogModel = new SystemLogModel();
        $list = $SystemLogModel->field('id,description,create_time')
            ->where('type','addon_idcsmart_ticket')
            ->where('rel_id',$ticketId)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order('id','desc')
            ->select()->toArray();
        $count = $SystemLogModel->field('id,description,create_time')
            ->where('type','addon_idcsmart_ticket')
            ->where('rel_id',$ticketId)
            ->count();
        return [
            'status' => 200,
            'msg' =>lang_plugins('success_message'),
            'data' => [
                'list' => $list,
                'count' => $count
            ]
        ];
    }

    /**
     * 时间 2022-09-23
     * @title 修改工单内容
     * @desc 修改工单内容
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     * @param int content - 内容 required
     */
    public function updateContent($param)
    {
        $this->startTrans();

        try{
            $id = $param['id']??'';

            $ticket = $this->find($id);
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            $ticket->save([
                'content' => htmlspecialchars($param['content']),
                'update_time' => time()
            ]);

            active_log(lang_plugins('ticket_log_admin_update_ticket_content', ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{content}'=>$param['content']]), 'addon_idcsmart_ticket', $id);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    //
    public function afterAdminDelete($param){
        IdcsmartTicketTypeAdminLinkModel::where('admin_id', $param['id']??0)->delete();
    }

    /**
     * 时间 2024-01-22
     * @title 工单通知设置
     * @desc 工单通知设置
     * @author wyh
     * @version v1
     * @return int ticket_notice_open - 是否开启工单通知，1是默认，0否
     * @return string ticket_notice_description - 工单通知描述
     */
    public function ticketConfig(){
        $config = IdcsmartTicketLogic::getDefaultConfig();
        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'ticket_notice_open' => $config['ticket_notice_open']??1,
                'ticket_notice_description' => !empty($config['ticket_notice_description'])?htmlspecialchars_decode($config['ticket_notice_description']):"",
            ]
        ];
    }

    /**
     * 时间 2024-06-18
     * @title 手动发起传递
     * @desc 手动发起传递
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     */
    public function manualDelivery($param)
    {
        $ticket = $this->find($param['id']??0);
        if (empty($ticket)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_is_not_exist')];
        }

        $IdcsmartTicketHostLinkModel = new IdcsmartTicketHostLinkModel();

        $hostId = $IdcsmartTicketHostLinkModel->where('ticket_id',$param['id'])->value('host_id');

        $IdcsmartTicketDeliveryModel = new IdcsmartTicketDeliveryModel();

        $IdcsmartTicketDeliveryModel->isAdmin = $this->isAdmin;

        // 1、传递工单
        $result = $IdcsmartTicketDeliveryModel->delivery([
            'host_id' => $hostId,
            'ticket_id' => $param['id']
        ]);

        if ($result['status']==200){
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id',$hostId)->find();
            $ticketUpstream[] = [
                'host_id' => $hostId,
                'upstream_host_id' => $upstreamHost['upstream_host_id']??0,
                'ticket_id' => $ticket->id,
                'upstream_ticket_id' => $result['data']['id'],
                'create_time' => time(),
                'delivery_status' => 1,
            ];
            $IdcsmartTicketUpstreamModel = new IdcsmartTicketUpstreamModel();
            if (!empty($ticketUpstream)){
                $IdcsmartTicketUpstreamModel->insertAll($ticketUpstream);
            }
        }

        // 2、传递工单的客户回复
        $IdcsmartTicketReplyModel = new IdcsmartTicketReplyModel();
        $ticketReplies = $IdcsmartTicketReplyModel->where('ticket_id',$param['id'])
            ->where('type','Client')
            ->select();
        foreach ($ticketReplies as $ticketReply){
            $result = $IdcsmartTicketDeliveryModel->deliveryReply([
                'ticket_id' => $param['id'],
                'ticket_reply_id' => $ticketReply->id
            ]);
            if ($result['status']==200){
                $ticketReply->save([
                    'upstream_ticket_reply_id' => $result['data']['ticket_reply_id'],
                    'update_time' => time()
                ]);
            }
        }

        // 3、传递工单的管理员回复
        /*$IdcsmartTicketReplyModel = new IdcsmartTicketReplyModel();
        $ticketAdminReplies = $IdcsmartTicketReplyModel->where('ticket_id',$param['id'])
            ->where('type','Admin')
            ->select();
        foreach ($ticketAdminReplies as $ticketAdminReply){

        }*/

        return $result;
    }

    /**
     * 时间 2024-06-18
     * @title 终止传递
     * @desc 终止传递
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     */
    public function terminateDelivery($param)
    {
        $ticket = $this->find($param['id']??0);
        if (empty($ticket)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_is_not_exist')];
        }

        $IdcsmartTicketUpstreamModel = new IdcsmartTicketUpstreamModel();
        $ticketUpstream = $IdcsmartTicketUpstreamModel->where('ticket_id',$param['id'])->find();

        if (empty($ticketUpstream)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_has_not_deliveried')];
        }

        if ($ticketUpstream['delivery_status']==0){
            return ['status'=>400,'msg'=>lang_plugins('ticket_delivery_status_0')];
        }

        $UpstreamHostModel = new UpstreamHostModel();
        $upstreamHost = $UpstreamHostModel->where('host_id',$ticketUpstream['host_id'])->find();
        if (empty($upstreamHost)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_upstream_host_is_not_exist')];
        }

        $result = idcsmart_api_curl($upstreamHost['supplier_id'],"console/v1/ticket/{$ticketUpstream['upstream_ticket_id']}/terminate",[
            'is_downstream' => 1,
            ],30,'PUT','json');


        $SupplierModel = new SupplierModel();
        $supplier = $SupplierModel->find($upstreamHost['supplier_id']);
        if ($result['status']==200){
            active_log(lang_plugins('log_ticket_delivery_terminate_success',['{admin}'=>request()->admin_name,'{host_id}'=>$ticketUpstream['host_id'],'{ticket_id}'=>$param['id'],'{upstream}'=>$supplier['name']]), 'addon_idcsmart_ticket', $param['id']);
            $ticketUpstream->save([
                'delivery_status' => 0,
                'update_time' => time()
            ]);
        }else{
            active_log(lang_plugins('log_ticket_delivery_terminate_success',['{admin}'=>request()->admin_name,'{host_id}'=>$ticketUpstream['host_id'],'{ticket_id}'=>$param['id'],'{upstream}'=>$supplier['name'],'{reason}'=>$result['msg']??""]), 'addon_idcsmart_ticket', $param['id']);
        }

        return $result;
    }

    /**
     * 时间 2024-06-18
     * @title 启动传递
     * @desc 启动传递
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     */
    public function activeDelivery($param)
    {
        $ticket = $this->find($param['id']??0);
        if (empty($ticket)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_is_not_exist')];
        }

        $IdcsmartTicketUpstreamModel = new IdcsmartTicketUpstreamModel();
        $ticketUpstream = $IdcsmartTicketUpstreamModel->where('ticket_id',$param['id'])->find();

        if (empty($ticketUpstream)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_has_not_deliveried')];
        }

        if ($ticketUpstream['delivery_status']==1){
            return ['status'=>400,'msg'=>lang_plugins('ticket_delivery_status_1')];
        }

        $UpstreamHostModel = new UpstreamHostModel();
        $upstreamHost = $UpstreamHostModel->where('host_id',$ticketUpstream['host_id'])->find();
        if (empty($upstreamHost)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_upstream_host_is_not_exist')];
        }

        $result = idcsmart_api_curl($upstreamHost['supplier_id'],"console/v1/ticket/{$ticketUpstream['upstream_ticket_id']}/processing",[
            'is_downstream' => 1,
        ],30,'PUT','json');


        $SupplierModel = new SupplierModel();
        $supplier = $SupplierModel->find($upstreamHost['supplier_id']);
        if ($result['status']==200){
            active_log(lang_plugins('log_ticket_delivery_active_success',['{admin}'=>request()->admin_name,'{host_id}'=>$ticketUpstream['host_id'],'{ticket_id}'=>$param['id'],'{upstream}'=>$supplier['name']]), 'addon_idcsmart_ticket', $param['id']);
            $ticketUpstream->save([
                'delivery_status' => 1,
                'update_time' => time()
            ]);
        }else{
            active_log(lang_plugins('log_ticket_delivery_active_fail',['{admin}'=>request()->admin_name,'{host_id}'=>$ticketUpstream['host_id'],'{ticket_id}'=>$param['id'],'{upstream}'=>$supplier['name'],'{reason}'=>$result['msg']??""]), 'addon_idcsmart_ticket', $param['id']);
        }

        return $result;
    }

    /**
     * 时间 2024-06-18
     * @title 工单处理中
     * @desc 工单处理中
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     */
    public function processing($param)
    {
        $this->startTrans();

        try{
            $time = time();
            $clientId = get_client_id();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticket = $this->where('id',$id)->where('client_id',$clientId)->find();
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            if ($ticket->status == 5){
                throw new \Exception(lang_plugins('ticket_is_processing'));
            }

            $data = [
                'status' => 5,
                'update_time' => $time
            ];

            // 下游工单已开启传递
            if (isset($param['is_downstream']) && $param['is_downstream']){
                $data['downstream_delivery_status'] = 1;
            }

            $ticket->save($data);

            active_log(lang_plugins('ticket_log_client_processing_ticket', ['{client}'=>'client#'.get_client_id() .'#' .request()->client_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        // wyh 20240618 最下游启动传递，继续向上游发起启动
//        if (isset($param['is_downstream']) && $param['is_downstream']){
//            $IdcsmartTicketUpstreamModel = new IdcsmartTicketUpstreamModel();
//            $ticketUpstream = $IdcsmartTicketUpstreamModel->where('ticket_id',$param['id'])->find();
//            if (!empty($ticketUpstream)){
//                $UpstreamHostModel = new UpstreamHostModel();
//                $upstreamHost = $UpstreamHostModel->where('host_id',$ticketUpstream['host_id'])->find();
//                if (!empty($upstreamHost)){
//                    $result = idcsmart_api_curl($upstreamHost['supplier_id'],"console/v1/ticket/{$ticketUpstream['upstream_ticket_id']}/processing",[
//                        'is_downstream' => 1,
//                    ],30,'PUT','json');
//                    if ($result['status']==200){
//                        $ticketUpstream->save([
//                            'delivery_status' => 1,
//                            'update_time' => $time
//                        ]);
//                    }
//                }
//            }
//        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    public function updateStatus($param)
    {
        $this->startTrans();

        try{
            $id = $param['id']??0;

            $ticket = $this->find($id);

            $IdcsmartTicketStatusModel = new IdcsmartTicketStatusModel();
            $ticketStatus = $IdcsmartTicketStatusModel->find($param['status']??0);
            $IdcsmartTicketUpstreamModel = new IdcsmartTicketUpstreamModel();
            $ticketUpstream = $IdcsmartTicketUpstreamModel->where('ticket_id',$id)->find();
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id',$ticketUpstream['host_id']??0)->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($upstreamHost['supplier_id']??0);

            if (!isset($param['status']) || !in_array($param['status'],[1,2,3,4,5])){
                throw new \Exception(lang_plugins('ticket_push_status_error').'_'.($param['status']??''));
            }

            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }
            // 验签
            $param['token'] = $ticket['token'];
            
            if (!IdcsmartTicketLogic::validateSign($param)){
                throw new \Exception(lang_plugins('ticket_push_token_error'));
            }

            $ticket->save([
                'status' => $param['status'],
                'update_time' => time()
            ]);

            active_log(lang_plugins('log_ticket_push_status_to_local_success',['{upstream}'=>$supplier['name']??"",'{ticket}'=>$ticket['ticket_num']??"",'{status}'=>$ticketStatus['name']??""]),'addon_idcsmart_ticket',$id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            active_log(lang_plugins('log_ticket_push_status_to_local_fail',['{upstream}'=>$supplier['name']??"",'{ticket}'=>$ticket['ticket_num']??"",'{status}'=>$ticketStatus['name']??"",'{reason}'=>$e->getMessage()]),'addon_idcsmart_ticket',$id);
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        // 推送工单状态至下游
        IdcsmartTicketLogic::pushTicketStatus($ticket,$ticketStatus,$param);

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    public function updateReply($param)
    {
        $this->startTrans();

        try{
            $ticket = $this->find($param['id']);
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            $IdcsmartTicketUpstreamModel = new IdcsmartTicketUpstreamModel();
            $ticketUpstream = $IdcsmartTicketUpstreamModel->where('ticket_id',$param['id'])->find();
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id',$ticketUpstream['host_id']??0)->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($upstreamHost['supplier_id']??0);

            $IdcsmartTicketReplyModel = new IdcsmartTicketReplyModel();
            $ticketReply = $IdcsmartTicketReplyModel->where('id',$param['ticket_reply_id'])->find();
            if (empty($ticketReply)){
                throw new \Exception(lang_plugins('ticket_reply_is_not_exist'));
            }

            // 验签
            $param['token'] = $ticket['token'];
            if (!IdcsmartTicketLogic::validateSign($param)){
                throw new \Exception(lang_plugins('ticket_push_token_error'));
            }

            $ticketReply->save([
                'content'=>$param['content']??'',
                'update_time'=>time()
            ]);

            active_log(lang_plugins('log_ticket_push_reply_to_local_success',
                ['{upstream}'=>$supplier['name']??"",'{ticket}'=>$ticket['ticket_num']??"",'{ticket_reply_id}'=>$ticketReply['id']??""]),
                'addon_idcsmart_ticket',$ticket['id']);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            active_log(lang_plugins('log_ticket_push_reply_to_local_fail',
                ['{upstream}'=>$supplier['name']??"", '{ticket}'=>$ticket['ticket_num']??"",'{ticket_reply_id}'=>$ticketReply['id']??"",'{reason}'=>$e->getMessage()]),
                'addon_idcsmart_ticket',$ticket['id']);
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        IdcsmartTicketLogic::pushTicketReply($ticket,$ticketReply,$param);

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    public function deleteReply($param)
    {
        $this->startTrans();

        try{
            $ticket = $this->find($param['id']);
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            $IdcsmartTicketUpstreamModel = new IdcsmartTicketUpstreamModel();
            $ticketUpstream = $IdcsmartTicketUpstreamModel->where('ticket_id',$param['id'])->find();
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id',$ticketUpstream['host_id']??0)->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($upstreamHost['supplier_id']??0);

            $IdcsmartTicketReplyModel = new IdcsmartTicketReplyModel();
            $ticketReply = $IdcsmartTicketReplyModel->where('id',$param['ticket_reply_id'])->find();
            if (empty($ticketReply)){
                throw new \Exception(lang_plugins('ticket_reply_is_not_exist'));
            }

            // 验签
            $param['token'] = $ticket['token'];
            if (!IdcsmartTicketLogic::validateSign($param)){
                throw new \Exception(lang_plugins('ticket_push_token_error'));
            }

            $ticketReply->delete();

            active_log(lang_plugins('log_ticket_push_reply_delete_to_local_success',
                ['{upstream}'=>$supplier['name']??"",'{ticket}'=>$ticket['ticket_num']??"",'{ticket_reply_id}'=>$ticketReply['id']??""]),
                'addon_idcsmart_ticket',$ticket['id']);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            active_log(lang_plugins('log_ticket_push_reply_delete_to_local_fail',
                ['{upstream}'=>$supplier['name']??"", '{ticket}'=>$ticket['ticket_num']??"",'{ticket_reply_id}'=>$ticketReply['id']??"",'{reason}'=>$e->getMessage()]),
                'addon_idcsmart_ticket',$ticket['id']);
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        IdcsmartTicketLogic::pushTicketReplyDelete($ticket,$ticketReply,$param);

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    public function createReply($param)
    {
        $this->startTrans();

        try{
            $ticket = $this->find($param['id']);
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            $IdcsmartTicketUpstreamModel = new IdcsmartTicketUpstreamModel();
            $ticketUpstream = $IdcsmartTicketUpstreamModel->where('ticket_id',$param['id'])->find();
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id',$ticketUpstream['host_id']??0)->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($upstreamHost['supplier_id']??0);

            // 验签
            $param['token'] = $ticket['token'];
            if (!IdcsmartTicketLogic::validateSign($param)){
                throw new \Exception(lang_plugins('ticket_push_token_error'));
            }

            $IdcsmartTicketReplyModel = new IdcsmartTicketReplyModel();
            // 默认超级管理员1
            $data = [
                'ticket_id' => $param['id'],
                'type' => 'Admin',
                'rel_id' => 1,
                'content' => htmlspecialchars($param['content']),
                'attachment' => (isset($param['attachment']) && !empty($param['attachment']))?implode(',',$param['attachment']):'',
                'create_time' => time(),
                'upstream_ticket_reply_id' => $param['upstream_ticket_reply_id']??0,
            ];

            $ticketReply = $IdcsmartTicketReplyModel->create($data);
            // 更改工单回复状态，默认超级管理员1
            $update = [
                'last_reply_time' => time(),
                'status' => 3
            ];
            $update['last_reply_admin_id'] = 1;
            $ticket->save($update);

            active_log(lang_plugins('log_ticket_push_reply_create_to_local_success',
                ['{upstream}'=>$supplier['name']??"",'{ticket}'=>$ticket['ticket_num']??"",'{ticket_reply_id}'=>$ticketReply['id']??""]),
                'addon_idcsmart_ticket',$ticket['id']);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            active_log(lang_plugins('log_ticket_push_reply_create_to_local_fail',
                ['{upstream}'=>$supplier['name']??"", '{ticket}'=>$ticket['ticket_num']??"",'{ticket_reply_id}'=>$ticketReply['id']??"",'{reason}'=>$e->getMessage()]),
                'addon_idcsmart_ticket',$ticket['id']);
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        IdcsmartTicketLogic::pushTicketReplyCreate($ticket,$ticketReply,$param);

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['ticket_reply_id'=>$ticketReply['id']]];
    }

    // 实现每五分钟执行一次的定时任务
    public function fiveMinuteCron($param)
    {
        $config = IdcsmartTicketLogic::getDefaultConfig();

        if (isset($config['ticket_close_switch']) && $config['ticket_close_switch']){
            // 已回复状态
            $status = 3;
            // 关闭时间
            $ticketCloseHour = ($config['ticket_close_hour']??0) * 60 * 60;
            $tickets = $this->field('id')
                ->where('status',$status)
                ->where('last_reply_time','<=',time()-$ticketCloseHour)
                ->select()
                ->toArray();
            foreach ($tickets as $ticket){
                // 如果是多级传递的， 定时任务也要判断当前工单是否可以操作关闭工单！
                $this->resolvedTicket([
                    'id' => $ticket['id']
                ]);
            }

        }

        return true;
    }

    public function appendSendParam($param): array
    {
        $result = [
            'label' => lang_plugins('ticket_label_about'),
            'param' => [
                [
                    'label' => lang_plugins('ticket_subject_send_param'),
                    'value' => 'subject',
                ],
                [
                    'label' => lang_plugins('ticket_num_send_param'),
                    'value' => 'ticket_num',
                ],
                [
                    'label' => lang_plugins('ticket_create_time_send_param'),
                    'value' => 'ticket_create_time',
                ],
            ],
        ];
        return $result;
    }

}
