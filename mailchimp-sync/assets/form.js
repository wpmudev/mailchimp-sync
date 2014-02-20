jQuery(document).ready(function($) {

	var mailchimp_widget = {
		init: function() {
			// Hiding labels
			var inputs = $( '.incsub-mailchimp-form .incsub-mailchimp-field-wrap input' );

			$.each( inputs, function( index, val ) {
				var label = ( $(this).siblings('label'));				
				$(this).attr( 'placeholder', label.text() );
				label.hide();
			});

			// There could be more than one form in the same screen
			var mailchimp_forms = $( '.incsub-mailchimp-form' );

			if ( mailchimp_forms.length ) {
				mailchimp_forms.submit( function(e) {
					e.preventDefault();

					// Populating form data
					var elems = $(this).find('.incsub-mailchimp-field');

					var form_data = {};
					elems.each(function() {
					    form_data[ $(this).attr("name") ] = $(this).val();
					});

					form_data['nonce'] = mailchimp_form_captions.nonce;

				  	mailchimp_widget.submit_form( form_data, $(this).attr('id') );
				  	return false;
				});
			}
		},
		submit_form: function( form_data, form_id ) {
			var the_form = $('#' + form_id);
			
			var spinner = the_form
				.find( '.mailchimp-spinner' )
				.css('visibility', 'visible');

			$.ajax({
				url: mailchimp_form_captions.ajaxurl,
				type: 'POST',
				data: form_data
			})
			.done(function(return_data,xhr) {

				the_form
					.find('.incsub-mailchimp-error')
					.hide();

				if ( return_data.success ) {
					the_form.find('*').detach();
					var message_container = $('<p class="incsub-mailchimp-updated"></p>').text(return_data.data['message']).hide();
				}
				else {
					var message_container = $('<ul class="incsub-mailchimp-error"></ul>').hide();
					for ( var i = 0; i < return_data.data.length; i++ ) {
						message_container.append( '<li>' + return_data.data[i] + '</li>' );
					}
				}
				$('#' + form_id).prepend(message_container);
				message_container.slideDown();

				spinner.css( 'visibility', 'hidden' );
			});
			return false;
		}
	}

	mailchimp_widget.init();
});