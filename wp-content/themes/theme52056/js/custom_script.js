jQuery(window).load(function() {
	jQuery('.wpcf7-not-valid-tip').live('mouseover', function(){
		jQuery(this).fadeOut();
	});

	jQuery('.wpcf7-form input[type="reset"]').live('click', function(){
		jQuery('.wpcf7-not-valid-tip, .wpcf7-response-output').fadeOut();
	});
});