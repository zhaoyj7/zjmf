<?php
namespace app\admin\controller;

use app\admin\model\AdminWidgetModel;
use app\admin\validate\WidgetValidate;
use app\common\logic\WidgetLogic;

/**
 * @title 挂件管理
 * @desc 挂件管理
 * @use app\admin\controller\WidgetController
 */
class WidgetController extends AdminBaseController
{	
    public function initialize()
    {
        parent::initialize();
        $this->validate = new WidgetValidate();
    }

    /**
     * 时间 2023-05-04
     * @title 后台首页挂件
     * @desc 后台首页挂件
     * @url /admin/v1/widget
     * @method GET
     * @author hh
     * @version v1
     * @return string widget[].id - desc:挂件标识
     * @return string widget[].title - desc:挂件名称
     * @return int widget[].columns - desc:挂件列数
     * @return array show_widget - desc:显示的挂件标识
     */
    public function index()
    {
    	$AdminWidgetModel = new AdminWidgetModel();
    	$data = $AdminWidgetModel->adminWidgetIndex();

    	$result = [
    		'status' => 200,
    		'msg'	 => lang('success_message'),
    		'data'	 => $data,
    	];
    	return json($result);
    }

    /**
     * 时间 2023-05-04
     * @title 保存挂件排序
     * @desc 保存挂件排序
     * @url /admin/v1/widget/order
     * @method PUT
     * @author hh
     * @version v1
     * @param array widget_arr - desc:挂件标识 只要已显示的并且已排序 validate:required
     */
    public function widgetSaveOrder()
    {
    	$param = $this->request->param();

    	if (!$this->validate->scene('save_order')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

    	$AdminWidgetModel = new AdminWidgetModel();
    	$result = $AdminWidgetModel->adminWidgetSaveOrder($param);
    	return json($result);
    }

    /**
     * 时间 2023-05-04
     * @title 显示/隐藏挂件
     * @desc 显示/隐藏挂件
     * @url /admin/v1/widget/status
     * @method PUT
     * @author hh
     * @version v1
     * @param string widget - desc:挂件标识 validate:required
     * @param int status - desc:状态 0隐藏 1显示 validate:required
     */
    public function toggleWidget()
    {
    	$param = $this->request->param();

    	if (!$this->validate->scene('toggle_status')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $AdminWidgetModel = new AdminWidgetModel();
    	$result = $AdminWidgetModel->toggleWidget($param);
    	return json($result);
    }

    /**
     * 时间 2023-05-04
     * @title 获取挂件内容
     * @desc 获取挂件内容
     * @url /admin/v1/widget/output
     * @method GET
     * @author hh
     * @version v1
     * @param string widget - desc:挂件标识 validate:required
     * @return string content - desc:挂件内容
     */
    public function output()
    {
    	$param = $this->request->param();
    	$param['widget'] = $param['widget'] ?? '';

        // 20240204 hh 去掉验证,提升获取速度
    	// $AdminWidgetModel = new AdminWidgetModel();
    	// $data = $AdminWidgetModel->adminWidgetIndex();
    	// if(in_array($param['widget'], array_column($data['widget'], 'id'))){
    		$WidgetLogic = new WidgetLogic();
    		$data = $WidgetLogic->output($param['widget']);
    	// }else{
    	// 	$data = [
    	// 		'content' => '',
    	// 	];
    	// }
        
    	$result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data,
        ];
        return json($result);

    }

    /**
     * 时间 2023-05-04
     * @title 获取挂件数据
     * @desc  获取挂件数据
     * @url /admin/v1/widget/data
     * @method GET
     * @author hh
     * @version v1
     * @param string widget - desc:挂件标识 validate:required
     */
    public function getData()
    {
    	$param = $this->request->param();
    	$param['widget'] = $param['widget'] ?? '';

    	$AdminWidgetModel = new AdminWidgetModel();
    	$data = $AdminWidgetModel->adminWidgetIndex();
    	if(in_array($param['widget'], array_column($data['widget'], 'id'))){
    		$WidgetLogic = new WidgetLogic();
    		$data = $WidgetLogic->getData($param['widget']);
    	}else{
    		$data = [];
    	}

    	$result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data,
        ];
        return json($result);
    }


}