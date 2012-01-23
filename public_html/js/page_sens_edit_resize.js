/**
 * Skrypt do obsługi dopasowania edytora do wielkości przeglądarki.
 */
$(function(){
	fit_transcriber_to_screen();

	$(window).resize(function(){
		fit_transcriber_to_screen();
	});
});

/**
 * Funkcja dopasowuje ekran transkprycji do wielkości przeglądarki.
 * @return
 */
function fit_transcriber_to_screen(){
	//var other_content_height = $(document).height() - $(".horizontal").outerHeight();
	var other_content_height = $("#main_menu").outerHeight();
	other_content_height += $("#sub_menu").outerHeight();
	other_content_height += $("#page_content .pagging").outerHeight();
	other_content_height += $("#page_content ul.ui-tabs-nav").outerHeight();
	other_content_height += $("#page_content h1").outerHeight();
	other_content_height += $("#footer").outerHeight();
	other_content_height += 90;

	var panel_height = $(window).height() - other_content_height;
	
	$(".sensTableContent").css("height", panel_height + "px");
	$(".sensDescriptionContainer").css("height", panel_height + "px");
	
}