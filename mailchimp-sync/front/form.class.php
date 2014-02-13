<?php


class WPMUDEV_MailChimp_Form {
	public $errors = array();
	public $enqueue_scripts = false;
	public $args = array();
	public $form_id = '';

	public function __construct( $args ) {
		$defaults = array(
			'text' 						=> __( 'Subscribe to our MailChimp list.', MAILCHIMP_LANG_DOMAIN ),
			'button_text' 				=> __( 'Subscribe', MAILCHIMP_LANG_DOMAIN ),
			'subscribed_placeholder' 	=> __( 'Thank you, your email has been added to the list.', MAILCHIMP_LANG_DOMAIN ),
		    'subscribed' 				=> false,
		    'firstname' 				=> isset( $_POST['subscription-firstname'] ) ? stripslashes( $_POST['subscription-firstname'] ) : '',
		    'lastname' 					=> isset( $_POST['subscription-lastname'] ) ? stripslashes( $_POST['subscription-lastname'] ) : '',
		    'email' 					=> isset( $_POST['subscription-email'] ) ? stripslashes( $_POST['subscription-email'] ) : '',
		    'form_id' 					=> '',
		    'form_class' 				=> ''
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'mailchimp_form_args', $args );
		$this->args = $args;

		$this->form_id = $args['form_id'];

		$this->errors = array();

		add_action( 'template_redirect', array( $this, 'validate_form' ) );
		add_action( 'wp_ajax_incsub_mailchimp_subscribe_user', array( $this, 'validate_form' ) );
		add_action( 'wp_ajax_nopriv_incsub_mailchimp_subscribe_user', array( $this, 'validate_form' ) );
	}
	public function register_scripts() {
		wp_register_script( 'mailchimp-form-js', MAILCHIMP_ASSETS_URL . 'form.js', array( 'jquery' ), '20140212', true );
		wp_enqueue_style( 'mailchimp-form-css', MAILCHIMP_ASSETS_URL . 'form.css', array(), '20140212' );

		$l10n = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( "mailchimp_subscribe_user" )
		);
		wp_localize_script( 'mailchimp-form-js', 'mailchimp_form_captions', $l10n );

		add_action( 'wp_footer', array( $this, 'enqueue_scripts' ) );

	}

	public function set_form_id( $id ) {
		$this->form_id = $id;
	}

	public function enqueue_scripts() {
		if ( $this->enqueue_scripts ) {
			wp_enqueue_script( 'mailchimp-form-js' );
			//wp_enqueue_style( 'mailchimp-form-css' );
		}
	}

	public function render_form() {
		$this->enqueue_scripts = true;
		$errors = $this->errors;
		extract( $this->args );

		include_once( 'form-template.php' );
	}

	public function validate_form() {
		global $mailchimp_sync;

		if ( isset( $_POST['action'] ) && 'incsub_mailchimp_subscribe_user' == $_POST['action'] ) {

			$doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;
			$errors = array();

			if ( ! $doing_ajax ) {
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'mailchimp_subscribe_user' ) )
					return false;
			}
			else {
				check_ajax_referer( 'mailchimp_subscribe_user', 'nonce' );
			}
				
			$email = sanitize_email( $_POST['subscription-email'] );
			if ( ! is_email( $email ) )
				$errors[] = ( __( 'Please, insert a valid email', MAILCHIMP_LANG_DOMAIN ) );

			$firstname = sanitize_text_field( $_POST['subscription-firstname'] );
			$firstname = ! empty( $firstname ) ? $firstname : '';
			$require_firstname = apply_filters( 'mailchimp_form_require_field', false, 'firstname', $this->form_id );
			if ( empty( $firstname ) && $require_firstname )
				$errors[] = ( __( 'First name is required', MAILCHIMP_LANG_DOMAIN ) );

			
			$lastname = sanitize_text_field( $_POST['subscription-lastname'] );
			$lastname = ! empty( $lastname ) ? $lastname : '';
			$require_lastname = apply_filters( 'mailchimp_form_require_field', false, 'lastname', $this->form_id );
			if ( empty( $lastname ) && $require_lastname )
				$errors[] = ( __( 'Last name is required', MAILCHIMP_LANG_DOMAIN ) );

			apply_filters( 'mailchimp_form_validate', $this->errors );

			if ( empty( $errors ) ) {
				$user['email'] = $email;
				$user['first_name'] = $firstname;
				$user['last_name'] = $lastname;

				$mailchimp_sync->mailchimp_add_user( $user );

				if ( ! $doing_ajax ) {
					$redirect_to = add_query_arg( 'mailchimp-subscribed', 'true' );
					$redirect_to = apply_filters( 'mailchimp_form_success_redirect', $redirect_to );
					wp_redirect( $redirect_to );
					exit;		
				}
				else {
					$text = apply_filters( 'mailchimp_form_subscribed_placeholder', __( 'Thank you, your email has been added to the list.', MAILCHIMP_LANG_DOMAIN ) );
					wp_send_json_success( array( 'message' => $text ) );
				}
			}
			elseif ( ! empty( $errors ) && $doing_ajax ) {
    			wp_send_json_error( $errors );
    		}

    		$this->errors = $errors;

		}
	}
}