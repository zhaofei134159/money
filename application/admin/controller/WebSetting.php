<?php
namespace app\admin\controller;

use app\admin\model\Admin as Admin_user;
use app\admin\model\User as Home_user;
use app\admin\model\Setting;
use think\Session;
use think\Config;

/**
* 网站设置 展示
**/
class WebSetting extends Common
{	
	public function __construct(){
		parent::__construct();
	   
        if(!Session::get('login_id','forum_admin')){
        	$this->redirect('login/login');
        }
	}
    
    public function index(){

        $WebSetting = Setting::get();

        $data = array(
                'WebSetting'=>$WebSetting,
            );
        return $this->view->fetch('index',$data);
    }

    public function saveSetting(){
        $post = input('post.');

        $file = request()->file('webimg');

        if($file){
            $info = $file->move(ROOT_PATH.'public'.DS.'uploads'.DS.'webimg');
            if($info){
               $post['webimg'] = 'uploads'.DS.'webimg'.DS.$info->getSaveName();
            }else{
                Log::log(Session::get('login_id','forum_admin').' 上传网站 '.$post['name'].' 图错误 ：'.$file->getError());
                $post['webimg'] = '';
            }
        }

        $post['utime'] = time();
        if(empty($post['id'])){
            $post['ctime'] = time();
            $accountData = Setting::create($post);
        }else{
            $id = $post['id'];
            unset($post['id']);
            Setting::where('id', $id)->update($post); 
        }
       
        $this->redirect('WebSetting/index');
    }
}