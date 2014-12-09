<?php

require_once( MAILCHIMP_FRONT_DIR . 'form.class.php' );

class WPMUDEV_MailChimp_Shortcode {

	public $errors  = array();
	public $enqueue_styles = false;
	static $number = 1;

	public function __construct() {
		add_shortcode( 'mailchimp-form', array( $this, 'render_form' ) );

		// Adding icon in rich editor
		add_action( 'admin_head-post-new.php', array( $this,'add_tinymce_buttons' ) );
        add_action( 'admin_head-post.php', array( $this,'add_tinymce_buttons' ) );

		add_action( 'wp_footer', array( &$this, 'register_scripts' ) );
		add_action( 'wp_ajax_display_mailchimp_shortcode_admin_form', array( &$this, 'display_shortcode_admin_form' ) );

	}


	public function validate_shortcode( $settings = array() ) {
		if ( isset( $_POST['submit-subscribe-shortcode-user'] ) ) {

			global $mailchimp_sync;
			$errors = array();

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'mailchimp_subscribe_user' ) )
				return false;

			
			$_form_id = $_POST['form_id'];
			$form_id = explode( '-', $_form_id );
			$number = $form_id[ count( $form_id ) -1 ];

			$errors = WPMUDEV_MailChimp_Form::validate_subscription_form( $_POST, $settings );

			if ( empty( $errors ) ) {
				$user['email'] = sanitize_email( $_POST['subscription-email'] );
				$user['first_name'] = sanitize_text_field( $_POST['subscription-firstname'] );
				$user['last_name'] = sanitize_text_field( $_POST['subscription-lastname'] );

				$mailchimp_sync->mailchimp_add_user( $user );
			}

    		$this->errors[ $number ] = $errors;


		}
		
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
		if ( $this->enqueue_styles ) {
			WPMUDEV_MailChimp_Form::enqueue_dependencies();
		}
	}


	public function add_tinymce_buttons() {
		if ( get_user_option( 'rich_editing' ) == 'true' ) {
			add_filter( 'mce_external_plugins', array( &$this, 'add_buttons' ) );
	    	add_filter( 'mce_buttons', array( &$this, 'register_buttons' ) );
			add_editor_style( MAILCHIMP_ASSETS_URL . 'shortcode-editor.css' );
		}
	}

	public function add_buttons( $plugin_array ) {
		global $wp_version;

		if ( version_compare( $wp_version, '3.9', '<' ) )
			$suffix = '';
		else
			$suffix = '_39';

		$plugin_array['mailchimpshortcode'] = MAILCHIMP_ASSETS_URL . 'tiny-mce-button.js';

		return $plugin_array;
	}


	public function register_buttons( $buttons ) {
		array_push( $buttons, 'mailchimpform' );
		return $buttons;
	}


	public function render_form( $atts ) {
		extract( $atts );

		$args['text'] = $title;
		$args['subscribed_placeholder'] = $success_text;
		$args['button_text'] = $button_text;
		$args['form_id'] = 'incsub-mailchimp-shortcode-form-' . self::$number;
		$args['submit_name'] = 'submit-subscribe-shortcode-user';
		$args['firstname'] = ! empty( $_POST['subscription-firstname'] ) ? stripslashes( $_POST['subscription-firstname'] ) : '';
		$args['lastname'] = ! empty( $_POST['subscription-lastname'] ) ? stripslashes( $_POST['subscription-lastname'] ) : '';
		$args['email'] = ! empty( $_POST['subscription-email'] ) ? stripslashes( $_POST['subscription-email'] ) : '';

		$settings = array(
			'require_firstname' => isset( $firstname ) ? (bool)$firstname : false,
			'require_lastname' => isset( $lastname ) ? (bool)$lastname : false,
		);
		$this->validate_shortcode( $settings );

		$args['errors'] = isset( $this->errors[ self::$number ] ) ? $this->errors[ self::$number ] : array();
		$args['subscribed'] = isset( $_POST[ 'submit-subscribe-shortcode-user' ] ) && empty( $this->errors[ self::$number ] );
		$args['require_fn'] = $settings['require_firstname'];
		$args['require_ln'] = $settings['require_lastname'];

		self::$number++;
		$this->enqueue_styles = true;
		
		ob_start();
		WPMUDEV_MailChimp_Form::render_form( $args );
		return ob_get_clean();
	}

	public function display_shortcode_admin_form() {
		
		global $wp_scripts;
		?>

		<html xmlns="http://www.w3.org/1999/xhtml">
		    <head>
		        <title><?php _e( 'Mailchimp Shortcode', MAILCHIMP_LANG_DOMAIN ); ?></title>
		        <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
		        <script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/tiny_mce_popup.js"></script>
		        <script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/utils/form_utils.js"></script>
		        <link rel="stylesheet" id="dashicons-css" href="<?php echo includes_url(); ?>css/dashicons.css" type="text/css" media="all">
		        <link rel='stylesheet' href='<?php $urlblog=get_bloginfo('wpurl');echo $urlblog ?>/wp-admin/load-styles.php?c=1&amp;dir=ltr&amp;load=widgets,global,wp-admin' type='text/css' media='all' />
		        <link rel='stylesheet' id='colors-css'  href='<?php echo includes_url(); ?>css/buttons.css' type='text/css' media='all' />
		        <!--[if lte IE 7]>
		        	<link rel='stylesheet' id='ie-css'  href='<?php echo admin_url() ?>wp-admin/css/ie.css' type='text/css' media='all' />
		        <![endif]-->
		        
		        <base target="_self" />
		        <?php wp_print_scripts('jquery'); ?>
		        <script>
		        	function init() {
					    tinyMCEPopup.resizeToInnerSize();
					}

					function insert_mailchimp_shortcode() {
					    var code;

					    code = "[mailchimp-form";
					    // Form title
	                    var title = '';
	                    var title_input = jQuery( '#mailchimp_title' );
	                    code += ' title="' + title_input.val() + '"';

	                    // Success text
	                    var success = '';
	                    var success_input = jQuery( '#success_text' );
	                    code += ' success_text="' + success_input.val() + '"';

	                    // Submit button text
	                    var button = '';
	                    var button_input = jQuery( '#button_text' );
	                    code += ' button_text="' + button_input.val() + '"';

	                    // Required fields
	                    var require = jQuery( '#require_firstname' ).attr('checked');
	                    if ( ! require )
	                        code += ' firstname="0"';
	                    else
	                        code += ' firstname="1"';

	                    require = jQuery( '#require_lastname' ).attr('checked');
	                    if ( ! require )
	                        code += ' lastname="0"';
	                    else
	                        code += ' lastname="1"';

	                    code += ']';

					    if (window.tinyMCE) {
					        window.tinyMCE.execInstanceCommand(window.tinyMCE.activeEditor.id, 'mceInsertContent', false, code);
					        //Peforms a clean up of the current editor HTML.
					        //tinyMCEPopup.editor.execCommand('mceCleanup');
					        //Repaints the editor. Sometimes the browser has graphic glitches.
					        //tinyMCEPopup.editor.execCommand('mceRepaint');
					        tinyMCEPopup.close();
					    }
					    return false;
					}
		        </script>
		    </head>

		    <body id="link">
		        <form action="#">
		        	<div id="mailchimp-admin-form-wrap">
			            <div class="mailchimp-admin-shortcode-field">
							<label>
								<?php _e( 'Form title', MAILCHIMP_LANG_DOMAIN ); ?> <br/>
								<input class="widefat" type="text" id="mailchimp_title" name="title" value="<?php echo esc_attr( __( 'Subscribe to our MailChimp list.', MAILCHIMP_LANG_DOMAIN ) ); ?>"> 
							</label>
						</div>
						<div class="mailchimp-admin-shortcode-field">
							<label>
								<?php _e( 'Text displayed when a user subscribes', MAILCHIMP_LANG_DOMAIN ); ?> <br/>
								<input class="widefat" type="text" id="success_text" name="success-text" value="<?php echo esc_attr( __( 'Thank you, your email has been added to the list.', MAILCHIMP_LANG_DOMAIN ) ); ?>"> 
							</label>
						</div>
						<div class="mailchimp-admin-shortcode-field">
							<label>
								<?php _e( 'Subscribe button text', MAILCHIMP_LANG_DOMAIN ); ?> <br/>
								<input type="text" id="button_text" name="button-text" value="<?php echo esc_attr( __( 'Subscribe', MAILCHIMP_LANG_DOMAIN ) ); ?>"> 
							</label>
						</div>
						<div class="mailchimp-admin-shortcode-field">
							<label>
								<input type="checkbox" id="require_firstname" name="require_firstname" > 
								<?php _e( 'Require First Name', MAILCHIMP_LANG_DOMAIN ); ?>
							</label>
						</div>
						<div class="mailchimp-admin-shortcode-field">
							<label>
								<input type="checkbox" id="require_lastname" name="require_lastname" > 
								<?php _e( 'Require Last Name', MAILCHIMP_LANG_DOMAIN ); ?>
							</label>
						</div>

			            <div>
			                <div style="float: left">
			                    <input type="button" id="cancel" name="cancel" value="<?php _e( 'Cancel', MAILCHIMP_LANG_DOMAIN); ?>" onClick="tinyMCEPopup.close();" />
			                </div>

			                <div style="float: right">
			                    <input type="submit" id="insert" name="insert" value="<?php _e( 'Insert', MAILCHIMP_LANG_DOMAIN); ?>" onClick="insert_mailchimp_shortcode();" />
			                </div>
			            </div>
			        </div>
		        </form>
		    </body>
		</html>
		<style>
			body,input {
				font-size:14px;
			}
			input[type="text"] {
				box-sizing:border-box;
				width:100%;
				padding:5px;
			}
			.mailchimp-admin-shortcode-field {
				margin-bottom:25px;

			}
			.mailchimp-admin-shortcode-field label {
				margin-right:15px;
				line-height: 2.5;
			}
			#mailchimp-admin-form-wrap {
				padding:2em;
			}
			input[type=checkbox]:checked:before, input[type=radio]:checked:before {
				float: left;
				display: inline-block;
				vertical-align: middle;
				width: 16px;
				font: normal 30px/1 'dashicons';
				speak: none;
				-webkit-font-smoothing: antialiased;
				-moz-osx-font-smoothing: grayscale;
			}

		</style>

		<?php die();

	}

}