<?php

class Incsub_Mailchimp_Widget extends WP_Widget {

	private $errors;
	private $success;

	/**
	 * Widget setup.
	 */
	function Incsub_Mailchimp_Widget() {

		$this->errors = false;
		$this->success = false;

		/* Widget settings. */
		$widget_ops = array( 
			'classname' => 'incsub-mailchimp-widget' , 
			'description' => __( 'This widget allows visitors to subscribe to a Mailchimp email list (set by the network administrator).', 'mailchimp' ) 
		);

		/* Create the widget. */
		parent::WP_Widget( 'incsub-mailchimp-widget' , __( 'Mailchimp', 'mailchimp' ), $widget_ops );

		add_action( 'template_redirect', array( &$this, 'validate' ) );

	}

	public function validate() {
		if ( isset( $_POST['action'] ) && 'incsub_mailchimp_subscribe_user' == $_POST['action'] ) {

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'mailchimp_subscribe_user' ) )
				return false;

			$instance = $this->get_settings();

			if ( false !== $instance ) {
				$instance = $instance[ $this->number ];
				
				$email = sanitize_email( $_POST['subscription-email'] );
				if ( ! is_email( $email ) )
					$this->add_error( __( 'Please, insert a valid email', 'mailchimp' ) );

				$firstname = sanitize_text_field( $_POST['subscription-firstname'] );
				$firstname = ! empty( $firstname ) ? $firstname : '';
				if ( empty( $firstname ) && $instance['require_firstname'] )
					$this->add_error( __( 'First name is required', 'mailchimp' ) );


				$lastname = sanitize_text_field( $_POST['subscription-lastname'] );
				$lastname = ! empty( $lastname ) ? $lastname : '';
				if ( empty( $lastname ) && $instance['require_lastname'] )
					$this->add_error( __( 'Last name is required', 'mailchimp' ) );

				if ( ! $this->is_error() ) {
					$user['email'] = $email;
					$user['first_name'] = $firstname;
					$user['last_name'] = $lastname;

					mailchimp_add_user( $user );

					$link = add_query_arg( 'mailchimp-subscribed', 'true' );
					$link .= '#incsub-mailchimp-widget-' . $this->number;
					wp_redirect( $link );
				}

			}
		}
	}

	private function add_error( $message ) {
		if ( ! $this->errors )
			$this->errors = array();

		$this->errors[] = $message;
	}

	private function is_error() {
		return ( is_array( $this->errors ) && ! empty( $this->errors ) );
	}



	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

        $title = apply_filters( 'widget_title', $instance['title'] );
        $text = $instance['text'];
		$button_text = ! empty( $instance['button_text'] ) ? $instance['button_text'] : __( 'Subscribe', 'mailchimp' );

	    echo $before_widget;
	     
	    if ( $title )
	     	echo $before_title . $title . $after_title; 

	    if ( isset( $_GET['mailchimp-subscribed'] ) && 'true' == $_GET['mailchimp-subscribed'] ) {
	    	?>
				<p style="background:#B6ECBB;border:1px solid #44793D;box-sizing:border-box;padding:5px;">
					<?php echo $instance['subscribed_placeholder']; ?>
				</p>
	    	<?php
	    }
	    else {
		    ?>
		        <form method="post" action="#<?php echo 'incsub-mailchimp-widget-' . $this->number; ?>" id="incsub-mailchimp-widget">
		        	<p>
			        	<?php echo $text; ?>
			        </p>
			        <?php if ( $this->is_error() ): ?>
			        	<ul style="background:#FFCACA;border:1px solid #B35C5C;box-sizing:border-box;list-style:none;padding:5px;">
			        	<?php foreach ( $this->errors as $error ): ?>
							<li style="color:#333 !important;"><?php echo $error; ?></li>
			        	<?php endforeach; ?>
			        	</ul>
			    	<?php endif; ?>
		        	<input type="text" class="incsub-mailchimp-field" name="subscription-firstname" value="<?php echo isset( $_POST['subscription-firstname'] ) ? $_POST['subscription-firstname'] : ''; ?>" placeholder="<?php _e( 'First name', 'mailchimp' ); ?>"><br/>
		        	<input type="text" class="incsub-mailchimp-field" name="subscription-lastname" value="<?php echo isset( $_POST['subscription-lastname'] ) ? $_POST['subscription-lastname'] : ''; ?>" placeholder="<?php _e( 'Last name', 'mailchimp' ); ?>"><br/>
		        	<input type="email" class="incsub-mailchimp-field" name="subscription-email" value="<?php echo isset( $_POST['subscription-email'] ) ? $_POST['subscription-email'] : ''; ?>" placeholder="<?php _e( 'Email', 'mailchimp' ); ?>"><br/>
		        	<input type="hidden" name="action" value="incsub_mailchimp_subscribe_user">
		        	<?php wp_nonce_field( 'mailchimp_subscribe_user' ); ?>
		        	<input type="submit" class="incsub-mailchimp-submit" name="submit-subscribe-user" value="<?php echo $button_text; ?>">
		        </form>
		        
	        <?php
	    }
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
			'title' => __( 'Subscribe to MailChimp', 'mailchimp' ), 
			'text' => __( 'Subscribe to our MailChimp list.', 'mailchimp' ), 
			'button_text' => __( 'Subscribe', 'mailchimp'  ),
			'subscribed_placeholder' => __( 'Thank you, your email has been added to the list.', 'mailchimp' ),
			'require_firstname' => false,
			'require_lastname' => false 
		);

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'mailchimp' ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'text' ); ?>"><?php _e('Text:', 'mailchimp' ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>" value="<?php echo esc_attr( $instance['text'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'button_text' ); ?>"><?php _e('Subscribe button text:', 'mailchimp'); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'button_text' ); ?>" name="<?php echo $this->get_field_name( 'button_text' ); ?>" value="<?php echo esc_attr( $instance['button_text'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'subscribed_placeholder' ); ?>"><?php _e( 'Text displayed when a user subscribes:', 'mailchimp'); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'subscribed_placeholder' ); ?>" name="<?php echo $this->get_field_name( 'subscribed_placeholder' ); ?>" value="<?php echo esc_attr( $instance['subscribed_placeholder'] ); ?>" />
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'require_firstname' ); ?>" name="<?php echo $this->get_field_name( 'require_firstname' ); ?>" value="1" <?php checked( $instance['require_firstname'] ); ?> /> 
			<label for="<?php echo $this->get_field_id( 'require_firstname' ); ?>"><?php _e('Require first name field', 'mailchimp' ); ?></label>
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'require_lastname' ); ?>" name="<?php echo $this->get_field_name( 'require_lastname' ); ?>" value="1" <?php checked( $instance['require_lastname'] ); ?> /> 
			<label for="<?php echo $this->get_field_id( 'require_lastname' ); ?>"><?php _e('Require last name field', 'mailchimp' ); ?></label>
		</p>
	<?php
	}

}