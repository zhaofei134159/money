<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
error_reporting(E_ERROR | E_WARNING | E_PARSE);
//curl
function httpRequest($url, $post_data='', $header=0, $timeout=15)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, $header);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	//post
	if($post_data)
	{
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	}
	$output = curl_exec($ch);
	curl_close($ch);

	return $output;
}


//读取配置
function read_conf($name = '',$config_path = 'config.php')
{
	if(empty($name)){
		return array();
	}

	$a = think\Config::load($config_path);
	return think\Config::get($name);
}

# 地址读配置
function url_read_conf($name,$config_path = 'urlConfig.php',$actionField='index')
{
	if(empty($name)){
		return array();
	}

	$urlConfig = think\Config::load(APP_PATH.'/index/'.$config_path,'',$actionField);
	return $urlConfig[$name];
}


//过滤参数
function clearhtml($str)
{
	return addslashes(htmlspecialchars(trim($str)));
}

function sprint_r($arr)
{
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}

//文本处理之中文汉字字符串转换为数组
function ch2arr($str)
{
    $length = mb_strlen($str, 'utf-8');
    $array = array();
    for ($i=0; $i<$length; $i++)  
        $array[] = mb_substr($str, $i, 1, 'utf-8');    
    return $array;
}


function str_em($str,$title)
{
    $length = mb_strlen($str, 'utf-8');
    $array = array();
    for ($i=0; $i<$length; $i++)  
        $array[] = mb_substr($str, $i, 1, 'utf-8');   
    $new = array();     
    foreach($array as $v)
	{	
		$new[$v] = '<b>'.$v.'</b>';
		
	}
	$title = strtr($title, $new);
	return $title;
}

# 对象转数组
function objToArray($obj,$str='id'){
	if($obj) {
        $obj = collection($obj)->toArray();
    }
    $arr = array();
    if(!empty($str)){
	    foreach($obj as $key=>$val){
	    	$arr[$val[$str]] = $val;
	    }
    }else{
    	$arr = $obj;
    }
    return $arr;
}

# 分类拆分
function cateSplit($cate){
	$arr = array();
	foreach($cate as $ct){
		if($ct['parent_id']==0){
			$arr[$ct['id']] = $ct;
			$arr[$ct['id']]['son'] = array(); 
		}
		foreach($cate as $c){
			if($ct['id']==$c['parent_id']){
				$arr[$ct['id']]['son'][$c['id']] = $c;
			}
		}
	}
	return $arr;
}

# 上传图片
function uploadFile($file,$path){
	$filePath = '';
 	if($file){
        $fileInfo = $file->move(ROOT_PATH.'public'.DS.$path);
        if($fileInfo){
           $filePath = $path.DS.$fileInfo->getSaveName();
        }else{
            Log::log($uid.' 前台上传forum 头像错误 ：'.$file->getError());
        }
    }

    return $filePath;
}

# 时间展示 
function tranTime($time)
{
    $rtime = date("Y-m-d H:i",$time);
    $htime = date("H:i",$time);
          
    $time = time() - $time;
    if ($time < 60){
        $str = '刚刚';
    }elseif ($time < 60 * 60){
        $min = floor($time/60);
        $str = $min.'分钟前';
    }elseif ($time < 60 * 60 * 24){
        $h = floor($time/(60*60));
        $str = $h.'小时前 '.$htime;
    }elseif ($time < 60 * 60 * 24 * 3){
        $d = floor($time/(60*60*24));
        if($d==1){
            $str = '昨天 '.$htime;
        }else{
            $str = '前天 '.$htime;
        }
    }else{
        $str = $rtime;
    }
    return $str;
}