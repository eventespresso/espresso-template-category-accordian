	jQuery(function ($) {

		$('#espresso_accordion ul ul').toggle()

		$('#espresso_accordion > ul > li > h2 > a').click(function(e) {
			e.preventDefault();

			$('#espresso_accordion li').removeClass('active');
			$(this).closest('li').addClass('active');	
			var checkElement = $(this).parent().next();
			if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
				$(this).closest('li').removeClass('active');
				checkElement.slideUp('normal');
			}
			if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
				$('#espresso_accordion ul ul:visible').slideUp('normal');
				checkElement.slideDown('normal');
			}
			if($(this).closest('li').find('ul').children().length == 0) {
				return true;
			} else {
				return false;	
			}		
		});
	});