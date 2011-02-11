//alert("included!");
$(function(){
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
	
	$("tr.subsetGroup").click(function(){
		if ($(this).hasClass("showItem"))
			$(this).removeClass("showItem").nextUntil(".subsetGroup, .setGroup").hide();
		else  
			$(this).addClass("showItem").nextUntil(".subsetGroup, .setGroup").filter(".annotation_type").show();		
	});
	
	$("tr.setGroup").click(function(){
		if ($(this).hasClass("showItem"))
			$(this).removeClass("showItem").nextUntil(".setGroup").hide();
		else 
			$(this).addClass("showItem").nextUntil(".setGroup").filter(".subsetGroup").show();		
	});	
	
	$("li.annotation_item").click(function(){
		$links = $(this).children(".annotationItemLinks");
		if ($links.hasClass("showItem")){
			$links.removeClass("showItem").empty();			
		}
		else{
			corpusId = vars['corpus'];
			annotationText = $(this).children("span:last").text();
			annotationType = $(this).parents("tr").prev().find("a.toggle_simple").text();
			$.post("index.php", 
					{
						ajax : "annmap_get_report_links",
						id : corpusId,
						type : annotationType,
						text : annotationText
					}, 
					function(data) {
						$links.addClass("showItem").append("<pre>"+data.toSource()+"</pre>");
						//console.log(data);
					}, "json");			
		}
	});
});