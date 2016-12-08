<?php

include_once( 'mailchimp-api/3.0/mailchimp-api-3.0.php' );

/**
 * Make a ping to MailChimp API
 *
 * @return true|WP_Error
 */
function mailchimp_30_ping() {
	$api = mailchimp_load_api_30();

	if ( is_wp_error( $api ) ) {
		return $api;
	}

	$ping = mailchimp_api_30_make_request( 'get', 'lists' );
	return ! is_wp_error( $ping );
}


/**
 * Subscribe a user to a Mailchimp list
 * 
 * @param string $user_email
 * @param string $list_id
 * @param array $options extra data
 * [
 *      autopt          boolean
 *      merge_fields    array [
 *          FNAME string First Name
 *          LNAME string Last Name
 *      ]
 *      interests       array       List of interests IDs with boolean values
 *
 * ]
 * @return array|WP_Error Result from the server
 */
function mailchimp_30_subscribe_user( $user_email, $list_id = '', $options = array() ) {
	$defaults = array(
		'autopt' => false,
		'merge_fields' => array(),
		'interests' => array()
	);
	$options = wp_parse_args( $options, $defaults );

	if ( ! $list_id ) {
		$list_id = get_site_option( 'mailchimp_mailing_list' );
	}

	if ( ! $list_id ) {
		return new WP_Error( 'missing-list-id', __( 'A list is not specified', 'mailchimp' ) );
	}

	$args = array(
		'email_address' => $user_email
	);

	if ( $options['autopt'] ) {
		$args['status'] = 'subscribed';
		$args['ip_opt'] = $_SERVER['REMOTE_ADDR'];
	}
	else {
		$args['status'] = 'pending';
	}

	if ( $options['merge_fields'] ) {
		$args['merge_fields'] = (object)$options['merge_fields'];
	}

	if ( $options['interests'] ) {
		$args['interests'] = (object)$options['interests'];
	}

	$result = mailchimp_api_30_make_request( 'post', "lists/$list_id/members", $args );

	return $result;
}

/**
 * Check if a user is subscribed in the list
 *
 * @param String $user_email
 * @param String $list_id
 * @return bool True if the user is subscribed already to the list
 */
function mailchimp_30_is_user_subscribed( $user_email, $list_id = '' ) {
	if ( ! $list_id ) {
		$list_id = get_site_option( 'mailchimp_mailing_list' );
		if ( ! $list_id ) {
			return false;
		}
	}

	$hash = md5( strtolower( $user_email ) );
	$results = mailchimp_api_30_make_request( 'get', "lists/$list_id/members/$hash" );

	return ! is_wp_error( $results );
}

/**
 * Unsubscribe a user from a list
 *
 * @param String $user_email
 * @param String $list_id
 * @param Boolean $delete True if the user is gonna be deleted from the list (not only unsubscribed)
 *
 * @return bool|array
 */
function mailchimp_30_unsubscribe_user( $user_email, $list_id = '', $delete = false ) {
	if ( ! $list_id ) {
		$list_id = get_site_option( 'mailchimp_mailing_list' );
		if ( ! $list_id ) {
			return false;
		}
	}

	if ( ! $delete ) {
		$result = mailchimp_30_update_user( $user_email, $list_id, array( 'status' => 'unsubscribed' ) );
	}
	else {
		$hash = md5( strtolower( $user_email ) );
		$result = mailchimp_api_30_make_request( 'delete', "lists/$list_id/members/$hash" );
	}

	return ! is_wp_error( $result );
}

/**
 * Update a user data in a list
 *
 * @param string $user_email
 * @param string $list_id
 * @param array $options extra data
 * [
 *      status          string subscribed|unsubscribed|cleaned|pending
 *      merge_fields    array [
 *          FNAME string First Name
 *          LNAME string Last Name
 *      ]
 *      interests       array       List of interests IDs with boolean values
 * ]
 *
 * @return array|bool New user data
 */
function mailchimp_30_update_user( $user_email, $list_id, $options ) {
	if ( ! $list_id ) {
		$list_id = get_site_option( 'mailchimp_mailing_list' );
		if ( ! $list_id ) {
			return false;
		}
	}

	$defaults = array(
		'merge_fields' => array(),
		'status' => false,
		'email_address' => false
	);
	$options = wp_parse_args( $options, $defaults );

	$hash = md5( strtolower( $user_email ) );

	$args = array();
	if ( $options['merge_fields'] ) {
		$args['merge_fields'] = $options['merge_fields'];
	}

	if ( $options['email_address'] ) {
		$args['email_address'] = $options['email_address'];
	}

	if ( $options['status'] ) {
		$args['status'] = $options['status'];
	}

	$result = mailchimp_api_30_make_request( 'patch', "lists/$list_id/members/$hash", $args );
	return $result;
}

/**
 * Return user data from a list
 *
 * @param String $user_email
 * @param String $list_id
 * @return array|bool User data / False if the user do not exist
 */
function mailchimp_30_get_user_info( $user_email, $list_id = '' ) {
	if ( ! $list_id ) {
		$list_id = get_site_option( 'mailchimp_mailing_list' );
		if ( ! $list_id ) {
			return false;
		}
	}

	$hash = md5( strtolower( $user_email ) );

	$result = mailchimp_api_30_make_request( 'get', "lists/$list_id/members/$hash" );
	if ( ! is_wp_error( $result ) ) {
		return $result;
	}
	return false;
}

/**
 * Subscribe a list of users
 * @param array $data List of users and their data
 * [
 *      user_email      string      User email
 *      options         array       List of options.
 *      [
 *	       autopt          boolean
 *	       merge_fields    array [
 *	           FNAME string First Name
 *	           LNAME string Last Name
 *	       ]
 *	       interests       array       List of interests IDs with boolean values
 *
 *	  ]
 * ]
 * @return WP_Error|array
 */
function mailchimp_30_bulk_subscribe_users( $data, $list_id = '' ) {
	if ( ! $list_id ) {
		$list_id = get_site_option( 'mailchimp_mailing_list' );
	}

	if ( ! $list_id ) {
		return new WP_Error( 'missing-list-id', __( 'A list is not specified', 'mailchimp' ) );
	}

	$path = "lists/$list_id/members";
	$method = 'post';

	$defaults = array(
		'autopt' => false,
		'merge_fields' => array(),
		'interests' => array()
	);

	$operations = array();
	foreach ( $data as $row ) {
		$row_options = wp_parse_args( $row, $defaults );

		$operation = array(
			'path' => $path,
			'method' => $method
		);

		$operation_args = array(
			'email_address' => $row['user_email']
		);

		if ( $row_options['autopt'] ) {
			$operation_args['status'] = 'subscribed';
			$operation_args['ip_opt'] = $_SERVER['REMOTE_ADDR'];
		}
		else {
			$operation_args['status'] = 'pending';
		}

		if ( $row_options['merge_fields'] ) {
			$operation_args['merge_fields'] = (object)$row_options['merge_fields'];
		}

		if ( $row_options['interests'] ) {
			$operation_args['interests'] = (object)$row_options['interests'];
		}

		$operation['args'] = $operation_args;
		$operations[] = $operation;
	}

	$result = mailchimp_api_30_make_batch_request( $operations );

	return $result;
}

/**
 * UnSubscribe a list of users
 *
 * @param array $emails List of emails to unsubscribe
 *
 * @return WP_Error|array
 */
function mailchimp_30_bulk_unsubscribe_users( $emails, $list_id = '', $delete = false ) {
	if ( ! $list_id ) {
		$list_id = get_site_option( 'mailchimp_mailing_list' );
	}

	if ( ! $list_id ) {
		return new WP_Error( 'missing-list-id', __( 'A list is not specified', 'mailchimp' ) );
	}

	$operations = array();
	foreach ( $emails as $email ) {
		$hash = md5( strtolower( $email ) );
		if ( $delete ) {
			$operations[] = array(
				'path' => "lists/$list_id/members/$hash",
				'method' => 'delete'
			);
		}
		else {
			$operations[] = array(
				'path' => "lists/$list_id/members/$hash",
				'method' => 'patch',
				'args' => array(
					'status' => 'unsubscribed'
				)
			);
		}
	}

	return mailchimp_api_30_make_batch_request( $operations );
}
//
//
//add_action( 'admin_init', function() {
//	if ( isset( $_GET['test'] ) ) {
//		$interests = mailchimp_30_get_interest_groups();
//		$result = mailchimp_30_bulk_subscribe_users(array(
//			array(
//				'user_email' => 'ignacio.incsub@gmail.com',
//				'autopt' => true,
//				'merge_fields' => array( 'FNAME' => 'Ignacio', 'LNAME' => 'Cruz' ),
//				'interests' => $interests
//			),
//			array(
//				'user_email' => 'ignacio@incsub.com',
//				'autopt' => false,
//				'merge_fields' => array( 'FNAME' => 'IgnacioI', 'LNAME' => 'CruzI' ),
//				'interests' => $interests
//			),
//		));
////		$result = mailchimp_30_subscribe_user( 'ignacio.incsub@gmail.com', '', array( 'interests' => $interests, 'autopt' => true, 'merge_fields' => array( 'FNAME' => 'ignacio', 'LNAME' => 'cruz' ) ) );
////		$result = mailchimp_30_update_user( 'ignacio@incsub.com', '', array( 'merge_fields' => array( 'FNAME' => 'Yeah', 'LNAME' => 'Lala' ) ) );
////		$result = mailchimp_30_get_user_info( 'ignacio@incsub.com' );
////		$result = mailchimp_30_unsubscribe_user( 'ignacio@incsub.com' );
////		$result = mailchimp_30_unsubscribe_user( 'ignacio@incsub.com', '', true );
//	}
//});



/**
 * Get the lists of a Mailchimp account
 *
 * @return array Lists info
 */
function mailchimp_30_get_lists() {
	$lists = mailchimp_api_30_make_request( 'get', 'lists' );
	if ( ! is_wp_error( $lists ) ) {
		return $lists['lists'];
	}
	return array();
}


function mailchimp_30_get_list_groups( $list_id ) {
	$api = mailchimp_load_api_30();

	if ( is_wp_error( $api ) ) {
		return array();
	}

	$cached = get_site_transient( 'mailchimp_list_groups_' . $list_id );
	if ( $cached ) {
		return $cached;
	}
	$groups = mailchimp_api_30_make_request( 'get', "/lists/$list_id/interest-categories" );

	if ( ! is_wp_error( $groups ) ) {
		set_site_transient( 'mailchimp_list_groups_' . $list_id, $groups['categories'], 60 ); // Save for 60 seconds
		return $groups['categories'];
	}

	return array();
}


function mailchimp_30_get_category_interests( $list_id, $category_id ) {
	$api = mailchimp_load_api_30();

	if ( is_wp_error( $api ) ) {
		return array();
	}

	$cached = get_site_transient( 'mailchimp_category_interests_' . $list_id . '_' . $category_id );
	if ( $cached ) {
		return $cached;
	}

	$interests = mailchimp_api_30_make_request( 'get', "/lists/$list_id/interest-categories/$category_id/interests" );

	if ( ! is_wp_error( $interests ) ) {
		set_site_transient( 'mailchimp_category_interests_' . $list_id . '_' . $category_id, $interests['interests'], 60 ); // Save for 60 seconds
		return $interests['interests'];
	}

	return array();
}

/**
 * Return the groups that the user has selected in Settings
 * 
 * @return array Array of groups
 */
function mailchimp_30_get_interest_groups() {
	$mailchimp_mailing_list = get_site_option( 'mailchimp_mailing_list', '' );
	$groups = get_site_option( 'mailchimp_groups', array() );

	if ( ! isset( $groups[ $mailchimp_mailing_list ] ) ) {
		return array();
	}
	$groups = $groups[ $mailchimp_mailing_list ];

	$interests = array();
	foreach ( $groups as $group_id => $group_value ) {
		if ( is_array( $group_value ) ) {
			foreach ( $group_value as $key => $interest_id ) {
				$interests[$interest_id] = true;
			}
		}
		elseif ( ! empty( $group_value ) ) {
			$interests[$group_value] = true;
		}
	}

	return $interests;
}

function mailchimp_get_webhooks_settings() {
	return wp_parse_args( get_site_option( 'mailchimp_webhooks_settings', array() ), mailchimp_get_webhooks_default_settings() );
}

function mailchimp_get_webhooks_default_settings() {
	return array(
		'webhook_key' => '',
		'write_log' => false,
		'delete_user' => 'mark'
	);
}

function mailchimp_update_webhooks_settings( $new_settings ) {
	update_site_option( 'mailchimp_webhooks_settings', $new_settings );
}

function mailchimp_get_webhook_url() {
	return WPMUDEV_MailChimp_Sync_Webhooks_30::get_callback_url();
}

function mailchimp_set_webhooks_rewrite_rules() {
	WPMUDEV_MailChimp_Sync_Webhooks_30::add_rewrite_rules();
}

function mailchimp_is_webhooks_active() {
	return WPMUDEV_MailChimp_Sync_Webhooks_30::is_webhooks_active();
}
