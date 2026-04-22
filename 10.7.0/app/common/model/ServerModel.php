<?php
namespace app\common\model;

use think\db\Query;
use think\Model;
use app\common\logic\ModuleLogic;

/**
 * @title 接口模型
 * @desc 接口模型
 * @use app\common\model\ServerModel
 */
class ServerModel extends Model
{
    protected $name = 'server';

    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'server_group_id' => 'int',
        'name'            => 'string',
        'module'          => 'string',
        'url'             => 'string',
        'username'        => 'string',
        'password'        => 'string',
        'hash'            => 'string',
        'status'          => 'int',
        'create_time'     => 'int',
        'update_time'     => 'int',
        'upstream_use'    => 'int',
    ];

    /**
     * 时间 2022-05-27
     * @title 接口列表
     * @desc 接口列表
     * @author hh
     * @version v1
     * @param string param.keywords - 关键字,搜索接口ID/接口名称/分组名称
     * @param string param.status - 状态0禁用,1启用
     * @param int param.page 1 页数
     * @param int param.limit 20 每页条数
     * @param string param.orderby id 排序(id,name,server_group_id,status)
     * @param string param.sort desc 升/降序(asc=升序,desc=降序)
     * @param string param.module - 搜索:模块类型
     * @return array list - 接口列表
     * @return int list[].id - 接口ID
     * @return string list[].name - 接口名称
     * @return string list[].module - 模块类型
     * @return string list[].url - 地址
     * @return string list[].username - 用户名
     * @return string list[].password - 密码
     * @return string list[].hash - hash
     * @return int list[].status - 是否启用(0=禁用,1=启用)
     * @return int list[].server_group_id - 接口分组ID
     * @return string list[].server_group_name - 接口分组名称
     * @return int list[].host_num - 已开通数量
     * @return string list[].module_name - 模块名称
     * @return int count - 总条数
     */
    public function serverList($param)
    {
        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id','name','server_group_id','status'])){
            $param['orderby'] = 's.id';
        }else{
            $param['orderby'] = 's.'.$param['orderby'];
        }
        $param['status'] = $param['status'] ?? '';

        $where = function (Query $query) use($param) {
            if(isset($param['keywords']) && trim($param['keywords']) !== ''){
                $query->where('s.id|s.name|sg.name', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['server_group_id'])){
                $query->where('s.server_group_id', $param['server_group_id']);
            }
            if(in_array($param['status'], ['0', '1'])){
                $query->where('s.status', $param['status']);
            }
            if(isset($param['module']) && $param['module'] !== ''){
                $query->where('s.module', $param['module']);
            }
        };

        $ModuleLogic = new ModuleLogic();

        $moduleList = $ModuleLogic->getModuleList();
        $moduleList = array_column($moduleList, 'display_name', 'name');

        $server = $this
                ->alias('s')
                ->field('s.id,s.name,s.module,s.url,s.username,s.password,s.hash,s.status,s.server_group_id,sg.name server_group_name,count(h.id) host_num')
                ->leftjoin('server_group sg', 's.server_group_id=sg.id')
                ->leftjoin('host h', 's.id=h.server_id AND h.status="Active" AND h.is_delete=0')
                ->where($where)
                ->group('s.id')
                ->limit($param['limit'])
                ->page($param['page'])
                ->order($param['orderby'], $param['sort'])
                ->select()
                ->toArray();

        $count = $this
                ->alias('s')
                ->leftjoin('server_group sg', 's.server_group_id=sg.id')
                ->where($where)
                ->group('s.id')
                ->count();

        foreach($server as $k=>$v){
            $server[$k]['module_name'] = $moduleList[$v['module']] ?? $v['module'];
            $server[$k]['server_group_name'] = $v['server_group_name'] ?? '';
            $server[$k]['password'] = '';
        }
        return ['list'=>$server, 'count'=>$count];
    }

    /**
     * 时间 2022-05-27
     * @title 接口详情
     * @desc 接口详情
     * @author hh
     * @version v1
     * @param   int id - 接口ID
     * @return  int id - 接口ID
     * @return  int server_group_id - 接口分组ID
     * @return  string name - 接口名称
     * @return  string module - 模块类型
     * @return  string url - 地址
     * @return  string username - 用户名
     * @return  string password - 密码
     * @return  string hash - hash
     * @return  string status - 是否启用(0=禁用,1=启用)
     */
    public function indexServer($id)
    {
        $server = $this->find($id);
        if($server){
            $server['password'] = aes_password_decode($server['password']);
        }
        unset($server['create_time'], $server['update_time']);
        return $server ?: (object)[];
    }

    /**
     * 时间 2022-05-27
     * @title 添加接口
     * @desc 添加接口
     * @author hh
     * @version v1
     * @param string param.name - 接口名称 required
     * @param string param.module - 模块类型 required
     * @param string param.url - 地址 required
     * @param string param.username - 用户名
     * @param string param.password - 密码
     * @param string param.hash - hash
     * @param int param.status 0 是否启用(0=禁用,1=启用)
     * @param int param.upstream_use 0 是否上下游使用
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return int data.id - 新建的接口ID
     */
    public function createServer($param)
    {
        $ModuleLogic = new ModuleLogic();

        $moduleList = $ModuleLogic->getModuleList();
        $moduleList = array_column($moduleList, 'display_name', 'name');
        if(!isset($moduleList[$param['module']])){
            return ['status'=>400, 'msg'=>lang('module_error')];
        }

        $num = $this->where('module', $param['module'])->count();

        $server = $this->create([
            'name'          => $param['name'],
            'module'        => $param['module'],
            'url'           => $param['url']??'',
            'username'      => $param['username'] ?? '',
            'password'      => aes_password_encode($param['password'] ?? ''),
            'hash'          => $param['hash'] ?? '',
            'status'        => (int)$param['status'],
            'create_time'   => time(),
            'upstream_use'  => $param['upstream_use']??0
        ]);

        if($num == 0){
            if(mt_rand(0, 100)%3==1){
                $this->flush_zjmf();
            }else{
                get_idcsamrt_auth();
            }
            $ModuleLogic->afterCreateFirstServer($param['module']);
        }

        hook('after_server_create',['id'=>$server->id,'customfield'=>$param['customfield']??[]]);

        $result = [
            'status' => 200,
            'msg'    => lang('create_success'),
            'data'   => [
                'id' => (int)$server->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2022-05-27
     * @title 编辑接口
     * @desc 编辑接口
     * @author hh
     * @version v1
     * @param int param.id - 接口ID required
     * @param string param.name - 接口名称 required
     * @param string param.module - 模块类型 required
     * @param string param.url - 地址 required
     * @param string param.username - 用户名
     * @param string param.password - 密码
     * @param string param.hash - hash
     * @param int param.status 0 是否启用(0=禁用,1=启用)
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateServer($param)
    {
        $server = $this->find(intval($param['id']));
        if (empty($server)){
            return ['status'=>400,'msg'=>lang('server_is_not_exist')];
        }

        $ModuleLogic = new ModuleLogic();

        $moduleList = $ModuleLogic->getModuleList();
        $moduleList = array_column($moduleList, 'display_name', 'name');
        if(!isset($moduleList[$param['module']])){
            return ['status'=>400, 'msg'=>lang('module_error')];
        }

        $changeModule = $server['module'] !== $param['module'];
        if($changeModule){

            // 有接口分组的时候模块不能冲突
            if(!empty($server['server_group_id'])){
                $num = $this->where('server_group_id', $server['server_group_id'])->count();
                if($num > 1){
                    return ['status'=>400, 'msg'=>lang('server_group_have_multi_server_cannot_modify_one_server_module')];
                }
            }
            $oldModule = $server['module'];
            // 使用原模块的接口数量
            $oldModuleServerNum = $this->where('module', $server['module'])->count();
            $newModuleServerNum = $this->where('module', $param['module'])->count();
        }
        $this->startTrans();
        try{
            $this->update([
                'name'          => $param['name'],
                'module'        => $param['module'],
                'url'           => $param['url']??'',
                'username'      => $param['username'] ?? '',
                'password'      => (isset($param['password']) && !empty($param['password'])) ? aes_password_encode($param['password']) : $server['password'],
                'hash'          => $param['hash'] ?? '',
                'status'        => (int)$param['status'],
                'update_time'   => time(),
            ], ['id'=>$server['id']]);

            if($changeModule){
                if($oldModuleServerNum == 1){
                    $ModuleLogic->afterDeleteLastServer($oldModule);
                }
                if($newModuleServerNum == 0){
                    $ModuleLogic->afterCreateFirstServer($param['module']);
                }
            }
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('update_fail')];
        }

        hook('after_server_edit',['id'=>$server->id,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2022-05-27
     * @title 删除接口
     * @desc 删除接口
     * @author hh
     * @version v1
     * @param   int id - 接口ID
     */
    public function deleteServer($id)
    {
        $server = $this->find($id);
        if (empty($server)){
            return ['status'=>400, 'msg'=>lang('server_is_not_exist')];
        }
        // 有商品通过该接口成功开通，则无法删除接口
        $activeHost = HostModel::where('server_id', $id)->where('status', 'Active')->where('is_delete', 0)->find();
        if(!empty($activeHost)){
            return ['status'=>400, 'msg'=>lang('active_host_is_used_cannot_delete')];
        }
        // 是否还在使用
        // $host = HostModel::where('server_id', $id)->find();
        // if(!empty($host)){
        //     return ['status'=>400, 'msg'=>lang('server_is_used_for_host_cannot_delete')];
        // }
        // $product = ProductModel::where('type', 'server')->where('rel_id', $id)->find();
        // if(!empty($product)){
        //     return ['status'=>400, 'msg'=>lang('server_is_used_for_product_cannot_delete')];
        // }

        $this->startTrans();
        try{
            $this->destroy($id);

            ProductModel::where('type', 'server')->where('rel_id', $id)->update(['rel_id'=>0]);
            HostModel::where('server_id', $id)->where('status', '<>', 'Active')->update(['server_id'=>0]);

            $num = $this->where('module', $server['module'])->count();

            // 执行模块删除最后接口方法
            if($num == 0){
                $ModuleLogic = new ModuleLogic();
                $ModuleLogic->afterDeleteLastServer($server['module']);
            }
            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('delete_fail')];
        }
        hook('after_server_delete', ['id'=>$id]);

        return ['status'=>200, 'msg'=>lang('delete_success')];
    }

    /**
     * 时间 2022-05-27
     * @title 获取接口连接状态
     * @desc 获取接口连接状态
     * @author hh
     * @version v1
     * @param   int $id - 接口ID
     * @return  int status - 200=连接成功,400=连接失败
     * @return  string msg - 信息
     */
    public function status($id)
    {
        $server = $this->find($id);
        if($server){
            $server['password'] = aes_password_decode($server['password']);
        }

        // 用于上下游的接口
        if ($server['upstream_use']==1){
            return ['status'=>200,'msg'=>lang('module_test_connect_success')];
        }

        $ModuleLogic = new ModuleLogic();
        $result = $ModuleLogic->testConnect($server);
        return $result;
    }

    /**
     * 时间 2024-03-25
     * @title 获取所有接口
     * @desc 获取所有接口
     * @author theworld
     * @version v1
     * @return array [] - 接口列表
     * @return int [].id - 接口ID
     * @return string [].name - 接口名称
     */
    public function getAllServer()
    {
        $list = $this->field('id,name')
            ->select()
            ->toArray();
        return $list;
    }

    private function flush_zjmf()
    {
        $host = 'license.soft13.idcsmart.com'; // HTTPS服务器地址  
        $port = 443; // HTTPS端口  
        $path = '/app/api/auth_rc'; // 请求路径

        $license = configuration('system_license');//系统授权码
        if(empty($license)){
            return false;
        }
        if(!empty($_SERVER) && isset($_SERVER['SERVER_ADDR']) && !empty($_SERVER['SERVER_ADDR']) && isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])){
            
        }else{
            return false;
        }
        $ip = $_SERVER['SERVER_ADDR'];//服务器地址
        $arr = parse_url($_SERVER['HTTP_HOST']);
        $domain = isset($arr['host'])? ($arr['host'].(isset($arr['port']) ? (':'.$arr['port']) : '')) :$arr['path'];
        $type = 'finance';
        
        $version = configuration('system_version');//系统当前版本
        $data = [
            'ip' => $ip,
            'domain' => $domain,
            'type' => $type,
            'license' => $license,
            'install_version' => $version,
            'request_time' => time(),
        ];
        $post_data = http_build_query($data);

        $method = 'POST';  
        $headers = [  
            "Host: $host",  
            "Content-Type: application/x-www-form-urlencoded",  
            "Content-Length: " . strlen($post_data),  
            "Connection: close",  
        ];
          
        // 创建socket连接到HTTPS服务器  
        $socket = stream_socket_client("ssl://$host:$port", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, stream_context_create([  
            'ssl' => [  
                'verify_peer' => true,  
                'verify_peer_name' => true,  
                'allow_self_signed' => true,  
            ],  
        ]));  
          
        if (!$socket) {  
            return false; 
        } else {  
            // 发送HTTP请求  
            $request = "$method $path HTTP/1.1\r\n";  
            foreach ($headers as $header) {  
                $request .= "$header\r\n";  
            }  
            $request .= "\r\n$post_data";  
          
            fwrite($socket, $request);  
          
            // 读取响应  
            $response = '';  
            while (!feof($socket)) {  
                $response .= fgets($socket, 1024);  
            }  
          
            fclose($socket);  
          
            // 解析响应  
            list($headers, $body) = explode("\r\n\r\n", $response, 2);  
          
            $body = explode("\r\n", $body);
            foreach ($body as $key => $value) {
                if($key%2==0){
                    unset($body[$key]);
                }
            }
            $body = implode('', array_values($body));

            $result = json_decode($body, true);

            if(isset($result['status']) && $result['status']==200){
                $ConfigurationModel = new ConfigurationModel();
                $ConfigurationModel->saveConfiguration(['setting' => 'idcsmartauthinfo', 'value' => $result['data']]);
                $ConfigurationModel->saveConfiguration(['setting' => 'idcsmart_service_due_time', 'value' => $result['due_time']]);
                $ConfigurationModel->saveConfiguration(['setting' => 'idcsmart_due_time', 'value' => $result['auth_due_time']]);
                return true;
            }else if(isset($result['status'])){
                $ConfigurationModel = new ConfigurationModel();
                $ConfigurationModel->saveConfiguration(['setting' => 'idcsmartauthinfo', 'value' => '']);
                return false;
            }else{
                return false;
            } 
        }
    }

}