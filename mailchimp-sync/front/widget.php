<?php

require_once( MAILCHIMP_FRONT_DIR . 'form.class.php' );

class Incsub_Mailchimp_Widget extends WP_Widget {

	private $errors = array();
	private $success;
	private $enqueue_scripts = false;
	private $form_id;

	/**
	 * Widget setup.
	 */
	function Incsub_Mailchimp_Widget() {

		$this->success = false;

		/* Widget settings. */
		$widget_ops = array( 
			'classname' => 'incsub-mailchimp-widget' , 
			'description' => __( 'This widget allows visitors to subscribe to a Mailchimp email list (set by the network administrator).', MAILCHIMP_LANG_DOMAIN ) 
		);

		/* Create the widget. */
		parent::WP_Widget( 'incsub-mailchimp-widget' , __( 'Mailchimp', MAILCHIMP_LANG_DOMAIN ), $widget_ops );

		add_filter( 'mailchimp_form_require_field', array( &$this, 'set_require_field' ), 10, 3 );
		add_filter( 'mailchimp_form_success_redirect', array( &$this, 'set_form_success_redirect' ) );
		add_filter( 'mailchimp_form_subscribed_placeholder', array( &$this, 'set_form_success_placeholder' ), 10, 2 );

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'init', array( $this, 'validate_widget' ) );

		
	}

	function init() {
		WPMUDEV_MailChimp_Form::enqueue_dependencies();
	}


	function validate_widget() {
		if ( isset( $_POST['submit-subscribe-widget-user'] ) ) {

			global $mailchimp_sync;

			$doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;
			$errors = array();

			if ( ! $doing_ajax ) {
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'mailchimp_subscribe_user' ) )
					return false;
			}
			else {
				check_ajax_referer( 'mailchimp_subscribe_user', 'nonce' );
			}

			$_form_id = $_POST['form_id'];
			$form_id = explode( '-', $_form_id );
			$number = $form_id[ count( $form_id ) -1 ];

			$settings = $this->get_settings();

			if ( ! isset( $settings[ $number ] ) )
				return;

			$settings = $settings[ $number ];

			$errors = WPMUDEV_MailChimp_Form::validate_subscription_form( $_POST, $settings );

			if ( empty( $errors ) ) {
				$user['email'] = sanitize_email( $_POST['subscription-email'] );
				$user['first_name'] = sanitize_text_field( $_POST['subscription-firstname'] );
				$user['last_name'] = sanitize_text_field( $_POST['subscription-lastname'] );

				$mailchimp_sync->mailchimp_add_user( $user );

				if ( ! $doing_ajax ) {
					$redirect_to = add_query_arg( 'mailchimp-subscribed-' . $number, 'true' ) . '#' . $_form_id;
					wp_redirect( $redirect_to );
					exit;		
				}
				else {
					$text = apply_filters( 'mailchimp_form_subscribed_placeholder', $this->args['subscribed_placeholder'], $_POST['form_id'] );
					wp_send_json_success( array( 'message' => $text ) );
				}
			}
			elseif ( ! empty( $errors ) && $doing_ajax ) {
    			wp_send_json_error( $errors );
    		}

    		$this->errors[ $number ] = $errors;


		}
	}


	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {

		extract( $args );

        $title = apply_filters( 'widget_title', $instance['title'] );		
		
	    echo $before_widget;
	     
	    if ( $title )
	     	echo $before_title . $title . $after_title; 

	    
	    $firstname = ! empty( $_POST['subscription-firstname'] ) ? stripslashes( $_POST['subscription-firstname'] ) : '';
		$lastname = ! empty( $_POST['subscription-lastname'] ) ? stripslashes( $_POST['subscription-lastname'] ) : '';
		$email = ! empty( $_POST['subscription-email'] ) ? stripslashes( $_POST['subscription-email'] ) : '';

		$form_id = 'incsub-mailchimp-widget-form-' . $this->number;
		$subscribed = isset( $_GET[ 'mailchimp-subscribed-' . $this->number ] );

		$submit_name = 'submit-subscribe-widget-user';
		$errors = isset( $this->errors[ $this->number ] ) ? $this->errors[ $this->number ] : array();

		$args = compact( 'submit_name', 'form_id', 'subscribed', 'firstname', 'lastname', 'email', 'errors' );
		$args['button_text'] = $instance['button_text'];
		$args['subscribed_placeholder'] = $instance['subscribed_placeholder'];
		$args['text'] = $instance['text'];
		$args['require_fn'] = $instance['require_firstname'];
		$args['require_ln'] = $instance['require_lastname'];
	    
	    WPMUDEV_MailChimp_Form::render_form( $args );

		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['text'] = sanitize_text_field( $new_instance['text'] );
		$instance['button_text'] = sanitize_text_field( $new_instance['button_text'] );
		$instance['subscribed_placeholder'] = sanitize_text_field( $new_instance['subscribed_placeholder'] );
		$instance['require_firstname'] = ! empty( $new_instance['require_firstname'] ) ? true : false;
		$instance['require_lastname'] = ! empty( $new_instance['require_lastname'] ) ? true : false;

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 
			'title' => __( 'Subscribe to MailChimp', MAILCHIMP_LANG_DOMAIN ), 
			'text' => __( 'Subscribe to our MailChimp list.', MAILCHIMP_LANG_DOMAIN ), 
			'button_text' => __( 'Subscribe', MAILCHIMP_LANG_DOMAIN  ),
			'subscribed_placeholder' => __( 'Thank you, your email has been added to the list.', MAILCHIMP_LANG_DOMAIN ),
			'require_firstname' => false,
			'require_lastname' => false 
		);

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', MAILCHIMP_LANG_DOMAIN ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'text' ); ?>"><?php _e('Text:', MAILCHIMP_LANG_DOMAIN ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>" value="<?php echo esc_attr( $instance['text'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'button_text' ); ?>"><?php _e('Subscribe button text:', MAILCHIMP_LANG_DOMAIN); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'button_text' ); ?>" name="<?php echo $this->get_field_name( 'button_text' ); ?>" value="<?php echo esc_attr( $instance['button_text'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'subscribed_placeholder' ); ?>"><?php _e( 'Text displayed when a user subscribes:', MAILCHIMP_LANG_DOMAIN); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'subscribed_placeholder' ); ?>" name="<?php echo $this->get_field_name( 'subscribed_placeholder' ); ?>" value="<?php echo esc_attr( $instance['subscribed_placeholder'] ); ?>" />
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'require_firstname' ); ?>" name="<?php echo $this->get_field_name( 'require_firstname' ); ?>" value="1" <?php checked( $instance['require_firstname'] ); ?> /> 
			<label for="<?php echo $this->get_field_id( 'require_firstname' ); ?>"><?php _e('Require first name field', MAILCHIMP_LANG_DOMAIN ); ?></label>
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'require_lastname' ); ?>" name="<?php echo $this->get_field_name( 'require_lastname' ); ?>" value="1" <?php checked( $instance['require_lastname'] ); ?> /> 
			<label for="<?php echo $this->get_field_id( 'require_lastname' ); ?>"><?php _e('Require last name field', MAILCHIMP_LANG_DOMAIN ); ?></label>
		</p>
	<?php
	}

}