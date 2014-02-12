jQuery(document).ready(function($) {

	var mailchimp_widget = {
		init: function() {
			var mailchimp_widgets = $( '.incsub-mailchimp-widget' );

			if ( mailchimp_widgets.length ) {
				$( '.incsub-mailchimp-widget-form' ).submit( function(e) {
					e.preventDefault();

					var elems = $(this).find('.incsub-mailchimp-field');

					var form_data = {};
					elems.each(function() {
					    form_data[ $(this).attr("name") ] = $(this).val();
					});

					form_data['nonce'] = mailchimp_widget_captions.nonce;

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
				url: mailchimp_widget_captions.ajaxurl,
				type: 'POST',
				data: form_data
			})
			.done(function(return_data,xhr) {
				$('.mailchimp-widget-error').hide();
				if ( return_data.success ) {
					the_form.find('*').detach();
					var message_container = $('<p class="mailchimp-widget-updated"></p>').text(return_data.data['message']).hide();
				}
				else {
					var message_container = $('<ul class="mailchimp-widget-error"></ul>').hide();
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