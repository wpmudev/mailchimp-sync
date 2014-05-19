
jQuery(document).ready(function($) {
	var mailchimp_form = {
		init: function() {
			$('.incsub-mailchimp-form').submit( mailchimp_form.on_submit_form );
		},

		on_submit_form: function( e ) {
			e.preventDefault();
			var form_id = $(this).attr('id');

			var form_data = $( '#' + form_id ).serializeArray();
			var errors_container = $( '#' + form_id + ' .incsub-mailchimp-error' );
			var success_container = $( '#' + form_id + ' .incsub-mailchimp-updated' );
			var spinner = $( '#' + form_id ).find( '.mailchimp-spinner' );

			var ajax_data = {}
			for ( var i = 0; i < form_data.length -1; i++ ) {
				ajax_data[ form_data[i]['name'] ] = form_data[i]['value'];
			}

			errors_container.slideUp().find('*').detach();
			spinner.css('visibility', 'visible');

			$.ajax({
				url: mailchimp_form_captions.ajaxurl,
				type: 'post',
				data: ajax_data,
				dataType: 'json'
			})
			.done(function( data ) {

				spinner.css('visibility', 'hidden');

				if ( data.success ){
					success_container.slideDown();
					$( '#' + form_id ).find('*').not('.incsub-mailchimp-updated').detach();
				}
				else {
					for ( var i = 0; i < data.data.errors.length; i++ ) {
						var error = $('<li></li>').text( data.data.errors[i] );
						errors_container.append( error );
					}
					errors_container.slideDown();
				}



			});
			
			return false;
		}
	}

	mailchimp_form.init();
});