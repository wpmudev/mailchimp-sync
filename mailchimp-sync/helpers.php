<?php
function mailchimp_load_API() {
	require_once( 'mailchimp-api.php' );
	$mailchimp_apikey = get_site_option('mailchimp_apikey');
	$api = new MCAPI( $mailchimp_apikey );
	return $api;
}

