jQuery(document).ready(function($) {
	$( '#form-review' ).submit( function( e ) {

		//Get the form data
		var formData = {
			'name'	: $('input[name=movie-name]').val(),
			'rating': $('input[name=movie-rating]').val(),
			'review'	: $('input[name=user-review]').val()
		};
		//process ajax
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: review_ajax.ajaxurl
			data: {
				action : 'review_save_ajax',
				data : formData,
				submission : $('.review-submitted').val(),
				security: review_ajax.security
			},
			sucess: function(response) {
				if ( true === response.success) {
					alert('this was a success');
				} else {
					alert('you suck.');
				}
			},
			error: function(xhr,textStatus,e) {

			}
		});
	});
});





