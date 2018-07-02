<?php
namespace app\admin\controller;

use app\admin\model\Admin as Admin_user;
use app\admin\model\User as Home_user;
use app\admin\model\Cate as Admin_cate;
use think\Session;
use think\Config;


class Cate extends Common
{	
	public function __construct(){
		parent::__construct();
	   
        if(!Session::get('login_id','forum_admin')){
        	$this->redirect('login/login');
        }
	}
    
    public function index(){

        $where = array();
        $where['is_del'] = 0;
        $where['parent_id'] = 0;
        $pcate = Admin_cate::all($where);
        $cateArr = array();
        $i = 0;
        $j = 0;
        foreach($pcate as $key=>$val){
            $cateArr[$key]['text'] = $val['name'];
            $cateArr[$key]['nodeId'] = $val['id'];
            $cateArr[$key]['parent_id'] = $val['parent_id'];
            $cateArr[$key]['nodes'] = array();

            $where = array();
            $where['is_del'] = 0;
            $where['parent_id'] = $val['id'];
            $scate = Admin_cate::all($where);
            if(empty($scate)){
                // $cateArr[$key]['nodes'][0]['text'] = '无';
                continue;
            }
            foreach($scate as $skey=>$cate){
                $cateArr[$key]['nodes'][$skey]['text'] = $cate['name'];
                $cateArr[$key]['nodes'][$skey]['nodeId'] = $cate['id'];
                $cateArr[$key]['nodes'][$skey]['parent_id'] = $cate['parent_id'];
            }
        }


        $data = array(
                'cateStr'=>json_encode($cateArr),
            );
        return $this->view->fetch('index',$data);
    }

    public function cateEdit(){
        $post = input('post.');

        $type = $post['type'];
        $selectCate = $post['selectCate'];

        $parentArr = array();
        $cateArr = array();
        if($type==1&&!empty($selectCate)){
            $parentArr = Admin_cate::get(['name'=>$selectCate,'is_del'=>0]);
            if(!empty($parentArr['parent_id'])){
                return json(['flog'=>0, 'data'=>'二级分类 下 无法创建 子分类']);
            }
        }else if($type==2){
            $cateArr = Admin_cate::get(['name'=>$selectCate,'is_del'=>0]);
            if(!empty($cateArr['parent_id'])){
                $parentArr = Admin_cate::get(['id'=>$cateArr['parent_id'],'is_del'=>0]);
            }
        }

        $data = array(
                'cateArr'=>$cateArr,
                'parentArr'=>$parentArr,
            );
        $html = $this->view->fetch('cate_edit',$data);

        return json(['flog'=>1, 'msg'=>'成功','data'=>$html]);
    }

    public function cateSave(){
        $post = input('post.');
        $parentId = $post['parentId'];
        $parentName = $post['parentName'];
        $cateName = $post['cateName'];
        $cateId = $post['cateId'];

        $isCateSet = Admin_cate::get(['name'=>$cateName,'is_del'=>0]);
        if(!empty($isCateSet)){
            return json(['flog'=>0, 'msg'=>$cateName.' 分类已经存在']);
        }

        $cate = Admin_cate::get(['id'=>$cateId]);

        if(!empty($cateId)&&!empty($cate)){
            $id = $cate['id'];
            $updateArr = array(
                    'parent_id'=>$parentId,                    
                    'name'=>$cateName,
                    'utime'=>time(),                  
                );
            Admin_cate::where('id', $id)->update($updateArr); 

            return json(['flog'=>1, 'msg'=>'分类修改成功']);
        }else{
            $cateArr = array();
            $cateArr['name'] = $cateName;
            $cateArr['parent_id'] = $parentId;
            $cateArr['ctime'] = time();
            $cateArr['utime'] = time();
            $cateId = Admin_cate::create($cateArr);

            return json(['flog'=>1, 'msg'=>'分类创建成功']);
        }
    }

    public function cateDel(){
        $post = input('post.');
        $selectCate = $post['selectCate'];

        $isCateSet = Admin_cate::get(['name'=>$selectCate,'is_del'=>0]);
        if(empty($isCateSet)){
            return json(['flog'=>0, 'msg'=>$selectCate.'分类错误']);
        }
        $SonCates =  Admin_cate::get(['parent_id'=>$isCateSet['id'],'is_del'=>0]); 
        if(!empty($SonCates)){
            return json(['flog'=>0, 'msg'=>$selectCate.'分类下 存在子类  不能删除']);
        }
        $updateArr = array(
                'is_del'=>1,                    
                'utime'=>time(),                  
            );
        Admin_cate::where('id', $isCateSet['id'])->update($updateArr); 

        return json(['flog'=>1, 'msg'=>'分类删除成功']);
    }
}