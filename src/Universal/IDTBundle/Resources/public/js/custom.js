$(document).ready(function() {
	/*
	==HIDE COLLAPSE ON CLICK==
	*/
	$("#main-menu a").click(function(event) {
		$(".navbar-collapse").removeClass("in").addClass("collapse");
	});
	
	$("#header-top-menu a:not(.dropdown-toggle)").click(function(event) {
		$("#header-top-menu").removeClass("in").addClass("collapse");
	});
	
	
	
	
	
	/*
	==CHECKOUT TABS==
	*/
	$('.checkout-tabs .tab-pane [data-toggle="tab"]').click(function() {
		var id = $(this).attr('data-target').substring(1);
		$('.checkout-tabs .nav-tabs li').removeClass('active');
		$('.checkout-tabs .nav-tabs li a[href="#'+ id +'"]').closest('li').addClass('active');
	});
	
	
	
	//
	///*
	//==DATA TABLE==
	//*/
	//$('#table, #table1, #table2').dataTable();
	
});