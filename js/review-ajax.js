jQuery(document).ready(function($) {
	$( '#form-review' ).submit( function( e ) {

		//Get the form data
		var formData = {
			'movie_name'	: $(document.getElementById('movie_name')).val(),
			'movie_rating': $(document.getElementById('movie_rating')).val(),
			'user_review'	: $(document.getElementById('user_review')).val()
		};
		//process ajax
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: review_ajax.ajaxurl,
			data: {
				action : 'review_save_ajax',
				data : formData,
				submission : $('#xyq').val(),
				security: review_ajax.security
			},
			success: function(response) {
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





