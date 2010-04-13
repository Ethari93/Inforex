function status_set(text){
	$("#status_icon").hide();
	$("#status_text").html(text);
	$("#system_status").show();	
}

function status_processing(text){
	$("#status_icon").show();
	$("#status_text").html(text);
	$("#system_status").show();		
}

function status_hide(text){
	$("#system_status").hide();	
}

function status_fade(){
	$("#system_status").fadeOut("10000", function(){$("#status").hide()});
}