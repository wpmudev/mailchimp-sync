<?php


class WPMUDEV_MailChimp_Form {
	public $errors = array();
	public $enqueue_scripts = false;
	public $args = array();
	public $form_id = '';

	public function __construct( $args ) {
		add_action( 'wp_ajax_incsub_mailchimp_subscribe_user', array( $this, 'validate_form' ) );
		add_action( 'wp_ajax_nopriv_incsub_mailchimp_subscribe_user', array( $this, 'validate_form' ) );
	}

	public static function enqueue_dependencies() {
		add_action( 'wp_enqueue_scripts', array( 'WPMUDEV_MailChimp_Form', 'register_styles' ) );
		//add_action( 'wp_enqueue_scripts', array( 'WPMUDEV_MailChimp_Form', 'register_scripts' ) );
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
			'form_class' => ''
		);

		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		include( apply_filters( 'mailchimp_form_template_location', MAILCHIMP_PLUGIN_DIR . 'front/form-template.php' ) );
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

		return apply_filters( 'mailchimp_form_validate', $errors );
	}
}
