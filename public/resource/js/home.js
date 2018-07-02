// 弹框
function alertFun(lable,body,footer){
	var modelLable=$('#custom-width-modalLabel');
	var modelBody=$('#custom-model-body');
	var modelFooter=$('#modelFooter');
	var modelSave=$('#modelSave');

	var alertBtn = $('#alert-btn');

	modelLable.html(lable);
	modelBody.html(body);
	if(!footer){
		modelFooter.css('display','none');
	}

	alertBtn.click();

}

