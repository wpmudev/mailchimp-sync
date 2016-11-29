<?php
/*
Plugin Name: MailChimp Sync
Plugin URI: http://premium.wpmudev.org/project/mailchimp-newsletter-integration
Description: Simply integrate MailChimp with your Multisite (or regular old single user WP) site - automatically add new users to your email lists and import all your existing users
Author: WPMU DEV
Version: 1.9
Author URI: http://premium.wpmudev.org
Network: true
WDP ID: 73
*/

/* 
Copyright 2007-2013 Incsub (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


class WPMUDEV_MailChimp_Sync {

	public static $instance = null;

	public static $version = '1.9';

	public static function get_instance() {
		if ( ! self::$instance ) {
			return new self();
		}

		return self::$instance;
	}


	public function __construct() {
		$this->set_globals();
		$this->includes();

		add_action( 'plugins_loaded', array( $this, 'mailchimp_localization' ) );

		add_action( 'init', array( $this, 'init' ), 1 );
		add_action( 'admin_init', array( $this, 'maybe_upgrade' ), 1 );

		add_action( 'wpmu_new_user', array( $this, 'mailchimp_add_user' ) );
		add_action( 'user_register', array( $this, 'mailchimp_add_user' ) );
		add_action( 'make_ham_user', array( $this, 'mailchimp_add_user' ) );
		add_action( 'profile_update', array( $this, 'mailchimp_edit_user' ) );
		add_action( 'xprofile_updated_profile', array( $this, 'mailchimp_edit_user' ) ); //for buddypress

		add_action( 'make_spam_blog', array( $this, 'mailchimp_blog_users_remove' ) );
		add_action( 'make_spam_user', array( $this, 'mailchimp_user_remove' ) );
		add_action( 'delete_user', array( $this, 'mailchimp_user_remove' ) );
		add_action( 'wpmu_delete_user', array( $this, 'mailchimp_user_remove' ) );
		add_action( 'bp_core_action_set_spammer_status', array( $this, 'mailchimp_bp_spamming' ) , 10, 2); //for buddypress

		add_action( 'widgets_init', array( $this, 'mailchimp_widget_init' )  );

		$plugin_basename = plugin_basename( plugin_dir_path( __FILE__ )) . '/mailchimp-sync.php';
		add_filter( 'network_admin_plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		new WPMUDEV_MailChimp_Sync_Webhooks_20();

		if ( is_multisite() ) {
			add_action( 'network_admin_notices', array( $this, 'admin_notices' ) );
		}
		else {
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}
	}

	private function set_globals() {
		define( 'MAILCHIMP_MAX_LOG_LINES', 100 );
		define( 'MAILCHIMP_LANG_DOMAIN', 'mailchimp' );
		define( 'MAILCHIMP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		define( 'MAILCHIMP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

		define( 'MAILCHIMP_ASSETS_URL', MAILCHIMP_PLUGIN_URL . 'assets/' );
		define( 'MAILCHIMP_FRONT_DIR', MAILCHIMP_PLUGIN_DIR . 'front/' );
	}

	private function includes() {
		require_once( 'deprecated.php' );
		require_once( 'helpers.php' );
		require_once( 'integration.php' );
		require_once( 'mailchimp-api-2.0/webhooks.php' );

		if ( is_admin() ) {
			include_once( 'admin/user-profile.php' );
		}

		// WPMUDEV Dashboard class
		if ( is_admin() ) {
			global $wpmudev_notices;
			$wpmudev_notices[] = array( 
				'id'=> 73,
				'name'=> 'MailChimp Sync', 
				'screens' => array( 
					'settings_page_mailchimp-network'
				) 
			);
			include_once( 'externals/wpmudev-dash-notification.php' );
		}
			
	}

	public function add_action_links( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . network_admin_url( 'settings.php?page=mailchimp' ) . '">' . __( 'Settings', 'mailchimp' ) . '</a>'
			),
			$links
		);
	}

	public function init() {
		if ( is_admin() ) {
			require_once( 'admin/admin_page.php' );
			new WPMUDEV_MailChimp_Admin();
		}

		if ( ! is_multisite() || ( is_multisite() && get_site_option( 'mailchimp_allow_shortcode', false ) ) ) {
			require_once( MAILCHIMP_FRONT_DIR . 'shortcode.php' );
			new WPMUDEV_MailChimp_Shortcode();
		}

		require_once( MAILCHIMP_FRONT_DIR . 'form.class.php' );
		add_action( 'wp_ajax_incsub_mailchimp_subscribe_user', array( 'WPMUDEV_MailChimp_Form', 'validate_ajax_form' ) );
		add_action( 'wp_ajax_nopriv_incsub_mailchimp_subscribe_user', array( 'WPMUDEV_MailChimp_Form', 'validate_ajax_form' ) );


	}


	function mailchimp_localization() {
	  // Load up the localization file if we're using WordPress in a different language
		// Place it in this plugin's "languages" folder and name it "mailchimp-[value in wp-config].mo"
	  load_plugin_textdomain( 'mailchimp', false, '/mailchimp-sync/languages/' );
	}



	function mailchimp_widget_init() {
		if ( ! is_multisite() || ( is_multisite() && get_site_option( 'mailchimp_allow_widget', false ) ) ) {
			require_once( MAILCHIMP_FRONT_DIR . 'widget.php' );
			register_widget( 'Incsub_Mailchimp_Widget' );
		}
	}


	function mailchimp_add_user($uid) {

		if ( is_integer( $uid ) ) {
			$user = get_userdata( $uid );
		}
		elseif ( is_array( $uid ) ) {
			$user = new stdClass;
			$user->spam = false;
			$user->deleted = false;
			$user->user_email = $uid['email'];
			$user->user_firstname = $uid['first_name'];
			$user->user_lastname = $uid['last_name'];
		}
		else {
			return false;
		}

		// If we have recently added this email, don't do anything
		$transient_key = 'mailchimp_sync_' . md5( $user->user_email );
		if ( get_site_transient( $transient_key ) ) {
			return false;
		}
		
		//check for spam
		if ( $user->spam || $user->deleted )
	    	return false;

		//remove + sign emails
		if ( get_site_option('mailchimp_ignore_plus') == 'yes' && strstr( $user->user_email, '+' ) )
			return false;
		
		$mailchimp_mailing_list = get_site_option('mailchimp_mailing_list');
		$mailchimp_auto_opt_in = get_site_option('mailchimp_auto_opt_in');

		$autopt = $mailchimp_auto_opt_in == 'yes' ? true : false;
		$merge_vars = array( 'FNAME' => $user->user_firstname, 'LNAME' => $user->user_lastname );
		$merge_groups = mailchimp_get_interest_groups();
		if ( ! empty( $merge_groups ) )
			$merge_groups = array( 'groupings' => $merge_groups );

		$merge_vars = array_merge( $merge_vars, $merge_groups );

		$merge_vars = apply_filters( 'mailchimp_merge_vars', $merge_vars, $user );
		do_action( 'mailchimp_subscribe_user', $merge_vars, $user );

		$results = false;
		if ( ! mailchimp_is_user_subscribed( $user->user_email ) )
			$results = mailchimp_subscribe_user( $user->user_email, $mailchimp_mailing_list, $autopt, $merge_vars, true );

		if ( ! is_wp_error( $results ) ) {
			// There could be other plugins triggering this function twice for a single user.
			// MailChimp does not refresh the list such fast.
			// We'll save the subscriber user data in order to avoid that.
			$transient_key = 'mailchimp_sync_' . md5( $results['email'] );
			set_site_transient( $transient_key, true, 30 ); // Set it only to 30 seconds, should be enough for most cases
		}
		return $results;
	}



	function mailchimp_edit_user($uid) {

		$user = get_userdata( $uid );

		//check for spam
		if ( $user->spam || $user->deleted )
	    	return false;

		$mailchimp_mailing_list = get_site_option('mailchimp_mailing_list');

		$merge_vars = array( 'FNAME' => $user->user_firstname, 'LNAME' => $user->user_lastname );

	  	$merge_vars = apply_filters('mailchimp_merge_vars', $merge_vars, $user);
	  	do_action( 'mailchimp_update_user', $merge_vars, $user );

        $results = mailchimp_update_user( $user->user_email, $mailchimp_mailing_list, $merge_vars );

	}

	function mailchimp_user_remove( $uid ) {

		$user = get_userdata( $uid );

		if ( ! $user )
			return;

		$mailchimp_mailing_list = get_site_option('mailchimp_mailing_list');

		$results = mailchimp_unsubscribe_user( $user->user_email, $mailchimp_mailing_list );
	}

	function mailchimp_blog_users_remove( $blog_id ) {
		$mailchimp_mailing_list = get_site_option('mailchimp_mailing_list');
		$api = mailchimp_load_API();

		$emails = array();
		$blogusers = get_users_of_blog( $blog_id );
		if ( $blogusers ) {
			foreach ($blogusers as $bloguser) {
				$emails[] = $bloguser->user_email;
			}
		}

		$results = mailchimp_bulk_unsubscribe_users( $emails, $mailchimp_mailing_list );
	}

	function mailchimp_bp_spamming( $user_id, $is_spam ) {
	  if ($is_spam)
	    $this->mailchimp_user_remove( $user_id );
	  else
	    $this->mailchimp_add_user( $user_id );
	}

	//------------------------------------------------------------------------//
	//---Page Output Functions------------------------------------------------//
	//------------------------------------------------------------------------//


	/**
	 * Log MailChimp errors
	 * @param Array $errors 
	 * @return type
	 */
	function mailchimp_log_errors( $errors ) {

		if ( ! is_array( $errors ) )
			$errors = array( $errors );

		$current_log = get_site_option( 'mailchimp_error_log' );
		$new_log = array();


		foreach ( $errors as $error ) {

			$code = isset( $error['code'] ) ? $error['code'] : 0;
			$message = isset( $error['message'] ) ? $error['message'] : '';
			$email = isset( $error['email'] ) ? $error['email'] : '';
			$date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), current_time( 'timestamp' ) );

			$new_log[] = compact( 'code', 'message', 'email', 'date' );
			
		}


		if ( $current_log ) {

			$new_log = array_merge( $current_log, $new_log );

			// We'll only saved the last X lines of the log
			$count = count( $new_log );
			if ( $count > MAILCHIMP_MAX_LOG_LINES ) {
				$new_log = array_slice( $new_log, $count - $offset - 1 );
			}
			
		}

		update_site_option( 'mailchimp_error_log', $new_log );

	}

	public function maybe_upgrade() {
		$saved_version = get_site_option( 'mailchimp_sync_version', '1.8.5' );
		if ( $saved_version === self::$version ) {
			return;
		}

		if ( version_compare( $saved_version, '1.9', '<' ) ) {
			// Show a notice so the user can set the groups again
			$mailchimp_apikey = get_site_option('mailchimp_apikey', '');
			$mailchimp_mailing_list = get_site_option('mailchimp_mailing_list');
			$groups = get_site_option( 'mailchimp_groups' );

			if ( $mailchimp_apikey && $mailchimp_mailing_list && $groups ) {
				update_site_option( 'mailchimp_sync_set_groups_again_notice', true );
			}
		}

		update_site_option( 'mailchimp_sync_version', self::$version );

	}

	public function admin_notices() {
		if ( ! current_user_can( 'manage_network' ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( get_site_option( 'mailchimp_sync_set_groups_again_notice' ) ) {
			?>
			<div class="error">
				<p><?php printf(
					__( 'Mailchimp API has been updated. These change affects to groups references. Please, <a href="%s">click here</a> to set the groups again.', 'mailchimp' ),
						is_multisite() ? network_admin_url('settings.php') . '?page=mailchimp' : admin_url('options.php') . '?page=mailchimp'
					); ?>
					<a href="#" data-option="mailchimp_sync_set_groups_again_notice" class="mailchimp-dismiss dashicons-dismiss dashicons"><span class="screen-reader-text"><?php _e( 'Dismiss', 'mailchimp' ); ?></span></a>
				</p>
			</div>
			<style>
				.error .mailchimp-dismiss {float:right; text-decoration: none; color:#dc3232 }
				.error .mailchimp-dismiss:hover {color:red;}
			</style>
			<script>
				jQuery(document).ready( function( $ ) {
					$('.mailchimp-dismiss').click( function(e) {
						e.preventDefault();
						var data = {
							action: 'mailchimp_dismiss_notice',
							option: $(this).data( 'option' )
						};
						$(this).parent().parent().remove();
						$.post( ajaxurl, data );
					});

				});
			</script>
			<?php
		}
	}
}

global $mailchimp_sync;
$mailchimp_sync = WPMUDEV_MailChimp_Sync::get_instance();

function mailchimp_sync() {
	return WPMUDEV_MailChimp_Sync::get_instance();
}

add_action( 'wp_ajax_mailchimp_dismiss_notice', 'mailchimp_dismiss_notice' );
function mailchimp_dismiss_notice() {
	if (
		( is_multisite() && current_user_can( 'manage_network' ) )
		|| ( ! is_multisite() && current_user_can( 'manage_options' ) )
	) {
		$option = $_POST['option'];
		$allowed_options = array(
			'mailchimp_sync_set_groups_again_notice'
		);
		if ( in_array( $option, $allowed_options ) ) {
			delete_site_option( $option );
		}
	}
}
