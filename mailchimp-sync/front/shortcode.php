<?php

require_once( MAILCHIMP_FRONT_DIR . 'form.class.php' );

class WPMUDEV_MailChimp_Shortcode {

	public $errors  = array();
	public $enqueue_styles = false;
	static $number = 1;

	public function __construct() {
		//add_action( 'wp_loaded', array( &$this, 'init_form' ) );
		add_shortcode( 'mailchimp-form', array( $this, 'render_form' ) );
		add_action( 'init', array( &$this, 'add_tinymce_buttons' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'register_scripts' ) );
		add_action( 'wp_ajax_display_mailchimp_shortcode_admin_form', array( &$this, 'display_shortcode_admin_form' ) );

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
		//$this->form->register_scripts();
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
		extract( $atts );

		$args['text'] = $title;
		$args['subscribed_placeholder'] = $success_text;
		$args['button_text'] = $button_text;
		$args['form_id'] = 'incsub-mailchimp-shortcode-form-' . self::$number;
		$args['submit_name'] = 'submit-subscribe-shortcode-user';
		$args['subscribed'] = isset( $_GET[ 'mailchimp-shortcode-subscribed-' . self::$number ] );
		$args['errors'] = isset( $this->errors[ self::$number ] ) ? $this->errors[ self::$number ] : array();
		$args['firstname'] = ! empty( $_POST['subscription-firstname'] ) ? stripslashes( $_POST['subscription-firstname'] ) : '';
		$args['lastname'] = ! empty( $_POST['subscription-lastname'] ) ? stripslashes( $_POST['subscription-lastname'] ) : '';
		$args['email'] = ! empty( $_POST['subscription-email'] ) ? stripslashes( $_POST['subscription-email'] ) : '';

		$this->form->render_form( $args );
	}

	public function display_shortcode_admin_form() {
		
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-widget');
		wp_enqueue_script('jquery-ui-position');
		wp_enqueue_script('jquery');
		global $wp_scripts;
		?>

		<html xmlns="http://www.w3.org/1999/xhtml">
		    <head>
		        <title><?php _e( 'Mailchimp Shortcode', MAILCHIMP_LANG_DOMAIN ); ?></title>
		        <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
		        <script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/tiny_mce_popup.js"></script>
		        <script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/utils/form_utils.js"></script>

		        
		        <base target="_self" />
		        <?php wp_print_scripts(); ?>
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
					        tinyMCEPopup.editor.execCommand('mceRepaint');
					        tinyMCEPopup.close();
					    }
					    return;
					}
		        </script>
		    </head>

		    <body id="link">
		        <form action="#">
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

		</style>

		<?php die();

	}

}