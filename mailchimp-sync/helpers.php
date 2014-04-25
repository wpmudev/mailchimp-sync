<?php

/**
 * Load the Mailchimp API
 * 
 * @return Mailchimp Object
 */
function mailchimp_load_API() {
	require_once( 'mailchimp-api/Mailchimp.php' );
	$mailchimp_apikey = get_site_option('mailchimp_apikey');

	$options = array(
		'timeout' => apply_filters( 'mailchimp_sync_api_timeout', false )
	);

	$ssl_verifypeer = apply_filters( 'mailchimp_sync_api_ssl_verifypeer', false );
	if ( $ssl_verifypeer ) {
		$options['ssl_verifypeer'] = $ssl_verifypeer;
	}

	$ssl_verifyhost = apply_filters( 'mailchimp_sync_api_ssl_verifyhost', false );
	if ( $ssl_verifyhost ) {
		$options['ssl_verifyhost'] = $ssl_verifyhost;
	}

	$ssl_cainfo = apply_filters( 'mailchimp_sync_api_ssl_cainfo', false );
	if ( $ssl_cainfo ) {
		$options['ssl_cainfo'] = $ssl_cainfo;
	}

	$debug = apply_filters( 'mailchimp_sync_api_debug', false );
	if ( $debug ) {
		$options['debug'] = $debug;
	}

	$api = new Mailchimp( $mailchimp_apikey, $options );

	// Pinging the server
	$ping = $api->helper->ping();

	if ( is_wp_error( $ping ) )
		return $ping;

	return $api;
}

/**
 * Subscribe a user to a Mailchimp list
 * 
 * @param String $user_email 
 * @param String $list_id 
 * @param Boolean $autopt 
 * @param Array $extra Extra data
 		Array(
			'FNAME' => First name,
			'LNAME' => Last Name
 		)
 * @return Array Result from the server
 */
function mailchimp_subscribe_user( $user_email, $list_id, $autopt = false, $merge = array(), $update = false ) {

	$api = mailchimp_load_API();

	if ( is_wp_error( $api ) )
		return $api;

	$merge_vars = array();
	if ( $autopt ) {
		$merge_vars['optin_ip'] = $_SERVER['REMOTE_ADDR'];
		$merge_vars['optin_time'] = current_time( 'mysql', true );
	} 

	if ( ! empty( $merge ) ) {
		$merge_vars = array_merge( $merge_vars, $merge );
	}

	return $api->lists->subscribe( $list_id, array( 'email' => $user_email ), $merge_vars, 'html', ! $autopt, $update );
	
}

/**
 * Subscribe a list of users
 * @param Array $emails 
	Array(
		array(
			'email' => array(
				'email' => Email
			)
			'merge_vars' => array(
				'FNAME' => First name,
				'LNAME' => Last name
			)
		),
		...
	) 
 * @param String $list_id 
 * @param Boolean $autopt 
 * @param Array $merge Array of merge vars
 * @return type
 */
function mailchimp_bulk_subscribe_users( $emails, $list_id, $autopt = false, $update = false ) {
	$api = mailchimp_load_API();

	if ( is_wp_error( $api ) )
		return $api;

	$merge_vars = array();
	if ( $autopt ) {
		$merge_vars['optin_ip'] = $_SERVER['REMOTE_ADDR'];
		$merge_vars['optin_time'] = current_time( 'mysql', true );
	} 

	$results = $api->lists->batchSubscribe( $list_id, $emails, ! $autopt, $update );

	$return = array();
	$return['added'] = $results['adds'];
	$return['updated'] = $results['updates'];
	$return['errors'] = array();

	if ( $results['error_count'] ) {
		foreach( $results['errors'] as $error ) {
			$return['errors'][] = new WP_Error( $error['code'], '{' . $error['email']['email'] . '} ' . $error['error'] );
		}
	}

	return $return;

}

/**
 * Unsubscribe a list of users
 * @param Array $emails 
	Array(
		array(
			'email' => Email
		),
		...
	) 
 * @param String $list_id 
 * @param Boolean $autopt 
 * @param Array $merge Array of merge vars
 * @return type
 */
function mailchimp_bulk_unsubscribe_users( $emails, $list_id, $delete = false ) {
	$api = mailchimp_load_API();

	if ( is_wp_error( $api ) )
		return $api;

	$results = $api->lists->batchUnsubscribe( $list_id, $emails, $delete );

	$return = array();
	$return['success_count'] = $results['success_count'];
	$return['errors'] = array();

	if ( $results['error_count'] ) {
		foreach( $results['errors'] as $error ) {
			$return['errors'][] = new WP_Error( $error['code'], '{' . $error['email']['email'] . '} ' . $error['error'] );
		}
	}

	return $return;

}

/**
 * Unsubscribe a user from a list
 * 
 * @param String $user_email 
 * @param String $list_id 
 * @param Boolean $delete True if the user is gonna be deleted from the list (not only unsubscribed)
 */
function mailchimp_unsubscribe_user( $user_email, $list_id, $delete = false ) {

	$api = mailchimp_load_API();

	if ( is_wp_error( $api ) )
		return $api;

	return $api->lists->unsubscribe( $list_id, array( 'email' => $user_email ), $delete );
}

/**
 * Update a user data in a list
 * @param String $user_email 
 * @param String $list_id 
 * @param Array $merge_vars
 	Array(
		'FNAME' => First name,
		'LNAME' => Last Name
	) 
 */
function mailchimp_update_user( $user_email, $list_id, $merge_vars ) {

	$api = mailchimp_load_API();

	if ( is_wp_error( $api ) )
		return $api;

	$merge_vars['update_existing'] = true;

	return $api->lists->updateMember( $list_id, array( 'email' => $user_email ), $merge_vars );
}

/**
 * Check if a user is subscribed in the list
 * 
 * @param String $user_email 
 * @param String $list_id 
 * @return Boolean. True if the user is subscribed already to the list
 */
function mailchimp_is_user_subscribed( $user_email, $list_id ) {
	if ( ! is_email( $user_email ) )
		return false;

	$api = mailchimp_load_API();

	if ( is_wp_error( $api ) )
		return $api;

	$emails = array(
		array( 'email' => $user_email )
	);

	$results = $api->lists->memberInfo( $list_id, $emails );

	if ( is_wp_error( $results ) )
		return $results;

	// The subscriber is not on the list
	if ( empty( $results['success_count'] ) )
		return false;

	// The subscriber is on the list but is not subscribed
	if ( $results['data'][0]['status'] != 'subscribed' )
		return false;

	return true;
}



/**
 * Return user data from a list
 * 
 * @param String $user_email 
 * @param String $list_id 
 * @return Array User data / False if the user do not exist
 */
function mailchimp_get_user_info( $user_email, $list_id ) {
	if ( ! is_email( $user_email ) )
		return false;

	$api = mailchimp_load_API();

	if ( is_wp_error( $api ) )
		return false;

	$emails = array(
		array( 'email' => $user_email )
	);

	$results = $api->lists->memberInfo( $list_id, $emails );

	if ( is_wp_error( $results ) )
		return false;

	// The subscriber is not on the list
	if ( empty( $results['success_count'] ) )
		return false;

	return $results;
}

/**
 * Get the lists of a Mailchimp account
 * 
 * @return Array Lists info
 */
function mailchimp_get_lists() {
	$api = mailchimp_load_API();

	if ( is_wp_error( $api ) )
		return array();

	$lists = $api->lists->getList();

	if ( is_wp_error( $lists ) )
		return array();

	if ( isset( $lists['data'] ) )
		return $lists['data'];

	return array();
}

