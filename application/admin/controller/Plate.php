<?php
namespace app\admin\controller;

use app\admin\model\Admin as Admin_user;
use app\admin\model\User as Home_user;
use app\admin\model\Cate as Admin_cate;
use app\admin\model\Plate as model_plate;
use think\Session;
use think\Config;

/**
* 论坛 版块 展示
**/
class Plate extends Common
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
        $is_del = ($post['is_del'])?$post['is_del']:'-1';

        $where = array();
        if(!empty($name)){
            $where['name'] = array('like','%'.$name.'%'); 
        }
        if($is_del!='-1'){ 
            $where['is_del'] = $is_del; 
        }

        $plates = model_plate::where($where)->order('ctime','desc')->paginate(10, false);
        $page = $plates->render();

        $cates = Admin_cate::all(['is_del'=>0]);
        $cates = objToArray($cates);

        $users = Home_user::all(['is_del'=>0]);
        $users = objToArray($users);

        $data = array(
                'plates'=>$plates,
                'page'=>$page,
                'post'=>$post,
                'cates'=>$cates,
                'users'=>$users,
            );
        return $this->view->fetch('index',$data);
    }


    public function isDelPlate(){
        $post = input('post.');
        $id = $post['id'];

        $user = model_plate::get(['id'=>$id]);
        $update = array();
        if(empty($user['is_del'])){
            $update['is_del'] = 1;
        }else if($user['is_del']==1){
           $update['is_del'] = 0;
        }
        model_plate::where('id', $id)->update($update);

        return json(['flog'=>1, 'msg'=>$update['is_del']]);
    }

    public function isCheckPlate(){
        $post = input('post.');
        $id = $post['id'];

        $user = model_plate::get(['id'=>$id]);
        $update = array();
        if(empty($user['is_check'])||$user['is_check']==2){
            $update['is_check'] = 1;
        }else if($user['is_check']==1){
           $update['is_check'] = 2;
        }
        model_plate::where('id', $id)->update($update);

        return json(['flog'=>1, 'msg'=>$update['is_check']]);
    }

    public function isHomePlate(){
        $post = input('post.');
        $id = $post['id'];

        $user = model_plate::get(['id'=>$id]);
        $update = array();
        if(empty($user['is_home'])){
            $update['is_home'] = 1;
        }else if($user['is_home']==1){
           $update['is_home'] = 0;
        }
        model_plate::where('id', $id)->update($update);

        return json(['flog'=>1, 'msg'=>$update['is_home']]);
    }

    public function editPlate(){
        $get = input('get.');

        $PlateInfo = array();
        if(!empty($get['plateId'])){
            $plateId = $get['plateId'];
            $PlateInfo =  model_plate::get(['id'=>$plateId]);
        }

        # 用户
        $userArr = Home_user::all(['is_del'=>0]);
        $userArr = objToArray($userArr);

        # 分类
        $parent_cates = Admin_cate::all(['is_del'=>0,'parent_id'=>0]);

        $cates = Admin_cate::all(['is_del'=>0]);
        $cates = objToArray($cates);

        $data = array(
                'PlateInfo'=>$PlateInfo,
                'parent_cates'=>$parent_cates,
                'cates'=>$cates,
                'userArr'=>$userArr
            );
        return $this->view->fetch('editPlate',$data);
    }

    public function findCate(){
        $post = input('post.');
        $pid = $post['pCate'];

        $pcate = Admin_cate::get(['is_del'=>0,'id'=>$pid]);
        if(empty($pcate)){
            return json(['flog'=>0, 'msg'=>'父分类错误']);
        }

        $cate = Admin_cate::where(['is_del'=>0,'parent_id'=>$pid])->select();
        if(empty($cate)){
            return json(['flog'=>0, 'msg'=>'该分类下没有子分类']);
        }

        return json(['flog'=>1, 'msg'=>'成功','data'=>$cate]);
    }

    public function savePlate(){
        $post = input('post.');    
        $plateId = $post['plateId'];

        if(empty($plateId)){

            $data = array();
            $data['name'] = $post['name'];
            $data['info'] = $post['info'];
            $data['userid'] = $post['userid'];
            $data['cateid'] = $post['cateid'];
            $data['is_check'] = 1;
            $data['is_del'] = 0;
            $data['ctime'] = time();
            $data['utime'] = time();

            $headimg = request()->file('headimg');
            $backimg = request()->file('backimg');
            if($headimg){
                $saveHeadPath = 'uploads'.DS.'forum'.DS.'headimg';
                $data['headimg'] = uploadFile($headimg,$saveHeadPath);
            }
            if($backimg){
                $saveBackPath = 'uploads'.DS.'forum'.DS.'backimg';
                $data['backimg'] = uploadFile($backimg,$saveBackPath);
            }

            $place = model_plate::insert($data); 
        }else{

            $headimg = request()->file('headimg');
            $backimg = request()->file('backimg');

            if($headimg){
                $saveHeadPath = 'uploads'.DS.'forum'.DS.'headimg';
                $data['headimg'] = uploadFile($headimg,$saveHeadPath);
            }
            if($backimg){
                $saveBackPath = 'uploads'.DS.'forum'.DS.'backimg';
                $data['backimg'] = uploadFile($backimg,$saveBackPath);
            }
            
            $data['info'] = $post['info'];
            $data['utime'] = time();

            $place = model_plate::where('id', $plateId)->update($data); 

        }

        $this->redirect('plate/index');
    }
}