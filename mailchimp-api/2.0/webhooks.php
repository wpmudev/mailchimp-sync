<?php
//
//class WPMUDEV_MailChimp_Sync_Webhooks_20 {
//
//	public function __construct() {
//		add_action( 'init', array( 'WPMUDEV_MailChimp_Sync_Webhooks_20', 'add_rewrite_rules' ) );
//		add_action( 'template_redirect', array( $this, 'parse_request' ), 99 );
//	}
//
//	public static function add_rewrite_rules() {
//		if ( self::is_webhooks_active() ) {
//
//			add_rewrite_tag( '%mailchimp-sync%', '([^&]+)' );
//			add_rewrite_tag( '%mckey%', '([^&]+)' );
//
//			add_rewrite_rule(
//				'^mailchimp-sync/([^/]*)$',
//				'index.php?mailchimp-sync=1&mckey=$matches[1]',
//				'top'
//			);
//
//		}
//	}
//
//	public static function is_webhooks_active() {
//		$settings = mailchimp_get_webhooks_settings();
//		return ( ! empty( $settings['webhook_key'] ) );
//	}
//
//	public static function get_callback_url() {
//		$webhooks_settings = mailchimp_get_webhooks_settings();
//		if ( ! $webhooks_settings['webhook_key'] )
//			return '';
//
//		if ( ! get_option( 'permalink_structure' ) ) {
//			$url = add_query_arg(
//				array(
//					'mailchimp-sync' => 1,
//					'mckey' => $webhooks_settings['webhook_key']
//				),
//				home_url()
//			);
//		}
//		else {
//			$url = trailingslashit( site_url() ) . 'mailchimp-sync/' . $webhooks_settings['webhook_key'];
//		}
//
//
//		return $url;
//	}
//
//	public function parse_request() {
//		global $wp_query;
//		if ( ! self::is_webhooks_active() )
//			return;
//
//		$webhooks_settings = mailchimp_get_webhooks_settings();
//
//		if ( get_query_var( 'mailchimp-sync' ) == 1 ) {
//			if ( get_query_var( 'mckey' ) != $webhooks_settings['webhook_key'] ) {
//				$this->log( sprintf( __( 'Security key specified, but not correct: %s', MAILCHIMP_LANG_DOMAIN ),  get_query_var( 'mckey' ) ) );
//				exit();
//			}
//
//	        $this->trigger_webhook_action();
//	        exit();
//	    }
//	}
//
//	private function trigger_webhook_action() {
//		$req = $_POST;
//
//		if ( ! isset( $req['type'] ) ) {
//			$this->log( __( 'Request type not defined', MAILCHIMP_LANG_DOMAIN ) );
//			return;
//		}
//
//		$list_id = get_site_option( 'mailchimp_mailing_list' );
//		if ( $list_id != $req['data']['list_id'] ) {
//			$this->log( sprintf( __( 'Requested list ID [%s] is not the same than the selected one in Mailchimp Settings [%s]', MAILCHIMP_LANG_DOMAIN ), $req['data']['list_id'], $list_id ) );
//			return;
//		}
//
//		$allowed_types = apply_filters( 'mailchimp_webhooks_allowed_types', array( 'subscribe', 'unsubscribe', 'profile', 'upemail' ) );
//
//		if ( ! in_array( $req['type'], $allowed_types ) )
//			$this->log( sprintf( __( 'Request type "%s" unknown, ignoring.', MAILCHIMP_LANG_DOMAIN ), $req['type'] ) );
//
//		$function = array( $this, $req['type'] );
//		$function = apply_filters( 'mailchimp_webhook_action_function', $function, $req );
//
//		$result = call_user_func_array( $function, array( $req['data'] ) );
//
//		if ( is_wp_error( $result ) )
//			$this->log( strtoupper( $req['type'] ) . ': ' . $result->get_error_message() );
//
//	}
//
//	private function log( $message, $echo = true ) {
//
//		$current_log = get_site_option( 'mailchimp_webhooks_log' );
//		$new_log = array();
//
//		$date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), current_time( 'timestamp' ) );
//
//		$message = '[' . $date . '] ' . $message;
//		$new_log[] = $message;
//
//
//		if ( $current_log ) {
//
//			$new_log = array_merge( $current_log, $new_log );
//
//			// We'll only saved the last X lines of the log
//			$count = count( $new_log );
//			if ( $count > MAILCHIMP_MAX_LOG_LINES ) {
//				$new_log = array_slice( $new_log, $count - $offset - 1 );
//			}
//
//		}
//
//		update_site_option( 'mailchimp_webhooks_log', $new_log );
//
//		if ( $echo )
//			echo $message;
//	}
//
//	private function subscribe( $data ) {
//		$user_email = $data['email'];
//
//		$user = get_user_by( 'email', $user_email );
//
//		if ( $user )
//			return new WP_Error( 'user_exists', sprintf( __( 'Existing user found with this email address: "%s"', MAILCHIMP_LANG_DOMAIN ), $user_email ) );
//
//		$login = explode( '@', $user_email );
//		$login = $login[0];
//		$login = sanitize_user( $login );
//
//		$user = get_user_by( 'login', $login );
//
//		if ( $user )
//			return new WP_Error( 'user_exists', sprintf( __( 'Existing user found with this user login: "%s"', MAILCHIMP_LANG_DOMAIN ), $login ) );
//
//		$userdata = array(
//			'user_pass' 	=> wp_generate_password( 12, false ),
//			'user_login' 	=> $login,
//			'user_email' 	=> $user_email,
//			'first_name' 	=> $data['merges']['FNAME'],
//			'last_name' 	=> $data['merges']['LNAME'],
//			'role' 			=> 'subscriber'
//		);
//
//		$userdata = apply_filters( 'mailchimp_webhooks_subscribe_user', $userdata );
//
//		$user_id = wp_insert_user( $userdata );
//
//		if ( is_wp_error( $user_id ) )
//			return new WP_Error( 'user_exists', sprintf( __( 'FAILED! Problem encountered trying to create new user: "%s"', MAILCHIMP_LANG_DOMAIN ), $user_email ) );
//
//		$list_id = get_site_option( 'mailchimp_mailing_list' );
//
//		$this->log( sprintf( __( 'SUBSCRIBE: New user created: "%s"', MAILCHIMP_LANG_DOMAIN ), $user_email ) );
//
//		return true;
//	}
//
//	private function unsubscribe( $data ) {
//		$user_email = $data['email'];
//
//		$user = get_user_by( 'email', $user_email );
//
//		if ( ! $user )
//			return new WP_Error( 'user_not_exists', sprintf( __( 'User not found with this email address: "%s"', MAILCHIMP_LANG_DOMAIN ), $user_email ) );
//
//		$settings = mailchimp_get_webhooks_settings();
//		if ( 'mark' === $settings['delete_user'] ) {
//			$this->log( sprintf( __( 'UNSUBSCRIBE: user unsubscribed from list: "%s"', MAILCHIMP_LANG_DOMAIN ), $user_email ) );
//		}
//		else {
//			if ( is_multisite() ) {
//				if ( ! function_exists( 'wp_delete_user' ) )
//					require_once(ABSPATH . 'wp-admin/includes/ms.php');
//
//				if ( is_super_admin() )
//					return new WP_Error( 'user_not_exists', sprintf( __( 'Deleting Super Admins is not allowed: "%s"', MAILCHIMP_LANG_DOMAIN ), $user_email ) );
//
//				$result = wpmu_delete_user( $user->ID, false );
//			}
//			else {
//				if ( ! function_exists( 'wp_delete_user' ) )
//					require_once(ABSPATH . 'wp-admin/includes/user.php');
//
//				if ( current_user_can( 'manage_options' ) )
//					return new WP_Error( 'user_not_exists', sprintf( __( 'Deleting Administrators is not allowed: "%s"', MAILCHIMP_LANG_DOMAIN ), $user_email ) );
//
//				$result = wp_delete_user( $user->ID, false );
//			}
//
//			if ( ! $result )
//				return new WP_Error( 'error_deleting_user', sprintf( __( 'FAILED: Something went wrong while trying to delete user: "%s"', MAILCHIMP_LANG_DOMAIN ), $user_email ) );
//
//			$this->log( sprintf( __( 'UNSUBSCRIBE: user deleted: "%s"', MAILCHIMP_LANG_DOMAIN ), $user_email ) );
//		}
//
//		return true;
//
//	}
//
//	private function upemail( $data ) {
//		$user_email = $data['old_email'];
//
//		$user = get_user_by( 'email', $user_email );
//
//		if ( ! $user )
//			return new WP_Error( 'user_not_exists', sprintf( __( 'User not found with this email address: "%s"', MAILCHIMP_LANG_DOMAIN ), $user_email ) );
//
//		$new_email = $data['new_email'];
//
//		if ( ! is_email( $new_email ) )
//			return new WP_Error( 'wrong_email', sprintf( __( 'The new email is not a valid one: "%s"', MAILCHIMP_LANG_DOMAIN ), $new_email ) );
//
//		$userdata = array(
//			'ID' => $user->ID,
//			'user_email' => $new_email
//		);
//
//		$result = wp_update_user( $userdata );
//
//		if ( ! $result )
//			return new WP_Error( 'error_updating_email', sprintf( __( 'FAILED: Something went wrong while trying to update user email: "%s"', MAILCHIMP_LANG_DOMAIN ), $new_email ) );
//
//		$this->log( sprintf( __( 'UPEMAIL: email updated: "%s" to "%s"', MAILCHIMP_LANG_DOMAIN ), $user_email, $new_email ) );
//
//		return true;
//
//	}
//
//	private function profile( $data ) {
//		$user_email = $data['email'];
//
//		$user = get_user_by( 'email', $user_email );
//
//		if ( ! $user )
//			return new WP_Error( 'user_not_exists', sprintf( __( 'User not found with this email address: "%s"', MAILCHIMP_LANG_DOMAIN ), $user_email ) );
//
//		$userdata = array(
//			'ID' => $user->ID,
//			'first_name' => $data['merges']['FNAME'],
//			'last_name' => $data['merges']['LNAME'],
//		);
//
//		$result = wp_update_user( $userdata );
//
//		if ( ! $result )
//			return new WP_Error( 'error_updating_profile', sprintf( __( 'FAILED: Something went wrong while trying to update user profile: "%s"', MAILCHIMP_LANG_DOMAIN ), $user_email ) );
//
//
//		$this->log( sprintf( __( 'PROFILE: profile updated: "%s"', MAILCHIMP_LANG_DOMAIN ), $user_email ) );
//
//		return true;
//
//	}
//
//
//}