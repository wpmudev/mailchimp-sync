<?php

class WPMUDEV_MailChimp_Shortcode {

	public $errors  = array();
	public $enqueue_styles = false;

	public function __construct() {
		add_shortcode( 'mailchimp-form', array( $this, 'render_form' ) );
		add_action( 'init', array( &$this, 'add_tinymce_buttons' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'register_styles' ) );
		add_action( 'wp_footer', array( &$this, 'enqueue_styles' ) );
	}

	public function register_styles() {
		wp_register_style( 'mailchimp-shortcode', MAILCHIMP_ASSETS_URL . 'shortcode.css' );
	}

	public function enqueue_styles() {
		if ( $this->enqueue_styles )
			wp_enqueue_style( 'mailchimp-shortcode' );
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
		$this->enqueue_styles = true;
		$this->process();

		extract( shortcode_atts( array(
			'bgcolor' => 'transparent',
			'textcolor' => 'inherit',
			'width' => 'auto',
			'center' => 'true',
			'success_text' => __( 'Thanks, your email has been added to our subscription list.' , MAILCHIMP_LANG_DOMAIN )
		), $atts ) );

		$width = 'auto' == $width ? $width : $width . '%';

		if ( count( $this->errors ) == 0 && isset( $_GET['mailchimp-form-subscribed'] ) ) {
			echo '<div class="mailchimp-form-updated"><p>' . $success_text . '</p></div>';
		}
		else {
			?>
				<form method="post" class="mailchimp-form" id="mailchimp-form">
		        	<?php if ( count( $this->errors ) ): ?>
		        		<ul class="mailchimp-form-error">
							<?php foreach ( $this->errors as $error ): ?>
								<li><?php echo $error; ?></li>
							<?php endforeach; ?>
		        		</ul>
		        	<?php endif; ?>
		        	
		        	<?php do_action( 'mailchimp_shortcode_before_fields' ); ?>
	        		
	        		<?php $email = isset( $_POST['mailchimp-email'] ) ? $_POST['mailchimp-email'] : ''; ?>
	        		<div class="mailchimp-form-field-title"><?php _e( 'Email address', MAILCHIMP_LANG_DOMAIN ); ?></div>
		        	<input type="email" class="mailchimp-form-field mailchimp-form-email-field mailchimp-form-field"  name="mailchimp-email" placeholder="<?php _e( 'ex: someone@mydomain.com', MAILCHIMP_LANG_DOMAIN ); ?>" value="<?php echo $email; ?>"><br/>

		        	<?php do_action( 'mailchimp_shortcode_form_fields' ); ?>

			        <?php wp_nonce_field( 'mailchimp_shortcode_subscribe', 'mailchimp_shortcode_nonce' ); ?>

		        	<input type="hidden" class="mailchimp-form-field mailchimp-form-field" name="action" value="mailchimp_form_subscribe_user">

		        	<div class="mailchimp-form-submit-wrap">
		        		<input type="submit" class="mailchimp-form-submit" name="submit-mailchimp-subscribe-user" value="<?php echo apply_filters( 'mailchimp_shortcode_button_text', __( 'Subscribe', MAILCHIMP_LANG_DOMAIN ) ); ?>">
		        	</div>

					<?php do_action( 'mailchimp_shortcode_after_fields' ); ?>
		        </form>

			<?php
		}
	}

	private function process() {
		global $mailchimp_sync;
		if ( isset( $_POST['action'] ) && 'mailchimp_form_subscribe_user' == $_POST['action'] ) {

			if ( ! wp_verify_nonce( $_POST['mailchimp_shortcode_nonce'], 'mailchimp_shortcode_subscribe' ) )
				return false;
			
			$email = sanitize_email( $_POST['mailchimp-email'] );
			if ( ! is_email( $email ) )
				$this->errors[] =  __( 'Please, insert a valid email', MAILCHIMP_LANG_DOMAIN );

			$this->errors = apply_filters( 'mailchimp_shortcode_process_form', $this->errors );
			if ( empty( $this->errors ) ) {
				$user['email'] = $email;

				$user = apply_filters( 'mailchimp_shortcode_add_user', $user );

				$mailchimp_sync->mailchimp_add_user( $user );

				$link = add_query_arg( 'mailchimp-form-subscribed', 'true' );
				wp_redirect( $link );
			}

		}
	}


}