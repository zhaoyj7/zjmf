<?php
namespace app\admin\model;

use think\db\Query;
use think\Model;

/**
 * @title 消息通知模型
 * @desc 消息通知模型
 * @use app\admin\model\NoticeModel
 */
class NoticeModel extends Model
{
    protected $name = 'notice';

    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'title'           => 'string',
        'content'         => 'string',
        'attachment'      => 'string',
        'accept_time'     => 'int',
        'read'            => 'int',
        'priority'        => 'int',
        'type'            => 'string',
        'rel_id'          => 'int',
        'create_time'     => 'int',
        'update_time'     => 'int',
        'is_delete'       => 'int',
        'delete_time'     => 'int',
    ];

    public function sync()
    {
        $this->startTrans();
        try {
            $data = [
                'auth_code' => AUTHCODE,
                'version' => configuration('system_version'),
                'license' => configuration('system_license'),
            ];
            $result = curl('https://my.idcsmart.com/console/v1/idcsmart_notice/notice', $data,30,'GET');
            if ($result['http_code']==200){
                $content = json_decode($result['content'],true);
                $list = $content['data']['list']??[];

                $apps = PluginModel::column('name');

                // 保存数据至本地
                $NoticeModel = new NoticeModel();
                foreach ($list as $item){
                    if(isset($item['apps']) && !empty($item['apps'])){
                        if(empty(array_intersect($apps, $item['apps']))){
                            continue;
                        }
                    }

                    $exist = $NoticeModel->where('type','idcsmart')
                        ->where('rel_id',$item['id'])
                        ->find();
                    // 只插入不存在的
                    if (empty($exist)){
                        $NoticeModel->insert([
                            'title' => $item['title'],
                            'content' => $item['content'],
                            'attachment' => $item['attachment'],
                            'accept_time' => time(),
                            'read' => 0,
                            'priority' => $item['priority'],
                            'type' => 'idcsmart',
                            'rel_id' => $item['id'],
                            'create_time' => time()
                        ]);
                    }
                }
                $this->commit();
            }else{
                throw new \Exception($result['error'] ?? '请求失败');
            }
        }catch (\Exception $e){
            $this->rollback();
            return [
                'status' => 400,
                'msg' => $e->getMessage()
            ];
        }
        return [
            'status' => 200,
            'msg' => lang('success_message'),
        ];
    }

    /**
     * 时间 2024-12-12
     * @title 通知列表
     * @desc 通知列表
     * @author wyh
     * @version v1
     * @param string keywords - 关键字搜索,搜索范围:标题，内容
     * @param int read - 是否已读：0未读消息，1已读消息
     * @param string type - 消息类型：idcsmart官方通知，system系统通知
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序字段，默认id
     * @param string sort - 排序(desc,asc)
     * @return object list - 通知列表
     * @return int list[].id - ID
     * @return string list[].title - 标题
     * @return string list[].content - 内容
     * @return string list[].attachment - 附件，逗号分隔
     * @return int list[].accept_time - 接收时间
     * @return int list[].read - 是否已读：1是，0否
     * @return string list[].type - 消息类型：idcsmart官方通知，system系统通知
     * @return int list[].rel_id - 关联ID，消息类型是idcsmart时，表示官方消息ID
     * @return int count - 总数
     * @return int total_count - 所有消息未读总数
     */
    public function noticeList($param)
    {
        if (!isset($param['orderby']) || !in_array($param['orderby'],['id'])){
            $param['orderby'] = 'n.id';
        }else{
            $param['orderby'] = 'n.'.$param['orderby'];
        }

        $where = function (Query $query) use ($param){
            if(!empty($param['keywords'])){
                $query->where('n.title|n.content','like','%'.$param['keywords'].'%');
            }
            if (!empty($param['type'])){
                $query->where('n.type',$param['type']);
            }
            if (isset($param['read']) && $param['read']!=''){
                $query->where('n.read',(int)$param['read']);
            }
            // 未删除
            $query->where('is_delete',0);
        };

        $list = $this->alias('n')
            ->where($where)
            ->order($param['orderby'].' '.$param['sort'])
            ->order('n.priority','desc')
            ->order('n.accept_time','desc')
            ->limit($param['limit'])
            ->page($param['page'])
            ->select()
            ->toArray();

        $count = $this->alias('n')
            ->where($where)
            ->count();

        $totalCount = $this->where('is_delete',0)->where('read',0)->count();

        return [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => [
                'list'  => $list,
                'count' => $count,
                'total_count' => $totalCount
            ]
        ];
    }

    /**
     * 时间 2024-12-12
     * @title 通知详情
     * @desc 通知详情
     * @author wyh
     * @version v1
     * @param int id - 通知ID
     * @return object notice - 通知列表
     * @return int notice.id - ID
     * @return string notice.title - 标题
     * @return string notice.content - 内容
     * @return string notice.attachment - 附件，逗号分隔
     * @return int notice.accept_time - 接收时间
     * @return int notice.read - 是否已读：1是，0否
     * @return string notice.type - 消息类型：idcsmart官方通知，system系统通知
     * @return int notice.rel_id - 关联ID，消息类型是idcsmart时，表示官方消息ID
     * @return int count - 总数
     * @return object before - 上一条
     * @return object next - 下一条
     */
    public function detail($param)
    {
        $notice = $this->where('id',$param['id'])
            ->where('is_delete',0)
            ->find();

        if (!empty($notice)){
            $notice->save([
                'read' => 1,
                'update_time' => time()
            ]);
            $type = $notice['type'];
        }

        $before = $this->where('id','>',$param['id'])
            ->where('is_delete',0)
            ->where('type',$type??'idcsmart')
            ->find();

        $next = $this->where('id','<',$param['id'])
            ->order('id','desc')
            ->where('is_delete',0)
            ->where('type',$type??'idcsmart')
            ->find();

        return [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => [
                'notice' => $notice,
                'before' => $before??(object)[],
                'next' => $next??(object)[]
            ]
        ];
    }

    /**
     * 时间 2024-12-12
     * @title 标记已读
     * @desc 标记已读
     * @author wyh
     * @version v1
     * @param array ids - 通知ID，数组
     */
    public function markRead($param)
    {
        $ids = $param['ids']??[];
        if (!empty($param['all'])){
            $this->where('is_delete',0)
                ->update([
                    'read' => 1,
                    'update_time' => time()
                ]);
        }else{
            $this->whereIn('id',$ids)
                ->where('is_delete',0)
                ->update([
                    'read' => 1,
                    'update_time' => time()
                ]);
        }


        return [
            'status' => 200,
            'msg'    => lang('success_message')
        ];
    }

    /**
     * 时间 2024-12-12
     * @title 删除通知
     * @desc 删除通知
     * @author wyh
     * @version v1
     * @param array ids - 通知ID，数组
     */
    public function noticeDelete($param)
    {
        $param['ids'] = $param['ids']??[];
        foreach ($param['ids'] as $id){
            $this->where('id',$id)
                ->update([
                    'is_delete' => 1,
                    'delete_time' => time()
                ]);
        }

        return [
            'status' => 200,
            'msg'    => lang('success_message')
        ];
    }

    public function createNotice($param)
    {
        $this->startTrans();

        try {
            $this->create([
                'title' => $param['title'] ?? '',
                'content' => $param['content'] ?? '',
                'attachment' => (!empty($param['attachment']) && is_array($param['attachment'])) ? implode(',',$param['attachment']) : '',
                'type' => $param['type'] ?? 'system',
                'rel_id' => $param['rel_id'] ?? 0,
                'accept_time' => time(),
                'priority' => $param['priority'] ?? 0,
                'read' => 0,
                'create_time' => time(),
            ]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return [
                'status' => 400,
                'msg' => $e->getMessage()
            ];
        }

        return [
            'status' => 200,
            'msg' => lang('success_message')
        ];
    }

}