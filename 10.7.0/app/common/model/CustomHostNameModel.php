<?php
namespace app\common\model;

use think\Model;

/**
 * @title 自定义产品标识模型
 * @desc 自定义产品标识模型
 * @use app\common\model\CustomHostNameModel
 */
class CustomHostNameModel extends Model
{
    protected $name = 'custom_host_name';

    // 设置字段信息
    protected $schema = [
        'id'                                => 'int',
        'custom_host_name_prefix'           => 'string',
        'custom_host_name_string_allow'     => 'string',
        'custom_host_name_string_length'    => 'int',
        'create_time'                       => 'int',
        'update_time'                       => 'int',
    ];

    /**
     * 时间 2024-10-23
     * @title 自定义产品标识列表
     * @desc  自定义产品标识列表
     * @author theworld
     * @version v1
     * @return  int list[].id - 自定义产品标识ID
     * @return  string list[].custom_host_name_prefix - 自定义主机标识前缀
     * @return  array list[].custom_host_name_string_allow - 允许的字符串(number=数字,upper=大写字母,lower=小写字母)
     * @return  int list[].custom_host_name_string_length - 字符串长度
     * @return  array list[].product_group - 关联商品分组,类型为商品组时返回
     * @return  int list[].product_group[].id - 关联商品分组ID
     * @return  string list[].product_group[].first_group_name - 一级分组名称
     * @return  string list[].product_group[].name - 关联商品分组名称
     */
    public function customHostNameList($param)
    {
        $list = $this
                ->field('id,custom_host_name_prefix,custom_host_name_string_allow,custom_host_name_string_length')
                ->select()
                ->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['custom_host_name_string_allow'] = array_filter(explode(',', $value['custom_host_name_string_allow']));
        }
 
        $CustomHostNameLinkModel = new CustomHostNameLinkModel();
        $link = $CustomHostNameLinkModel->alias('a')
            ->field('a.custom_host_name_id,b.id,c.name first_group_name,b.name')
            ->leftJoin('product_group b', 'b.id=a.product_group_id')
            ->leftJoin('product_group c', 'c.id=b.parent_id')
            ->whereIn('a.custom_host_name_id', array_column($list, 'id'))
            ->select()
            ->toArray();
        $linkArr = [];
        foreach ($link as $key => $value) {
            if(!isset($linkArr[$value['custom_host_name_id']])){
                $linkArr[$value['custom_host_name_id']] = [];
            }
            $linkArr[$value['custom_host_name_id']][] = ['id' => $value['id'], 'first_group_name' => $value['first_group_name'], 'name' => $value['name']];
        }
        foreach ($list as $key => $value) {
            $list[$key]['product_group'] = $linkArr[$value['id']] ?? [];
        }

        return ['list'=>$list];
    }

    /**
     * 时间 2024-10-23
     * @title 添加自定义产品标识
     * @desc  添加自定义产品标识
     * @author theworld
     * @version v1
     * @param   string param.custom_host_name_prefix - 自定义主机标识前缀 require
     * @param   array param.custom_host_name_string_allow - 允许的字符串(number=数字,upper=大写字母,lower=小写字母) require
     * @param   int param.custom_host_name_string_length - 字符串长度 require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function createCustomHostName($param)
    {
        $this->startTrans();
        try {
            $customHostName = $this->create([
                'custom_host_name_prefix' => $param['custom_host_name_prefix'],
                'custom_host_name_string_allow' => implode(',', $param['custom_host_name_string_allow']),
                'custom_host_name_string_length' => $param['custom_host_name_string_length'],
                'create_time' => time()
            ]);

            # 记录日志
            active_log(lang('log_add_custom_host_name', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{id}' => $customHostName['id']]), 'custom_host_name', $customHostName->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('create_fail')];
        }
        return ['status' => 200, 'msg' => lang('create_success')];
    }

    /**
     * 时间 2024-10-23
     * @title 编辑自定义产品标识
     * @desc  编辑自定义产品标识
     * @author theworld
     * @version v1
     * @param   string param.id - 自定义产品标识ID require
     * @param   string param.custom_host_name_prefix - 自定义主机标识前缀 require
     * @param   array param.custom_host_name_string_allow - 允许的字符串(number=数字,upper=大写字母,lower=小写字母) require
     * @param   int param.custom_host_name_string_length - 字符串长度 require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function updateCustomHostName($param)
    {
        $customHostName = $this->find($param['id']);
        if(empty($customHostName)){
            return ['status'=>400, 'msg'=>lang('custom_host_name_not_found')];
        }

        $this->startTrans();
        try {
            $param['custom_host_name_string_allow'] = implode(',', $param['custom_host_name_string_allow']);
            
            $this->update([
                'custom_host_name_prefix' => $param['custom_host_name_prefix'],
                'custom_host_name_string_allow' => $param['custom_host_name_string_allow'],
                'custom_host_name_string_length' => $param['custom_host_name_string_length'],
                'create_time' => time()
            ], ['id' => $param['id']]);

            $description = [];

            $desc = [
                'custom_host_name_prefix'           => lang('field_product_custom_host_name_prefix'),
                'custom_host_name_string_allow'     => lang('field_product_custom_host_name_string_allow'),
                'custom_host_name_string_length'    => lang('field_product_custom_host_name_string_length'),
            ];

            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $customHostName[$k] != $param[$k]){
                    $old = $customHostName[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_custom_host_name', [
                    '{admin}'   => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{id}'      => $customHostName['id'],
                    '{detail}'  => implode(',', $description),
                ]);
                active_log($description, 'custom_host_name', $customHostName->id);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2024-10-23
     * @title 删除自定义产品标识
     * @desc 删除自定义产品标识
     * @author theworld
     * @version v1
     * @param int id - 自定义产品标识ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteCustomHostName($id)
    {
        $customHostName = $this->find($id);
        if(empty($customHostName)){
            return ['status'=>400, 'msg'=>lang('custom_host_name_not_found')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang('log_delete_custom_host_name', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{id}' => $customHostName['id']]), 'custom_host_name', $customHostName->id);
            
            CustomHostNameLinkModel::where('custom_host_name_id', $id)->delete();
            $this->destroy($id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }
        return ['status' => 200, 'msg' => lang('delete_success')];
    }
}