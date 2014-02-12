<?php

class Incsub_Mailchimp_Widget extends WP_Widget {

	private $errors = array();
	private $success;
	private $enqueue_scripts = false;

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

		add_action( 'template_redirect', array( &$this, 'validate' ) );

		add_action( 'wp_ajax_incsub_mailchimp_subscribe_user', array( &$this, 'validate' ) );
		add_action( 'wp_ajax_nopriv_incsub_mailchimp_subscribe_user', array( &$this, 'validate' ) );

		add_action( 'wp_enqueue_scripts', array( &$this, 'register_scripts' ) );

	}

	public function register_scripts() {
		wp_register_script( 'mailchimp-widget-js', MAILCHIMP_ASSETS_URL . 'widget.js', array( 'jquery' ), '20140212', true );

		$l10n = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( "mailchimp_subscribe_user" )
		);
		wp_localize_script( 'mailchimp-widget-js', 'mailchimp_widget_captions', $l10n );
	}

	public function validate() {
		global $mailchimp_sync;

		if ( isset( $_POST['action'] ) && 'incsub_mailchimp_subscribe_user' == $_POST['action'] ) {

			$doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

			if ( ! $doing_ajax ) {
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'mailchimp_subscribe_user' ) )
					return false;
			}
			else {
				check_ajax_referer( 'mailchimp_subscribe_user', 'nonce' );
			}

			$instance = $this->get_settings();

			if ( false !== $instance ) {
				$instance = $instance[ $this->number ];
				
				$email = sanitize_email( $_POST['subscription-email'] );
				if ( ! is_email( $email ) )
					$this->errors[] = ( __( 'Please, insert a valid email', MAILCHIMP_LANG_DOMAIN ) );

				$firstname = sanitize_text_field( $_POST['subscription-firstname'] );
				$firstname = ! empty( $firstname ) ? $firstname : '';
				if ( empty( $firstname ) && $instance['require_firstname'] )
					$this->errors[] = ( __( 'First name is required', MAILCHIMP_LANG_DOMAIN ) );


				$lastname = sanitize_text_field( $_POST['subscription-lastname'] );
				$lastname = ! empty( $lastname ) ? $lastname : '';
				if ( empty( $lastname ) && $instance['require_lastname'] )
					$this->errors[] = ( __( 'Last name is required', MAILCHIMP_LANG_DOMAIN ) );

				if ( empty( $this->errors ) ) {
					$user['email'] = $email;
					$user['first_name'] = $firstname;
					$user['last_name'] = $lastname;

					$mailchimp_sync->mailchimp_add_user( $user );

					
					if ( ! $doing_ajax ) {
						$link = add_query_arg( 'mailchimp-subscribed', 'true' );
						$link .= '#incsub-mailchimp-widget-' . $this->number;
						wp_redirect( $link );
						exit;		
					}
					else {
						$text = $instance['subscribed_placeholder'];
						wp_send_json_success( array( 'message' => $text ) );
					}
				}
				elseif ( ! empty( $this->errors ) && $doing_ajax ) {
	    			wp_send_json_error( $this->errors );
	    		}

			}
		}
	}


	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		wp_enqueue_script( 'mailchimp-widget-js' );

		extract( $args );

        $title = apply_filters( 'widget_title', $instance['title'] );
        $text = $instance['text'];
		$button_text = ! empty( $instance['button_text'] ) ? $instance['button_text'] : __( 'Subscribe', MAILCHIMP_LANG_DOMAIN );

	    echo $before_widget;
	     
	    if ( $title )
	     	echo $before_title . $title . $after_title; 

	    if ( isset( $_GET['mailchimp-subscribed'] ) && 'true' == $_GET['mailchimp-subscribed'] ) {
	    	?>
				<p class="mailchimp-widget-updated">
					<?php echo $instance['subscribed_placeholder']; ?>
				</p>
	    	<?php
	    }
	    else {
		    ?>
		        <form method="post" class="incsub-mailchimp-widget-form" action="" id="incsub-mailchimp-widget-form-<?php echo $this->number; ?>">
		        	<p>
			        	<?php echo $text; ?>
			        </p>
			        <?php if ( ! empty( $this->errors ) ): ?>
			        	<ul class="mailchimp-widget-error">
			        	<?php foreach ( $this->errors as $error ): ?>
							<li style="color:#333 !important;"><?php echo $error; ?></li>
			        	<?php endforeach; ?>
			        	</ul>
			    	<?php endif; ?>
		        	<input type="text" class="incsub-mailchimp-field" name="subscription-firstname" value="<?php echo isset( $_POST['subscription-firstname'] ) ? $_POST['subscription-firstname'] : ''; ?>" placeholder="<?php _e( 'First name', MAILCHIMP_LANG_DOMAIN ); ?>"><br/>
		        	<input type="text" class="incsub-mailchimp-field" name="subscription-lastname" value="<?php echo isset( $_POST['subscription-lastname'] ) ? $_POST['subscription-lastname'] : ''; ?>" placeholder="<?php _e( 'Last name', MAILCHIMP_LANG_DOMAIN ); ?>"><br/>
		        	<input type="email" class="incsub-mailchimp-field" name="subscription-email" value="<?php echo isset( $_POST['subscription-email'] ) ? $_POST['subscription-email'] : ''; ?>" placeholder="<?php _e( 'Email', MAILCHIMP_LANG_DOMAIN ); ?>"><br/>
		        	<input type="hidden" class="incsub-mailchimp-field" name="action" value="incsub_mailchimp_subscribe_user">
		        	<?php wp_nonce_field( 'mailchimp_subscribe_user' ); ?>
		        	<input type="submit" class="incsub-mailchimp-submit" name="submit-subscribe-user" value="<?php echo $button_text; ?>"> <span class="mailchimp-spinner"></span>
		        </form>
		        <style>
		        	.mailchimp-spinner {
		        		background: url('<?php echo MAILCHIMP_ASSETS_URL . "spinner.gif"; ?>') no-repeat top left transparent;
		        		height: 25px;
						width: 25px;
						display: inline-block;
						vertical-align: middle;
						visibility:hidden;
		        	}
		        	.mailchimp-widget-updated {
		        		background:#B6ECBB;
		        		border:1px solid #44793D;
		        		box-sizing:border-box;
		        		padding:5px;
		        	}
		        	.mailchimp-widget-error {
						background:#FFCACA;
						border:1px solid #B35C5C;
						box-sizing:border-box;
						list-style:none;
						padding:5px;
		        	}
		        	.incsub-mailchimp-field {
		        		box-sizing:border-box;
		        		width:100%;
		        	}
		        </style>
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