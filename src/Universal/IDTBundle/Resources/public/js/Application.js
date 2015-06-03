$(function () {
	
	Application.init ();
	
});



var Application = function () {

	var validationRules = getValidationRules ();

	return { init: init, validationRules: validationRules };

	function init () {
		enableBackToTop ();
		enableEnhancedAccordion ();
	}

	function enableBackToTop () {
		var backToTop = $('<a>', { id: 'back-to-top', href: '#top' });
		var icon = $('<i>', { class: 'fa fa-chevron-up' });

		backToTop.appendTo ('body');
		icon.appendTo (backToTop);

	    backToTop.hide();

	    $(window).scroll(function () {
	        if ($(this).scrollTop() > 150) {
	            backToTop.fadeIn ();
	        } else {
	            backToTop.fadeOut ();
	        }
	    });

	    backToTop.click (function (e) {
	    	e.preventDefault ();

	        $('body, html').animate({
	            scrollTop: 0
	        }, 600);
	    });
	}

	function enableEnhancedAccordion () {
		$('.accordion-toggle').on('click', function (e) {
	         $(e.target).parent ().parent ().parent ().addClass('open');
	    });

	    $('.accordion-toggle').on('click', function (e) {
	        $(this).parents ('.panel').siblings ().removeClass ('open');
	    });

	}

	function getValidationRules () {
		return {
			focusCleanup: false,

			wrapper: 'div',
			errorElement: 'span',

			highlight: function (element) {
				$(element).parents('.form-group').removeClass('success').addClass('error');
			},
			success: function (element) {
				$(element).parents('.form-group').removeClass('error').addClass('success');
				$(element).parents('.form-group:not(:has(.clean))').find('div:last').before('<div class="clean"></div>');
			},
			errorPlacement: function (error, element) {
				error.prependTo(element.parents('.form-group'));
			}

		};
	}

}();