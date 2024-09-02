define([
    "jquery",
	'mage/translate'
], function ($, $t) { 
    'use strict';
    
    $.widget('mage.verticalMenu', {
        options: {
        },
        _create: function () {
			this.limitShow = this.options.limitShow;
			this.smallDesktop = this.options.smallDesktop;
			
			var screenWidth = $(window).width();
			var limitItemShow = this.limitShow;
			if(screenWidth <= 1550){
				var limitItemShow = this.smallDesktop;
			}
			var lenghtLi = $('.verticalmenu-html .ui-menu-item.level0').length;

            if(screenWidth > 1199){
                if(lenghtLi > limitItemShow)
                {
                    $('.verticalmenu-html .ui-menu-item.level0').each(function( index ) {
                        if(index > (limitItemShow-1))
                        {
                            $(this).addClass('orther-link');
                            $(this).hide();
                        }
                    });
                    $('.verticalmenu-html .expand-menu-link').show();
                }else{
                    $('.expand-menu-link').hide();
                }
            }else{
                $('.expand-menu-link').remove();
            }
            $(document).on('click', '.verticalmenu-html .expand-menu-link', function(){
                if($(this).hasClass("expanding")){
                    $(this).find('a').text($t('More Menus'));
                }else{
                    $(this).find('a').text($t('Hide Menus'));
                }
                $(this).toggleClass('expanding');
                $('.verticalmenu-html .ui-menu-item.level0.orther-link').slideToggle('slow');
                return false;
            });

            if ($('header').find('.show_vertical_drop-down').length) {
                $('.vertical-menu .verticalmenu-list').show();
                $('.vertical-menu .title-menu-dropdown').addClass('active');
            }
            
            if(!$("html").hasClass("nav-horizontal")){
                $(document).on('click', '.nav-toggle', function(){
                    $('.verticalmenu-html .verticalmenu-list').show();
                    if(!$("html").hasClass("nav-open")) {
                        $("html").addClass("nav-before-open");
                        if(!$("html").hasClass("nav-horizontal")){
                            $("html").addClass("nav-vertical-open");
                        }
                        $("html").addClass("nav-open");
                    } else {
                        $("html").removeClass("nav-open");
                        if(!$("html").hasClass("nav-vertical-open")){
                            $("html").removeClass("nav-vertical-open");
                        }
                        $("html").removeClass("nav-before-open");
                    }
                    
                    return false;
                }); 
                
                $(document).on('click', '.vertical-menu-container .close-menu', function(){
                    if(!$("html").hasClass("nav-horizontal")){
                        $("html").removeClass("nav-open");
                        $("html").removeClass("nav-vertical-open");
                        $("html").removeClass("nav-before-open");
                    }
                    return false;
                }); 
            } 
			
			$(document).on('mouseover', '.vertical-menu-container .title-menu-dropdown', function(event){
                event.preventDefault();
                if(!$(this).hasClass("active")) {
                    $(this).closest('.vertical-menu-container').find('.verticalmenu-list').slideDown(300);
                    $(this).addClass("active");
                }
                else {
                    $(this).closest('.vertical-menu-container').find('.verticalmenu-list').slideUp(300);
                    $(this).removeClass("active"); 
                }
                return false;
			});


            $('body').on('click', '.verticalmenu-list li.ui-menu-item .open-children-toggle', function(){
				if(!$(this).parent().hasClass("opened-is")) {
					$(this).parent().addClass("opened-is");
				} else {
					$(this).parent().removeClass("opened-is");
				}
			});
             
            $('body').on('click', '.verticalmenu-list li.ui-menu-item .back-main-menu', function(){
				$(this).closest('li.ui-menu-item').removeClass("opened-is");
			});

            $('body').on('click', '.verticalmenu-list .close-main-menu', function(){
				if($("html").hasClass("nav-open")) {
					$("html").removeClass("nav-open");
					$("html").removeClass("nav-before-open");
                    $("li.ui-menu-item").removeClass("opened-is");
				}
				return false;
			});
        },
    });
    return $.mage.verticalMenu;
});