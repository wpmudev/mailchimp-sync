<?php

require_once( MAILCHIMP_FRONT_DIR . 'form.class.php' );

class WPMUDEV_MailChimp_Shortcode {

	public $errors  = array();
	public $enqueue_styles = false;
	static $number = 1;

	public function __construct() {
		add_action( 'wp_loaded', array( &$this, 'init_form' ) );
		add_shortcode( 'mailchimp-form', array( $this, 'render_form' ) );
		add_action( 'init', array( &$this, 'add_tinymce_buttons' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'register_scripts' ) );

	}

	public function init_form() {
		$form_args = array(
	    	'subscribed' => isset( $_GET['mailchimp-subscribed'] ) && 'true' == $_GET['mailchimp-subscribed'],
	    	'form_id' => 'incsub-mailchimp-shortcode-form-' . self::$number,
	    	'form_class' => 'incsub-mailchimp-shortcode-form'
	    );
		$this->form = new WPMUDEV_MailChimp_Form( $form_args );
		self::$number++;
	}

	public function set_form_success_redirect( $redirect_to ) {
		$instance = $this->get_settings();
		if ( false !== $instance && $form_id == 'incsub-mailchimp-widget-form-' . $this->number ) {
			$widget_settings = $instance [ $this->number ];
			$redirect_to .= 'incsub-mailchimp-shortcode-form-' . self::$number;
		}
		
		return $redirect_to;
	}

	public function register_scripts() {
		$this->form->register_scripts();
	}


	public function add_tinymce_buttons() {
		add_filter( 'mce_external_plugins', array( &$this, 'add_buttons' ) );
    	add_filter( 'mce_buttons', array( &$this, 'register_buttons' ) );
	}

	public function add_buttons( $plugin_arr ) {
		$plugin_arr['mailchimpshortcode'] = MAILCHIMP_ASSETS_URL . 'tiny-mce-button.js';
		return $plugin_arr;
	}


	public function register_buttons( $buttons ) {
		array_push( $buttons, 'mailchimpform' );
		return $buttons;
	}


	public function render_form( $atts ) {
		$this->form->render_form();
	}

}