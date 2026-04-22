<?php
namespace app\common\model;

use think\Model;

/**
 * @title 产品附加模型
 * @desc  产品附加模型
 * @use app\common\model\HostAdditionModel
 */
class HostAdditionModel extends Model
{
	protected $name = 'host_addition';

    // 设置字段信息
    protected $schema = [
        'id'                    => 'int',
        'host_id'               => 'int',
        'country_id'            => 'int',
        'city'                  => 'string',
        'area'                  => 'string',
        'power_status'          => 'string',
        'image_icon'            => 'string',
        'image_name'            => 'string',
        'username'              => 'string',
        'password'              => 'string',
        'port'                  => 'int',
        'create_time'           => 'int',
        'update_time'           => 'int',
    ];

    // 对应的图标
    protected $imageIcon = [
        'windows'   => 'Windows',
        'centos'    => 'CentOS',
        'ubuntu'    => 'Ubuntu',
        'debian'    => 'Debian',
        'esxi'      => 'ESXi',
        'xenserver' => 'XenServer',
        'freebsd'   => 'FreeBSD',
        'fedora'    => 'Fedora',
        'archlinux' => 'ArchLinux',
        'rocky'     => 'Rocky',
        'almalinux' => 'AlmaLinux',
        'openeuler' => 'OpenEuler',
        'redhat'    => 'RedHat',
        'android'   => 'Android',
    ];

    // 不保存用户产品密码:1是0否
    protected $donotSaveProductPassword = NULL;

    /**
     * 时间 2024-06-28
     * @title 保存产品附加信息
     * @desc  保存产品附加信息
     * @author hh
     * @version v1
     * @param   int hostId - 产品ID require
     * @param   array data - 要保存的数据(有哪些键保存哪些) require
     * @param   int data.country_id - 国家ID
     * @param   string data.city - 城市
     * @param   string data.area - 区域
     * @param   string data.power_status - 电源状态(on=开机,off=关机,suspend=暂停,operating=操作中,fault=故障)
     * @param   string data.image_icon - 镜像图标(Windows,CentOS,Ubuntu,Debian,ESXi,XenServer,FreeBSD,Fedora,ArchLinux,Rocky,AlmaLinux,OpenEuler,RedHat,其他)
     * @param   string data.image_name - 镜像名称
     * @param   string data.username - 实例用户名
     * @param   string data.password - 实例密码
     * @param   string|int data.port - 端口
     * @return  bool
     */
    public function hostAdditionSave($hostId, $data){
        // 存储的时候检查下镜像图标
        if(isset($data['image_icon'])){
            if( isset($this->imageIcon[ strtolower($data['image_icon']) ]) ){
                $data['image_icon'] = $this->imageIcon[ strtolower($data['image_icon']) ];

                // 有图标并且没有用户名时,自动变更
                // if(!isset($data['username'])){
                //     $data['username'] = $data['image_icon'] == 'Windows' ? 'administrator' : 'root';
                // }
            }
        }
        if(isset($data['password'])){
            if($this->donotSaveProductPassword() == 1){
                $data['password'] = '';
            }
            $data['password'] = aes_password_encode($data['password']);
        }
        $data['host_id'] = $hostId;
        $exist = $this->where('host_id', $hostId)->find();
        if(!empty($exist)){
            $data['update_time'] = time();
            $this->update($data, ['host_id'=>$hostId], ['country_id','city','area','power_status','image_icon','image_name','username','password','port','update_time']);
        }else{
            // 默认给个开机
            $data['create_time'] = time();
            $this->create($data, ['host_id','country_id','city','area','power_status','image_icon','image_name','username','password','port','create_time']);
        }
        return true;
    }

    /**
     * 时间 2025-11-21
     * @title 不保存用户产品密码
     * @desc  不保存用户产品密码
     * @author hh
     * @version v1
     * @return  int
     */
    public function donotSaveProductPassword(): int
    {
        if(!is_numeric($this->donotSaveProductPassword)){
            $this->donotSaveProductPassword = (int)configuration('donot_save_client_product_password');
        }
        return $this->donotSaveProductPassword;
    }

    /**
     * 时间 2024-07-10
     * @title 密码获取器
     * @desc  密码获取器,自动解密
     * @author hh
     * @version v1
     * @param   string $value - 密码 require
     * @return  string
     */
    public function getPasswordAttr($value)
    {
        if($this->donotSaveProductPassword() == 1){
            return '';
        }
        return aes_password_decode($value) ?: '';
    }


}