<?php
namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\User as Home_user;
use app\admin\model\TradeRecord;
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

        $phone = $post['phone'];
        $name = $post['name'];
        $is_del = ($post['is_del'])?$post['is_del']:'-1';

    	$where = array();
        if(!empty($phone)){
            $where['phone'] = $phone; 
        }
        if(!empty($name)){
            $where['name'] = $name; 
        }
        if($is_del!='-1'){ 
            $where['is_del'] = $is_del; 
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
            if($findField=='phone'){
                $msg = '手机号已经存在';
            }
            return json(['flog'=>0, 'msg'=>$msg]);
        }else{
            return json(['flog'=>1, 'msg'=>'success']);
        }
    }

    public function saveUser(){
        $post = input('post.');

        $post['utime'] = time();
        if(empty($post['id'])){
            $post['ctime'] = time();
            $accountData = Home_user::create($post);
        }else{
            $id = $post['id'];
            unset($post['id']);
            $accountData = Home_user::where('id', $id)->update($post); 
        }
        if(!empty($post['money'])){
            $tradeRecord = array();
            $tradeRecord['userid'] = $uid; 
            $tradeRecord['trade_money'] = $post['money']; 
            $tradeRecord['surplus_money'] = $post['money']; 
            $tradeRecord['content'] = '直接修改用户资金'; 
            $tradeRecord['ctime'] = time();
            $tradeRecord['utime'] = time();
            TradeRecord::create($tradeRecord);
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