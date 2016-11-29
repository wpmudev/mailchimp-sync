<?php
/**
 * @author: WPMUDEV, Ignacio Cruz (igmoweb)
 * @version:
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the Mailchimp API
 *
 * @return Mailchimp Object
 */
function mailchimp_load_API() {
	_deprecated_function( __FUNCTION__, '1.9', 'mailchimp_load_api_30()' );
	global $mailchimp_sync;

	if ( ! empty( $mailchimp_sync->api ) )
		return $mailchimp_sync->api;

	require_once( 'mailchimp-api-2.0/mailchimp-api-2.0.php' );
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

	try {
		$api = new WPMUDEV_Mailchimp_Sync_API_20( $mailchimp_apikey, $options );
	}
	catch ( Exception $e ) {
		return new WP_Error( $e->getCode(), $e->getMessage() );
	}

	// Pinging the server
	$ping = $api->helper->ping();

	if ( is_wp_error( $ping ) )
		return $ping;

	$mailchimp_sync->api = $api;

	return $api;
}

/**
 * Get the lists of a Mailchimp account
 *
 * @return array Lists info
 * @deprecated
 */
function mailchimp_get_lists() {
	_deprecated_function( __FUNCTION__, '1.9', 'mailchimp_30_get_lists()' );
	return mailchimp_30_get_lists();
}

/**
 * @param $list_id
 * @deprecated
 * @return array
 */
function mailchimp_get_list_groups( $list_id ) {
	_deprecated_function( __FUNCTION__, '1.9', 'mailchimp_30_get_list_groups()' );
	return mailchimp_30_get_list_groups( $list_id );
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
 * @deprecated
 */
function mailchimp_subscribe_user( $user_email, $list_id, $autopt = false, $merge = array(), $update = false ) {
	_deprecated_function( __FUNCTION__, '1.9', 'mailchimp_30_subscribe_user()' );
	$api = mailchimp_load_API();

	if ( ! $update ) {
		return mailchimp_30_subscribe_user( $user_email, $list_id, array( 'merge_fields' => $merge, 'autopt' => $autopt ) );
	}
	else {
	}
}

/**
 * Check if a user is subscribed in the list
 *
 * @param String $user_email
 * @param String $list_id
 * @return bool. True if the user is subscribed already to the list
 * @deprecated
 */
function mailchimp_is_user_subscribed( $user_email, $list_id = '' ) {
	_deprecated_function( __FUNCTION__, '1.9', 'mailchimp_30_is_user_subscribed()' );
	return mailchimp_30_is_user_subscribed( $user_email, $list_id );
}

/**
 * Unsubscribe a user from a list
 *
 * @param String $user_email
 * @param String $list_id
 * @param Boolean $delete True if the user is gonna be deleted from the list (not only unsubscribed)
 */
function mailchimp_unsubscribe_user( $user_email, $list_id, $delete = false ) {
	_deprecated_function( __FUNCTION__, '1.9', 'mailchimp_30_unsubscribe_user()' );
	return mailchimp_30_unsubscribe_user( $user_email, $list_id, $delete );
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
 * @deprecated
 */
function mailchimp_update_user( $user_email, $list_id, $merge_vars ) {
	_deprecated_function( __FUNCTION__, '1.9', 'mailchimp_30_update_user()' );
	return mailchimp_30_update_user( $user_email, $list_id, array( 'merge_fields' => $merge_vars ) );
}

/**
 * Return the groups that the user has selected in Settings
 *
 * @return array Array of groups
 * @deprecated
 */
function mailchimp_get_interest_groups() {
	_deprecated_function( __FUNCTION__, '1.9', 'mailchimp_30_get_interest_groups()' );
	$mailchimp_mailing_list = get_site_option( 'mailchimp_mailing_list', '' );
	$groups = get_site_option( 'mailchimp_groups', array() );

	$vars = array();
	$merge_groups = array();
	if ( ! empty( $groups[ $mailchimp_mailing_list ] ) ) {

		foreach ( $groups[ $mailchimp_mailing_list ] as $group_id => $subgroups ) {
			if ( is_array( $subgroups ) && ! empty( $subgroups ) ) {
				$merge_groups[] = array(
					'id' => $group_id,
					'groups' => $subgroups
				);
			}
			elseif ( ! empty( $subgroups ) ) {
				$merge_groups[] = array(
					'id' => $group_id,
					'groups' => array( $subgroups )
				);
			}
		}

		$vars = $merge_groups;
	}

	return $vars;
}

/**
 * Return user data from a list
 *
 * @param String $user_email
 * @param String $list_id
 * @return Array User data / False if the user do not exist
 * @deprecated
 */
function mailchimp_get_user_info( $user_email, $list_id ) {
	_deprecated_function( __FUNCTION__, '1.9', 'mailchimp_30_get_user_info()' );
	return mailchimp_30_get_user_info( $user_email, $list_id );
}

function mailchimp_bulk_subscribe_users( $emails, $list_id, $autopt = false, $update = false ) {
	_deprecated_function( __FUNCTION__, '1.9', 'mailchimp_30_bulk_subscribe_users()' );
	return mailchimp_30_bulk_subscribe_users( $emails );
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