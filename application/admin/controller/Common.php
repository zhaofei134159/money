<?php
namespace app\admin\controller;

use think\View;
use think\Request;
use think\Session;
use think\Controller;
use think\Log;

use app\admin\model\Common as modelCommon;
use app\admin\model\Admin;


class Common extends Controller
{
	public $view;

	public function __construct(){
    
		//实例化view
		$this->view = new View();

        $request = \think\Request::instance();
        $this->assign('controller',$request->controller());
        $this->assign('action',$request->action());


        $this->init($request->controller(),$request->action());
        // echo Power_group::getLastSql();
	}

    //获取当前登录人的权限
    public function init($controller,$action){
        // $time = time();
        if(Session::get('login_id','forum_admin')){

            $account = Admin::get(['id' => Session::get('login_id','forum_admin')]);
            
            $this->assign('admin',$account);
            
        }

    }

}
