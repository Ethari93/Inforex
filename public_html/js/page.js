/**
 * Part of the Inforex project
 * Copyright (C) 2013 Michał Marcińczuk, Jan Kocoń, Marcin Ptak
 * Wrocław University of Technology
 */

var url = $.url(window.location.href);
var current_page = url.param('page');
var corpus_id = url.param('corpus');

$(function(){

    /**
     * \/\/\/ Makes it possible to stack bootstrap modals on top of each other.
     */
    $('.modal').on('hidden.bs.modal', function(event) {
        $(this).removeClass( 'fv-modal-stack' );
        $('body').data( 'fv_open_modals', $('body').data( 'fv_open_modals' ) - 1 );
    });

    $('.modal').on('shown.bs.modal', function (event) {
        // keep track of the number of open modals
        if ( typeof( $('body').data( 'fv_open_modals' ) ) == 'undefined' ) {
            $('body').data( 'fv_open_modals', 0 );
        }

        // if the z-index of this modal has been set, ignore.
        if ($(this).hasClass('fv-modal-stack')) {
            return;
        }

        $(this).addClass('fv-modal-stack');
        $('body').data('fv_open_modals', $('body').data('fv_open_modals' ) + 1 );
        $(this).css('z-index', 1040 + (10 * $('body').data('fv_open_modals' )));
        $('.modal-backdrop').not('.fv-modal-stack').css('z-index', 1039 + (10 * $('body').data('fv_open_modals')));
        $('.modal-backdrop').not('fv-modal-stack').addClass('fv-modal-stack');

    });
    /**
     * ^^^ Makes it possible to stack bootstrap modals on top of each other.
     */

    $('body .dropdown-toggle').dropdown();
    $(".corpora_collapse").hover(function(){
        $(".dropdown-menu-search").hide();
        $(".corpora_search_bar").val("");
    });

    $(".corpus_select_nav").click(function(){
        $(".dropdown-menu-search").hide();
        $(".corpora_search_bar").val("");
    });

    $(".corpora_search_bar").keyup(function () {
        var text = this.value.toLowerCase();
        var dropdown_menu = $(this).parent().find("ul");

        if(text.length >= 2){
            var data = {
                'match_text': text
            };

            var success = function(corpora){
                var list = "";
                if(current_page == 'report'){
                    var page_link = 'browse';
                } else{
                    var page_link = current_page;
                }

                $.each(corpora, function (index, value) {
                    list += '<li><a href="index.php?page='+page_link+'&amp;corpus='+value.corpus_id+'">'+value.name+'</a></li>';

                } );

                if(corpora.length > 0){
                } else{
                    list = "<li><p>No results.</p></li>";
                }

                $(dropdown_menu).html(list);

                $(dropdown_menu).css("display", "block");

            };

            doAjaxSync("corpus_get_corpora", data, success);

        } else{
            $(dropdown_menu).css("display", "none");
        }
    });

    $('body .dropdown-toggle').dropdown();
	//Bootstrap-style errors for jQuery Validation plugin
    $.validator.setDefaults({
        errorElement: "span",
        errorClass: "help-block" +
        "",
        highlight: function (element, errorClass, validClass) {
            $(element).closest('.form-group').addClass('has-error');
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).closest('.form-group').removeClass('has-error');
        },
        errorPlacement: function (error, element) {
            if (element.parent('.input-group').length || element.prop('type') === 'checkbox' || element.prop('type') === 'radio') {
                error.insertAfter(element.parent());
            } else {
                error.insertAfter(element);
            }
        }
    });

    $.validator.addMethod("notEqual", function(value, element, param) {
        return this.optional(element) || param != $(element).val();
    }, "This field cannot be empty");

    $.validator.addMethod(
        "regex",
        function(value, element, regexp) {
            var re = new RegExp(regexp);
            return this.optional(element) || re.test(value);
        },
        'This field can only contain letters, numbers and "_".'
    );

    //Changes the number of pages available in Datatables pagination
    // e.g. 1 ... 10 instead of 1,2,3,4,5 ... 10 when numbers_length = 3;
    //$.fn.DataTable.ext.pager.numbers_length = 5;

    //Resets fields on the bootstrap modals when they are closed
    $('.modal').on('hidden.bs.modal', function (e) {
        $(this)
            .find("input,textarea").not("[type=checkbox], .button")
            .val('')
            .removeClass('error')
            .removeAttr('aria-invalid')
            .removeAttr('aria-describedby')
            .end()
            .find("input[type=checkbox], input[type=radio]")
            .prop("checked", "")
            .end()
            .find("aria-invalid.false")
            .attr("aria-invalid","false")
            .end()
            .find("#annotation_type_preview")
            .removeAttr("style")
            .end()
            .find("label.error")
            .remove()
            .end()
            .find(".has-error")
            .removeClass("has-error")
            .end()
            .find("span.help-block")
            .remove()
            .end();
    })


    $("#menu_page li").hover(function(){
		if (!$(this).hasClass("expanded")){
			$(this).addClass("expanded");
			$("#menu_page li").show();			
		}	
	});
	
	$("#menu_page").mouseleave(function(){
		$("#menu_page .expanded").removeClass("expanded");
		$("#menu_page li").hide();
		$("#menu_page li.active").show();					
	});

    $(".nav_corpus_pages > a em").html($(".nav_corpus_pages li.active").text());

    $("#compact-mode").click(function(){
		$("#page").toggleClass("compact");
		$.cookie("compact_mode", $("#page").hasClass("compact") ? "1" : "0");
		if ( autoreizeFitToScreen && typeof autoreizeFitToScreen === 'function' ) {
            autoreizeFitToScreen();
        }
	});
});
