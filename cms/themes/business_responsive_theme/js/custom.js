/* --------------------------------------------- 

* Filename:     custom-style.js
* Version:      1.0.0 (2016-01-12)
* Website:      http://www.zymphonies.com
                http://www.freebiezz.com
* Description:  System JS
* Author:       Zymphonies Dev Team
                info@zymphonies.com

-----------------------------------------------*/

jQuery(document).ready(function($){

	//Main menu
	$('#main-menu').smartmenus();
	
	//Mobile menu toggle
	$('.navbar-toggle').click(function(){
		$('.region-primary-menu').slideToggle();
	});

	//Mobile dropdown menu
	if ( $(window).width() < 767) {
		$(".region-primary-menu li a:not(.has-submenu)").click(function () {
			$('.region-primary-menu').hide();
	    });
	}
});