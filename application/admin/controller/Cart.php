<?php
namespace app\admin\controller;

use app\admin\model\Admin as Admin_user;
use app\admin\model\User as Home_user;
use app\admin\model\Cate as Admin_cate;
use app\admin\model\Plate as Admin_plate;
use app\admin\model\Cart as Admin_cart;
use think\Session;
use think\Config;

/**
* 帖子 展示
**/
class Cart extends Common
{	
	public function __construct(){
		parent::__construct();
	   
        if(!Session::get('login_id','forum_admin')){
        	$this->redirect('login/login');
        }
	}
    
    public function index(){
        $get = input('get.');

        $where = array();
        if(!empty($get['title'])){
            $where['title'] = array('like','%'.$get['title'].'%'); 
        }
        if(isset($get['plateId'])&&$get['plateId']!='-1'){
            $where['plateId'] = $get['plateId'];
        }
        if(isset($get['is_del'])&&$get['is_del']!='-1'){ 
            $where['is_del'] = $get['is_del']; 
        }
        $where['cartId'] = 0;
        $carts = Admin_cart::where($where)->order('ctime','desc')->paginate(10, false);
        $page = $carts->render();
        // echo Admin_cart::getLastSql();

        $plates = Admin_plate::all(['is_del'=>0]);
        $plates = objToArray($plates);

        $users = Home_user::all(['is_del'=>0]);
        $users = objToArray($users);

        $data = array(
                'carts'=>$carts,
                'page'=>$page,
                'plates'=>$plates,
                'users'=>$users,
                'get'=>$get,
            );
        return $this->view->fetch('index',$data);
    }

    public function isDelCart(){
        $post = input('post.');
        $id = $post['id'];

        $user = Admin_cart::get(['id'=>$id]);
        $update = array();
        if(empty($user['is_del'])){
            $update['is_del'] = 1;
        }else if($user['is_del']==1){
           $update['is_del'] = 0;
        }
        Admin_cart::where('id', $id)->update($update);

        return json(['flog'=>1, 'msg'=>$update['is_del']]);
    }

    public function SeeCart(){
        $get = input('get.');
        $cartId = $get['cartId'];

        $cart = Admin_cart::get(['id'=>$cartId]);

        $users = Home_user::all(['is_del'=>0]);
        $users = objToArray($users);

        $plate = Admin_plate::get(['is_del'=>0,'id'=>$cart['plateId']]);

        $replys = Admin_cart::where(['is_del'=>0,'cartId'=>$cartId])->order('ctime','asc')->paginate(9, false,[
                'query'=>['cartId'=>$cartId],
            ]);        
        $page = $replys->render();
        
        $data = array(
                'cart'=>$cart,
                'users'=>$users,
                'replys'=>$replys,
                'plate'=>$plate,
                'page'=>$page,
            );

        return $this->view->fetch('SeeCart',$data);
    }
}