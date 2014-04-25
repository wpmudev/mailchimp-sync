<?php

require_once( MAILCHIMP_FRONT_DIR . 'form.class.php' );

class Incsub_Mailchimp_Widget extends WP_Widget {

	private $errors = array();
	private $success;
	private $enqueue_scripts = false;
	private $form = null;

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

		add_action( 'init', array( &$this, 'init_form' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'register_scripts' ) );

		add_filter( 'mailchimp_form_require_field', array( &$this, 'set_require_field' ), 10, 3 );
		add_filter( 'mailchimp_form_success_redirect', array( &$this, 'set_form_success_redirect' ) );
		add_filter( 'mailchimp_form_subscribed_placeholder', array( &$this, 'set_form_success_placeholder' ), 10, 2 );

	}

	public function init_form() {
		$instance = $this->get_settings();

		if ( ! isset( $instance[ $this->number ] ) )
			return;
		
		$instance = $instance[ $this->number ];

		$form_args = array(
	    	'text' => $instance['text'],
	    	'subscribed' => isset( $_GET['mailchimp-subscribed'] ) && 'true' == $_GET['mailchimp-subscribed'],
	    	'subscribed_placeholder' => $instance['subscribed_placeholder'],
	    	'button_text' => ! empty( $instance['button_text'] ) ? $instance['button_text'] : __( 'Subscribe', MAILCHIMP_LANG_DOMAIN ),
	    	'form_id' => 'incsub-mailchimp-widget-form-' . $this->number,
	    	'form_class' => 'incsub-mailchimp-widget-form'
	    );

		$this->form = new WPMUDEV_MailChimp_Form( $form_args );
		
	}

	public function set_require_field( $require, $field, $form_id ) {
		$instance = $this->get_settings();
		if ( false !== $instance && 'incsub-mailchimp-widget-form-' . $this->number == $form_id ) {
			$widget_settings = $instance[ $this->number ];
			$require = $widget_settings[ 'require_' . $field ];
		}

		
		return $require;
	}

	public function register_scripts() {
		if ( ! is_null( $this->form ) )
			$this->form->register_scripts();
	}

	public function set_form_success_redirect( $redirect_to ) {
		$instance = $this->get_settings();
		if ( false !== $instance ) {
			$widget_settings = $instance[ $this->number ];
			$redirect_to .= '#incsub-mailchimp-widget-' . $this->number;
		}
		
		return $redirect_to;
	}

	public function set_form_success_placeholder( $placeholder, $form_id ) {
		$instance = $this->get_settings();
		if ( false !== $instance && 'incsub-mailchimp-widget-form-' . $this->number == $form_id ) {
			$widget_settings = $instance[ $this->number ];
			$placeholder = $widget_settings['subscribed_placeholder'];
		}
		
		return $placeholder;
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

	    $this->form->render_form();

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