// 弹框
function alertFun(body){
	var ButtonId = $('#ButtonId');
	var alertId = $('#alertId');

	alertId.html(body);
	ButtonId.css('display','');
}

// 加载
function loading(str){
	console.log(str);
	if(str=='show'){
		console.log(1);
		console.log($('#loadImg'));
		$('#loadImg').show();
	}else if(str=='hide'){
		console.log(2);
		$('#loadImg').hide();
	}
}

// 返回
function back(){
	loading('show');
	window.history.go(-1);
}

// 后台弹框
function adminAlert(body,title,save){
	if(save==undefined){
    	$('#saveChange').hide();
	}
    $('#myModalBody').html(body);
    $('#myModalLabel').html(title);

	$('#myModal').addClass('in').css('padding-right','17px').css('display','block');
	
}

function closeModal(){
	$('#myModal').removeClass('in').css('padding-right','').css('display','none');
} 



function loadingUrl(url){
	loading('show');
	window.location.href = url;
}