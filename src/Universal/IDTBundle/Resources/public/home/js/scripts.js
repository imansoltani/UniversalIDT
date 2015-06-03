$(document).ready(function() {
	
	/*
	==CHECKOUT TABS==
	*/
	$('.checkout-tabs .tab-pane [data-toggle="tab"]').click(function() {
		var id = $(this).attr('data-target').substring(1);
		$('.checkout-tabs .nav-tabs li').removeClass('active');
		$('.checkout-tabs .nav-tabs li a[href="#'+ id +'"]').closest('li').addClass('active');
	});
	
	
	
	
	
	
	
	
	/*
	==CHECKOUT FORM==
	*/
	var rules = {
		rules: {
			agree: "required"
		},
		submitHandler: function() {
			alert('You are going to the payment page');
		}
	};

	var validationObj = $.extend (rules, Application.validationRules);

	$('#checkout-form').validate(validationObj);
	
	
	
	
	
	
	
	
	
	

	/*
	==FIXED HEADER==
	*/
	$("#main-menu a").click(function(event) {
		$(".navbar-collapse").removeClass("in").addClass("collapse");
	});
	
	$("#header-top-menu a:not(.dropdown-toggle)").click(function(event) {
		$("#header-top-menu").removeClass("in").addClass("collapse");
	});
	
	
	
	
	
	
	
	/*
	==SMOOTH SCROLL==
	*/
	$('#main-menu ul > li > a[href*=#]:not([href=#]), [data-toggle="target"]').click(function() {
		if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
			var target = $(this.hash);
			target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
			if (target.length) {
				$('html,body').animate({
					scrollTop: target.offset().top
				}, 1000);
				return false;
			}
		}
	});
	
	
	
	
	
	
	
	/*
	==CONTACT FORM VALIDATION==
	*/
	var rules = {
			rules: {
				name: {
					minlength: 2,
					required: true
				},
				email: {
					required: true,
					email: true
				},
				message: {
					minlength: 2,
					required: true
				}
			}
		};

	var validationObj = $.extend (rules, Application.validationRules);

	$('#contact-form').validate(validationObj);
	
	
	
	
	
	
	
	
	/*
	==STEPPER==
	*/
	$("input[type='number']").stepper();
	
	
	

	
	
	
	
	
	
	
	/*
	==INTRO SLIDER==
	*/
	$('.intro-slider').owlCarousel({
		autoPlay: true,
		rewindSpeed: 2000,
		singleItem: true,
		pagenation: true,
		itemsDesktop: [1000, 4], //5 items between 1000px and 901px
        itemsDesktopSmall: [900, 2], // betweem 900px and 601px
        itemsTablet: [600, 1], //2 items between 600 and 0
        itemsMobile: false // itemsMobile disabled - inherit from itemsTablet option
	});
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*
	==CALLING CARDS CAROUSEL==
	*/
	$(".calling-cards").owlCarousel({
		items: 4,
		navigation:true,
		navigationText: [
			"<i class='fa fa-angle-left'></i>",
			"<i class='fa fa-angle-right'></i>"
			],
		pagination: false,
		itemsDesktop: [1000, 4],
        itemsDesktopSmall: [990, 3],
        itemsTablet: [600, 1],
        itemsMobile: false
	});
	
	
	
	
	
	
	
	
	
	
	
	/*
	==CALLING CARDS COUNTRY LIST==
	*/
	$('.calling-card-countries-list .dropdown-menu li a').click(function() {
		var country_name = $(this).data('name');
		$('.country-name').text(country_name);
	});
	
	
	
	
	
	
	
	/*
	==CALLING CARDS MAP==
	*/
	map = new GMaps({
		div: '#map',
		zoom: 4,
		scrollwheel: false,
		disableDoubleClickZoom: true,
		lat: 50.935931,
		lng: 10.700684
	});
	
	map.addMarker({
		lat: 47.528329,
		lng: 13.930664,
		title: "Austria",
		icon: "../img/mapMarker.png",
		click: function(e) {
			$('.country-name').text('Austria');
		},
		infoWindow: {
			content: '<h5>Austria</h5><p><a href="#calling-card-by-country" data-toggle="target">Lorem ipsum dolor sit amet,<br> consectetuer adipiscing elit</a></p>'
		}
	});
	
	map.addMarker({
		lat: 51.189673,
		lng: 4.702148,
		title: "Belgium",
		icon: "../img/mapMarker.png",
		click: function(e) {
			$('.country-name').text('Belgium');
		},
		infoWindow: {
			content: '<h5>Belgium</h5><p><a href="#calling-card-by-country" data-toggle="target">Lorem ipsum dolor sit amet,<br> consectetuer adipiscing elit</a></p>'
		}
	});
	
	map.addMarker({
		lat: 55.686875,
		lng: 10.063477,
		title: "Denmark",
		icon: "../img/mapMarker.png",
		click: function(e) {
			$('.country-name').text('Denmark');
		},
		infoWindow: {
			content: '<h5>Denmark</h5><p><a href="#calling-card-by-country" data-toggle="target">Lorem ipsum dolor sit amet,<br> consectetuer adipiscing elit</a></p>'
		}
	});
	
	map.addMarker({
		lat: 46.690899,
		lng: 2.416992,
		title: "France",
		icon: "../img/mapMarker.png",
		click: function(e) {
			$('.country-name').text('France');
		},
		infoWindow: {
			content: '<h5>France</h5><p><a href="#calling-card-by-country" data-toggle="target">Lorem ipsum dolor sit amet,<br> consectetuer adipiscing elit</a></p>'
		}
	});
	
	map.addMarker({
		lat: 51.409486,
		lng: 10.151367,
		title: "Germany",
		icon: "../img/mapMarker.png",
		click: function(e) {
			$('.country-name').text('Germany');
		},
		infoWindow: {
			content: '<h5>Germany</h5><p><a href="#calling-card-by-country" data-toggle="target">Lorem ipsum dolor sit amet,<br> consectetuer adipiscing elit</a></p>'
		}
	});
	
	map.addMarker({
		lat: 43.655950,
		lng: 12.260742,
		title: "Italy",
		icon: "../img/mapMarker.png",
		click: function(e) {
			$('.country-name').text('Italy');
		},
		infoWindow: {
			content: '<h5>Italy</h5><p><a href="#calling-card-by-country" data-toggle="target">Lorem ipsum dolor sit amet,<br> consectetuer adipiscing elit</a></p>'
		}
	});
	
	map.addMarker({
		lat: 53.235633,
		lng: -8.569336,
		title: "Ireland",
		icon: "../img/mapMarker.png",
		click: function(e) {
			$('.country-name').text('Ireland');
		},
		infoWindow: {
			content: '<h5>Ireland</h5><p><a href="#calling-card-by-country" data-toggle="target">Lorem ipsum dolor sit amet,<br> consectetuer adipiscing elit</a></p>'
		}
	});
	
	map.addMarker({
		lat: 49.815273,
		lng: 6.129583,
		title: "Luxembourg",
		icon: "../img/mapMarker.png",
		click: function(e) {
			$('.country-name').text('Luxembourg');
		},
		infoWindow: {
			content: '<h5>Luxembourg</h5><p><a href="#calling-card-by-country" data-toggle="target">Lorem ipsum dolor sit amet,<br> consectetuer adipiscing elit</a></p>'
		}
	});
	
	map.addMarker({
		lat: 52.812723,
		lng: 6.108398,
		title: "Netherlands",
		icon: "../img/mapMarker.png",
		click: function(e) {
			$('.country-name').text('Netherlands');
		},
		infoWindow: {
			content: '<h5>Netherlands</h5><p><a href="#calling-card-by-country" data-toggle="target">Lorem ipsum dolor sit amet,<br> consectetuer adipiscing elit</a></p>'
		}
	});
	
	map.addMarker({
		lat: 62.070453,
		lng: 9.184570,
		title: "Norway",
		icon: "../img/mapMarker.png",
		click: function(e) {
			$('.country-name').text('Norway');
		},
		infoWindow: {
			content: '<h5>Norway</h5><p><a href="#calling-card-by-country" data-toggle="target">Lorem ipsum dolor sit amet,<br> consectetuer adipiscing elit</a></p>'
		}
	});
	
	map.addMarker({
		lat: 64.743674,
		lng: 16.567383,
		title: "Sweden",
		icon: "../img/mapMarker.png",
		click: function(e) {
			$('.country-name').text('Sweden');
		},
		infoWindow: {
			content: '<h5>Sweden</h5><p><a href="#calling-card-by-country" data-toggle="target">Lorem ipsum dolor sit amet,<br> consectetuer adipiscing elit</a></p>'
		}
	});
	
	map.addMarker({
		lat: 46.955887,
		lng: 7.844238,
		title: "Switzerland",
		icon: "../img/mapMarker.png",
		click: function(e) {
			$('.country-name').text('Switzerland');
		},
		infoWindow: {
			content: '<h5>Switzerland</h5><p><a href="#calling-card-by-country" data-toggle="target">Lorem ipsum dolor sit amet,<br> consectetuer adipiscing elit</a></p>'
		}
	});
	
	map.addMarker({
		lat: 55.637299,
		lng: -2.680664,
		title: "United Kingdom",
		icon: "../img/mapMarker.png",
		click: function(e) {
			$('.country-name').text('United Kingdom');
		},
		infoWindow: {
			content: '<h5>United Kingdom</h5><p><a href="#calling-card-by-country" data-toggle="target">Lorem ipsum dolor sit amet,<br> consectetuer adipiscing elit</a></p>'
		}
	});

	
});