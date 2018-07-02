<?php 
namespace app\admin\model;

use think\Db;
use think\Model;

class Common extends Model
{

	public static function get_query($sql,$arr=array()){
		if(empty($sql)){
			return array();
		}
		
		// 下面执行原生SQL操作
		$res = Db::query($sql,$arr);

		return $res;
	}

	public static function set_add($sql){
		if(empty($sql)){
			return 'error';
		}

		Db::execute($sql);
	}
}