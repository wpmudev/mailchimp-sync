<?php

add_action( 'show_user_profile', 'mailchimp_show_user_profile' );
add_action( 'edit_user_profile', 'mailchimp_show_user_profile' );

function mailchimp_show_user_profile( $user ) {

	if ( is_multisite() && ! current_user_can( 'manage_network_users') )
		return;
	elseif ( ! is_multisite() && ! current_user_can( 'edit_users' ) )
		return;

	$subscribed = mailchimp_30_get_user_info( $user->user_email );
	$is_subscribed = $subscribed && ( $subscribed['status'] === 'subscribed' );
	?>
	<h3>MailChimp</h3>
	<table class="form-table">
		<tr>
			<th><label for="mailchimp_list"><?php _e( 'Subscribed to the current Mailchimp List', MAILCHIMP_LANG_DOMAIN ); ?></label></th>
			<td>
				<?php if ( $is_subscribed ): ?>
					<div class="mailchimp-yes mailchimp-notice">
						<p><?php _e( 'Yes' ); ?></p>
					</div>
				<?php else: ?>
					<div class="mailchimp-no mailchimp-notice">
						<p><?php _e( 'No' ); ?></p>
					</div>
				<?php endif; ?>
			</td>
		</tr>
	</table>
	<style>
		div.mailchimp-yes {
			color:#333;
			background:#7ad03a;
		}
		div.mailchimp-no {
			background:#dd3d36;
			color:white;
		}
		div.mailchimp-notice {
			padding:1px 12px;
			float:left;
		}
		div.mailchimp-notice p {
			margin: .5em 0;
			padding-top: 2px;
			padding-bottom:2px;

		}
	</style>
	<?php
}

