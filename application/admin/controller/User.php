<?php
namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\User as Home_user;
use think\Session;
use think\Config;
use smtp;
use think\Log;


class User extends Common
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
        $nikename = $post['nikename'];
        $sex = ($post['sex'])?$post['sex']:0;
        $is_del = ($post['is_del'])?$post['is_del']:'-1';
        $is_activate = ($post['is_activate'])?$post['is_activate']:'-1';

    	$where = array();
        if(!empty($email)){
            $where['email'] = $email; 
        }
        if(!empty($nikename)){
            $where['nikename'] = $nikename; 
        }
        if(!empty($sex)){
            $where['sex'] = $sex;
        }
        if($is_del!='-1'){ 
            $where['is_del'] = $is_del; 
        }
        if($is_activate!='-1'){ 
            $where['is_activate'] = $is_activate; 
        }

        $users = Home_user::where($where)->order('ctime','desc')->paginate(10, false);
        $page = $users->render();

        $data = array(
                'users'=>$users,
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
            $UserInfo =  Home_user::get(['id'=>$uid]);
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

        $user = Home_user::get([$findField=>$findVal]);
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

        $file = request()->file('headimg');
        if($file){
            $info = $file->move(ROOT_PATH.'public'.DS.'uploads'.DS.'headimg');
            if($info){
               $post['headimg'] = 'uploads'.DS.'headimg'.DS.$info->getSaveName();
            }else{
                Log::log(Session::get('login_id','forum_admin').' 上传用户 '.$post['email'].' 头像错误 ：'.$file->getError());
                $post['headimg'] = '';
            }
        }

        $post['utime'] = time();
        if(empty($post['id'])){
            $login_stat = rand(11111, 99999);
            $post['password'] = md5(md5($post['password']).$login_stat);
            $post['login_stat'] = $login_stat;
            $post['is_activate'] = 1;
            $post['ctime'] = time();
            $accountData = Home_user::create($post);
        }else{
            $id = $post['id'];
            unset($post['id']);
            $user = Home_user::get(['id'=>$id]);
            if(!empty($post['password'])){
                $post['password'] = md5(md5($post['password']).$user['login_stat']);
            }
            $accountData = Home_user::where('id', $id)->update($post); 
        }
       

        $this->redirect('user/index');
    }

    public function isDelUser(){
        $post = input('post.');
        $uid = $post['uid'];

        $user = Home_user::get(['id'=>$uid]);
        $update = array();
        if(empty($user['is_del'])){
            $update['is_del'] = 1;
        }else if($user['is_del']==1){
           $update['is_del'] = 0;
        }
        Home_user::where('id', $uid)->update($update);

        return json(['flog'=>1, 'msg'=>$update['is_del']]);
    }
    public function isActivate(){
        $post = input('post.');
        $uid = $post['uid'];

        $user = Home_user::get(['id'=>$uid]);
        $update = array();
        if(empty($user['is_activate'])){
            $update['is_activate'] = 1;
        }else if($user['is_activate']==1){
           $update['is_activate'] = 0;
        }
        Home_user::where('id', $uid)->update($update);

        return json(['flog'=>1, 'msg'=>$update['is_activate']]);
    }
}