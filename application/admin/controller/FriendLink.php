<?php
namespace app\admin\controller;

use app\admin\model\Admin as Admin_user;
use app\admin\model\User as Home_user;
use app\admin\model\Friend;
use think\Session;
use think\Config;

/**
* 友情链接 展示
**/
class FriendLink extends Common
{	
	public function __construct(){
		parent::__construct();
	   
        if(!Session::get('login_id','forum_admin')){
        	$this->redirect('login/login');
        }
	}
    
    public function index(){
        $post = input('post.');

        $name = $post['name'];
        $link = $post['link'];
        $is_del = ($post['is_del'])?$post['is_del']:'-1';

        $where = array();
        if(!empty($name)){
            $where['name'] = ['like','%'+$name+'%'];
        }
        if(!empty($link)){
            $where['link'] = ['like','%'+$link+'%'];
        }
        if($is_del!='-1'){ 
            $where['is_del'] = $is_del; 
        }

        $friends = Friend::where($where)->order('ctime','desc')->paginate(10, false);
        $page = $friends->render();

        $data = array(
                'friends'=>$friends,
                'page'=>$page,
                'post'=>$post,
            );
        return $this->view->fetch('index',$data);
    }

    public function FriendEdit(){
        $get = input('get.');

        $FriendInfo = array();
        if(!empty($get['id'])){
            $id = $get['id'];
            $FriendInfo =  Friend::get(['id'=>$id]);
        }

        $data = array(
                'FriendInfo'=>$FriendInfo,
            );
        return $this->view->fetch('FriendEdit',$data);
    }

    public function saveFriend(){
        $post = input('post.');

        if(!preg_match('/(http:\/\/)|(https:\/\/)/i',$post['link'])) {
            $post['link'] = 'http://'.$post['link'];
        }

        $file = request()->file('linkimg');
        if($file){
            $info = $file->move(ROOT_PATH.'public'.DS.'uploads'.DS.'linkimg');
            if($info){
               $post['linkimg'] = 'uploads'.DS.'linkimg'.DS.$info->getSaveName();
            }else{
                $post['linkimg'] = '';
                Log::log(Session::get('login_id','forum_admin').' 上传链图 '.$post['name'].' 错误 ：'.$file->getError());
            }
        }

        $post['utime'] = time();
        if(empty($post['id'])){
            $post['is_check'] = 1;
            $post['adminid'] = Session::get('login_id','forum_admin');
            $post['ctime'] = time();
            $post['utime'] = time();
            Friend::create($post);
        }else{
            $id = $post['id'];
            unset($post['id']);

            $post['adminid'] = Session::get('login_id','forum_admin');
            Friend::where('id', $id)->update($post); 
        }

        $this->redirect('FriendLink/index');
    }

    public function isDelFriend(){
        $post = input('post.');
        $id = $post['id'];

        $user = Friend::get(['id'=>$id]);
        $update = array();
        if(empty($user['is_del'])){
            $update['is_del'] = 1;
        }else if($user['is_del']==1){
           $update['is_del'] = 0;
        }
        Friend::where('id', $id)->update($update);

        return json(['flog'=>1, 'msg'=>$update['is_del']]);
    }

    public function isCheckFriend(){
        $post = input('post.');
        $id = $post['id'];

        $user = Friend::get(['id'=>$id]);
        $update = array();
        if(empty($user['is_check'])||$user['is_check']==2){
            $update['is_check'] = 1;
        }else if($user['is_check']==1){
           $update['is_check'] = 2;
        }
        Friend::where('id', $id)->update($update);

        return json(['flog'=>1, 'msg'=>$update['is_check']]);
    }
}