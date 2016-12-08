<?php


class WPMUDEV_MailChimp_Form {
	public $errors = array();
	public $enqueue_scripts = false;
	public $args = array();
	public $form_id = '';


	public static function enqueue_dependencies() {
		add_action( 'wp_enqueue_scripts', array( 'WPMUDEV_MailChimp_Form', 'register_styles' ) );
		add_action( 'wp_enqueue_scripts', array( 'WPMUDEV_MailChimp_Form', 'register_scripts' ) );

		// Template hooks
		add_action( 'mailchimp_form_start', array( 'WPMUDEV_MailChimp_Form', 'add_subscribed_message' ) );
		add_action( 'mailchimp_form_after_errors', array( 'WPMUDEV_MailChimp_Form', 'add_errors_list_section' ) );
	}

	public static function add_subscribed_message( $args ) {
		?>
			<p style="display:none" class="incsub-mailchimp-updated">
				<?php echo $args['subscribed_placeholder']; ?>
			</p>
		<?php
	}

	public static function add_errors_list_section() {
		?>
			<ul style="display:none" class="incsub-mailchimp-error">
			</ul>
		<?php
	}

	public static function register_styles() {
		wp_enqueue_style( 'mailchimp-form-css', MAILCHIMP_ASSETS_URL . 'form.css', array(), '20140212' );
	}

	public static function register_scripts() {
		wp_register_script( 'mailchimp-form-js', MAILCHIMP_ASSETS_URL . 'form.js', array( 'jquery' ), '20140212', true );
		

		$l10n = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( "mailchimp_subscribe_user" )
		);
		wp_localize_script( 'mailchimp-form-js', 'mailchimp_form_captions', $l10n );

		add_action( 'wp_footer', array( 'WPMUDEV_MailChimp_Form', 'enqueue_scripts' ) );

	}


	public static function enqueue_scripts() {
		wp_enqueue_script( 'mailchimp-form-js' );
	}


	public static function render_form( $args ) {
		$defaults = array(
			'submit_name' => 'submit-subscribe-user',
			'button_text' => '',
			'form_id' => '',
			'subscribed_placeholder' => '',
			'text' => '',
			'subscribed' => false,
			'firstname' => '',
			'lastname' => '',
			'email' => '',
			'errors' => array(),
			'form_class' => '',
			'require_fn' => false,
			'require_ln' => false
		);

		$args = wp_parse_args( $args, $defaults );
		extract( $args );

		$require_fn = $require_fn ? 1 : 0;
		$require_ln = $require_ln ? 1 : 0;
		include( apply_filters( 'mailchimp_form_template_location', MAILCHIMP_PLUGIN_DIR . 'front/form-template.php' ) );
	}

	public static function validate_ajax_form() {
		global $mailchimp_sync_api;

		check_ajax_referer( 'mailchimp_subscribe_user_' . $_POST['form_id'] . $_POST['require_fn'] . $_POST['require_ln'] );

		$errors = self::validate_subscription_form( 
			$_POST, 
			array( 
				'require_firstname' => (bool)$_POST['require_fn'], 
				'require_lastname' => (bool)$_POST['require_ln'] 
			) 
		);

		if ( ! empty( $errors ) )
			wp_send_json_error( array( 'errors' => $errors ) );

		
		$user['email'] = sanitize_email( $_POST['subscription-email'] );
		$user['first_name'] = sanitize_text_field( $_POST['subscription-firstname'] );
		$user['last_name'] = sanitize_text_field( $_POST['subscription-lastname'] );

		$results = $mailchimp_sync_api->mailchimp_add_user( $user );

		wp_send_json_success( $results );

		die();
	}

	public static function validate_subscription_form( $input, $settings ) {
		$default_settings = array(
			'require_firstname' => false,
			'require_lastname' => false
		);


		$errors = array();
		
		$settings = wp_parse_args( $settings, $default_settings );

		$email = sanitize_email( $input['subscription-email'] );
		if ( ! is_email( $email ) )
			$errors[] = ( __( 'Please, insert a valid email', MAILCHIMP_LANG_DOMAIN ) );

		$firstname = sanitize_text_field( $input['subscription-firstname'] );
		$firstname = ! empty( $firstname ) ? $firstname : '';
		if ( empty( $firstname ) && $settings['require_firstname'] )
			$errors[] = ( __( 'First name is required', MAILCHIMP_LANG_DOMAIN ) );

		
		$lastname = sanitize_text_field( $input['subscription-lastname'] );
		$lastname = ! empty( $lastname ) ? $lastname : '';
		if ( empty( $lastname ) && $settings['require_lastname'] )
			$errors[] = ( __( 'Last name is required', MAILCHIMP_LANG_DOMAIN ) );

		// Check if user is already subscribed and confirmed
		$is_subscribed = mailchimp_30_get_user_info( $email );
		if ( $is_subscribed && $is_subscribed['status'] === 'subscribed' )
			$errors[] = ( __( 'The email is already in the subscription list', MAILCHIMP_LANG_DOMAIN ) );

		return apply_filters( 'mailchimp_form_validate', $errors );
	}
}
