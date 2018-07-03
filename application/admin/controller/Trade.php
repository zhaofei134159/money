<?php
namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\User as Home_user;
use app\admin\model\TradeRecord;
use think\Session;
use think\Config;
use smtp;
use think\Log;


class Trade extends Common
{	
	public function __construct(){
		parent::__construct();
	   
        if(!Session::get('login_id','forum_admin')){
        	$this->redirect('login/login');
        }
	}

	public function addTrade(){
		$uid = input('post.uid');

		$user = Home_user::get(['id'=>$uid,'is_del'=>0]);
		if(empty($user)){
        	return json(['flog'=>0, 'msg'=>'用户已被删除，无法添加交易记录','data'=>'']);
		}

        $data = array(
                'user'=>$user,
            );
        $html = $this->view->fetch('add_trade',$data);

        return json(['flog'=>1, 'msg'=>'成功','data'=>$html]);
	}

	public function tradeSave(){
		$post = input('post.');

		$trade_money = $post['trade_money'];
		$uid = $post['uid'];
		$content = $post['content'];

		$user = Home_user::get(['id'=>$uid,'is_del'=>0]);
		if(empty($user)){
        	return json(['flog'=>0, 'msg'=>'用户已被删除，无法添加交易记录','data'=>'']);
		}
		$money = $user['money'];

		$surplus_money = $money+$trade_money;
		
		$tradeRecord = array();
		$tradeRecord['userid'] = $uid; 
		$tradeRecord['trade_money'] = $trade_money; 
		$tradeRecord['surplus_money'] = $surplus_money; 
		$tradeRecord['content'] = $content; 
		$tradeRecord['ctime'] = time();
		$tradeRecord['utime'] = time();
        TradeRecord::create($tradeRecord);

        $userUpdate = array();
        $userUpdate['money'] = $surplus_money; 
        $userUpdate['utime'] = time(); 
        Home_user::where('id', $uid)->update($userUpdate); 

		return json(['flog'=>1, 'msg'=>'成功','data'=>'']);
	}

	public function index(){
        $get = input('get.');

        $phone = $get['phone'];
        $name = $get['name'];
        $id = $get['uid'];

    	$where = array();
        if(!empty($phone)){
            $where['phone'] = $phone; 
        }
        if(!empty($name)){
            $where['name'] = $name; 
        }
        if(empty($where)&&!empty($id)){
            $where['id'] = $id; 
        }

        $user = Home_user::get($where);
        $record = TradeRecord::where(['userid'=>$user['id']])->order('ctime','desc')->paginate(10, false);
        $page = $record->render();

        $data = array(
        		'user'=>$user,
                'record'=>$record,
                'page'=>$page,
                'get'=>$get,
            );
        return $this->view->fetch('index',$data);
	}

}