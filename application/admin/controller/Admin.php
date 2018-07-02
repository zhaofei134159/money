<?php
namespace app\admin\controller;

use app\admin\model\Admin as Admin_user;
use app\admin\model\User as Home_user;
use think\Session;
use think\Config;
use smtp;
use think\Log;


class Admin extends Common
{	
	public function __construct(){
		parent::__construct();
	   
        if(!Session::get('login_id','forum_admin')){
        	$this->redirect('login/login');
        }
	}
    
    public function index(){
        $post = input('post.');

        $email = $post['email'];
        $name = $post['name'];
        $is_del = ($post['is_del'])?$post['is_del']:'-1';

    	$where = array();
        if(!empty($email)){
            $where['email'] = $email; 
        }
        if(!empty($name)){
            $where['name'] = $name; 
        }
        if($is_del!='-1'){ 
            $where['is_del'] = $is_del; 
        }

        $admins = Admin_user::where($where)->order('createtime','desc')->paginate(10, false);
        $page = $admins->render();

        $data = array(
                'admins'=>$admins,
                'page'=>$page,
                'post'=>$post,
            );
        return $this->view->fetch('index',$data);
    }

    public function editUser(){
        $get = input('get.');

        $UserInfo = array();
        if(!empty($get['uid'])){
            $uid = $get['uid'];
            $UserInfo =  Admin_user::get(['id'=>$uid]);
        }


        $data = array(
                'UserInfo'=>$UserInfo,
            );
        return $this->view->fetch('editUser',$data);
    }

    public function findUser(){
        $post = input('post.');
        $findField = $post['findField'];
        $findVal = $post['findVal'];

        $user = Admin_user::get([$findField=>$findVal]);
        if(!empty($user)){
            $msg = '';
            if($findField=='email'){
                $msg = '邮箱已经存在';
            }else if($findField=='nikename'){
                $msg = '昵称已经存在';
            }
            return json(['flog'=>0, 'msg'=>$msg]);
        }else{
            return json(['flog'=>1, 'msg'=>'success']);
        }
    }

    public function saveUser(){
        $post = input('post.');

        $post['updatetime'] = time();
        if(empty($post['id'])){
            $login_salt = rand(11111, 99999);
            $post['password'] = md5(md5($post['password']).$login_salt);
            $post['createtime'] = time();
            $accountData = Admin_user::create($post);
        }else{
            $id = $post['id'];
            unset($post['id']);
            $user = Admin_user::get(['id'=>$id]);
            if(!empty($post['password'])){
                $post['password'] = md5(md5($post['password']).$user['login_stat']);
            }
            $accountData = Admin_user::where('id', $id)->update($post); 
        }
       

        $this->redirect('admin/index');
    }

    public function isDelUser(){
        $post = input('post.');
        $uid = $post['uid'];

        $user = Admin_user::get(['id'=>$uid]);
        $update = array();
        if(empty($user['is_del'])){
            $update['is_del'] = 1;
        }else if($user['is_del']==1){
           $update['is_del'] = 0;
        }
        Admin_user::where('id', $uid)->update($update);

        return json(['flog'=>1, 'msg'=>$update['is_del']]);
    }
   
}