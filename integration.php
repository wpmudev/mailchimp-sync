<?php


if ( ! function_exists( 'is_plugin_active' ) )
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( is_plugin_active( 'wpmu-dev-facebook/wpmu-dev-facebook.php' ) ) {
	function mailchimp_ultimate_fb_remove_filters() {
		global $mailchimp_sync;

		remove_action( 'wpmu_new_user', array( $mailchimp_sync, 'mailchimp_add_user' ) );
		remove_action( 'user_register', array( $mailchimp_sync, 'mailchimp_add_user' ) );
		remove_action( 'make_ham_user', array( $mailchimp_sync, 'mailchimp_add_user' ) );
	}
	add_action( 'init', 'mailchimp_ultimate_fb_remove_filters' );

	function mailchimp_ultimate_fb_add_user( $uid, $registration, $me ) {
		global $mailchimp_sync;

		$user = get_userdata( $uid );
		$user_arr = array(
			'email' => $user->user_email,
			'first_name' => $me['first_name'],
			'last_name' => $me['last_name']
		);

		$mailchimp_sync->mailchimp_add_user( $user_arr );
	}
	add_action( 'wdfb-user_registered-postprocess', 'mailchimp_ultimate_fb_add_user', 10, 3 );
}