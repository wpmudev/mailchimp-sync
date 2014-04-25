<?php

class WPMUDEV_MailChimp_Admin {

	private $page_id;
	private $errors;
	private $tabs;
	private $capability;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'network_admin_menu', array( $this, 'add_page' ) );

		$api = mailchimp_load_API();

		$this->tabs = array(
			'settings' 	=> __( 'Settings', MAILCHIMP_LANG_DOMAIN )
		);

		if ( ! is_wp_error( $api ) )
			$this->tabs['import'] = __( 'Import', MAILCHIMP_LANG_DOMAIN );

		$this->tabs['error-log'] = __( 'Error Log', MAILCHIMP_LANG_DOMAIN );

		$this->capability = is_multisite() ? 'manage_network' : 'manage_options';
	}

	private function get_current_tab() {
		if ( ! isset( $_GET['tab'] ) || ! array_key_exists( $_GET['tab'], $this->tabs ) ) {
			return 'settings';
		}

		return $_GET['tab'];
	}

	public function add_page() {
		if ( is_multisite() ) {
		    $this->page_id = add_submenu_page('settings.php', __( 'MailChimp Settings', MAILCHIMP_LANG_DOMAIN ), 'MailChimp', $this->capability, 'mailchimp', array( $this, 'render_page' ) );
		} 
		else {
			$this->page_id = add_options_page( __( 'MailChimp Settings', MAILCHIMP_LANG_DOMAIN ), 'MailChimp', $this->capability, 'mailchimp', array( $this, 'render_page' ) );
		}

		add_action( 'load-' . $this->page_id, array( $this, 'process_form' ) );
	}

	public function process_form() {
		if ( isset( $_POST['action'] ) && isset( $_POST['submit-mailchimp-settings'] ) ) {

			check_admin_referer( 'mailchimp-settings' );

			if ( ! current_user_can( $this->capability ) )
				return;

			$admin_url = is_multisite() ? network_admin_url( 'settings.php' ) : admin_url( 'options-general.php' );
			$redirect_to = add_query_arg( 'page', 'mailchimp', $admin_url );

			if ( $_POST['action'] == 'submit-settings' ) {
				update_site_option( 'mailchimp_allow_widget', ! empty( $_POST['allow_widget'] ) );
				update_site_option( 'mailchimp_allow_shortcode', ! empty( $_POST['allow_shortcode'] ) );

				if ( isset( $_POST['mailchimp_apikey'] ) )
					update_site_option('mailchimp_apikey', $_POST['mailchimp_apikey']);
				else 
					update_site_option('mailchimp_apikey', '' );

				if ( isset( $_POST['mailchimp_mailing_list'] ) )
					update_site_option('mailchimp_mailing_list', $_POST['mailchimp_mailing_list']);
				else 
					update_site_option('mailchimp_mailing_list', '' );

				if ( isset( $_POST['mailchimp_auto_opt_in'] ) )
					update_site_option('mailchimp_auto_opt_in', $_POST['mailchimp_auto_opt_in']);
				else 
					update_site_option('mailchimp_auto_opt_in', '' );

				if ( isset( $_POST['mailchimp_ignore_plus'] ) )
					update_site_option('mailchimp_ignore_plus', $_POST['mailchimp_ignore_plus']);
				else 
					update_site_option('mailchimp_ignore_plus', '' );


				wp_redirect( add_query_arg( 'updated', 'true', $redirect_to ) );
				exit();
			}

			if ( $_POST['action'] == 'submit-import' ) {
				global $wpdb, $mailchimp_sync;

				$mailchimp_import_mailing_list = $_POST['mailchimp_import_mailing_list'];
				$mailchimp_import_auto_opt_in = $_POST['mailchimp_import_auto_opt_in'];
				if ( ! empty( $mailchimp_import_mailing_list ) ) {
					set_time_limit(0);
					$mailchimp_ignore_plus = get_site_option( 'mailchimp_ignore_plus' );


					$query = "SELECT u.ID FROM {$wpdb->users} u";
					$existing_users = $wpdb->get_col( $query );

					
        			$add_list = array();
        			foreach ( $existing_users as $user_id ) {
        				$user = get_user_by( 'id', $user_id );

        				if ( ! $user || ! empty( $user->spam ) || ! empty( $user->deleted ) )
        					continue;

        				$item = array(
        					'email' => array( 'email' => $user->user_email )
        				);

        				$merge_vars = array();
        				if ( $first_name = get_user_meta( $user_id, 'first_name', true ) )
        					$merge_vars['FNAME'] = $first_name;

        				if ( $last_name = get_user_meta( $user_id, 'last_name', true ) )
        					$merge_vars['LNAME'] = $last_name;

        				$merge_vars = apply_filters( 'mailchimp_bulk_merge_vars', $merge_vars, $item, $user_id );

        				$item['merge_vars'] = $merge_vars;
        				$add_list[] = $item; 
        			}

        			if ( ! empty( $add_list ) ) {
        				$mailchimp_import_auto_opt_in == 'yes' ? true : false;
        				$results = mailchimp_bulk_subscribe_users( $add_list, $mailchimp_import_mailing_list, $mailchimp_import_auto_opt_in, true );

        				if ( $results['errors'] ) {
        					$errors = array();
        					foreach ( $results['errors'] as $error ) {
        						if ( ! is_wp_error( $error ) )
        							continue;

        						$errors[] = array(
        							'code' => $error->get_error_code(),
        							'message' => $error->get_error_message(),
        							'email' => ''
        						);
        					}

        					$mailchimp_sync->mailchimp_log_errors( $errors );
        					
        				}

        				wp_redirect( add_query_arg( array( 
								'imported' => 'true',
								'a' => count( $results['added'] ),
								'u' => count( $results['updated'] ),
								'e' => count( $results['errors'] ),
								'tab' => 'import'
							),
							$redirect_to ) 
						);
						exit();	
        			}
        			
				}
			}

		}
	}

	public function render_page() {

		$current_tab = $this->get_current_tab();
		$submit_text = 'settings' == $current_tab ? null : __( 'Import', MAILCHIMP_LANG_DOMAIN );
		?>
			<div class="wrap">
				
				<h2 class="nav-tab-wrapper">			
					<?php foreach ( $this->tabs as $tab => $tab_name ): ?>
						<a href="?page=mailchimp&tab=<?php echo $tab; ?>" class="nav-tab <?php echo $current_tab == $tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $tab_name ); ?></a>
					<?php endforeach; ?>
				</h2>

				<?php if ( isset( $_GET['updated'] ) ): ?>
					<div class="updated"><p><?php _e( 'Settings updated', MAILCHIMP_LANG_DOMAIN ); ?></p></div>
				<?php endif; ?>

				<?php if ( isset( $_GET['imported'] ) ): ?>
					<div class="updated"><p><?php printf( __('%d users added, %d updated, and %d errors.', MAILCHIMP_LANG_DOMAIN), $_GET['a'], $_GET['u'], $_GET['e'] ); ?></p></div>
				<?php endif; ?>

				
				<form action="" method="post" id="mailchimp-settings-form">

					<?php wp_nonce_field( 'mailchimp-settings', '_wpnonce' ); ?>

					<input type="hidden" name="action" value="submit-<?php echo $current_tab; ?>">

					<?php
					
						if ( 'settings' == $this->get_current_tab() ) {
							$this->render_settings_tab();
							submit_button( $submit_text, 'primary', 'submit-mailchimp-settings' );
						}
						elseif ( 'import' == $this->get_current_tab() ) {
							$this->render_import_tab();
							submit_button( $submit_text, 'primary', 'submit-mailchimp-settings' );
						}
						elseif ( 'error-log' == $this->get_current_tab() ) {
							$this->render_error_log_tab();
						}
					?>

				</form>
			</div>
		<?php
	}

	private function render_settings_tab() {
		$mailchimp_apikey = get_site_option('mailchimp_apikey', '');
		$mailchimp_mailing_list = get_site_option('mailchimp_mailing_list');
		$mailchimp_auto_opt_in = get_site_option('mailchimp_auto_opt_in');
		$mailchimp_ignore_plus = get_site_option('mailchimp_ignore_plus');
		$mailchimp_allow_widget = get_site_option('mailchimp_allow_widget', false);
		$mailchimp_allow_shortcode = get_site_option('mailchimp_allow_shortcode', false);

		if ( ! empty( $mailchimp_apikey ) ) {
			$api = mailchimp_load_API();
			if ( is_wp_error( $api ) )
				$api_error = $api->get_error_message();
        	
        	$mailchimp_lists = mailchimp_get_lists();
			$api_error = ! empty( $api_error );
		}

		if ( empty( $mailchimp_apikey ) ): ?>
   			<p><?php _e('After you have entered a valid API key you will be able to select different MailChimp options below.', MAILCHIMP_LANG_DOMAIN); ?></p>
    	<?php endif; ?>

    	<table class="form-table">
			

	        <tr class="form-field form-required">
	            <th scope="row"><?php _e('MailChimp API Key', MAILCHIMP_LANG_DOMAIN)?></th>
	            <td><input type="text" name="mailchimp_apikey" id="mailchimp_apikey" value="<?php echo $mailchimp_apikey; ?>" style="width:25%" /><br />
	            <?php _e('Visit <a href="http://admin.mailchimp.com/account/api" target="_blank">your API dashboard</a> to create an API key.', MAILCHIMP_LANG_DOMAIN); ?>
	            </td>
	        </tr>

	        <?php if ( ! empty( $mailchimp_apikey ) ): ?>

	        	<?php if ( ! $api_error ): ?>
					<tr class="form-field form-required">
						<th scope="row"><?php _e('API Key Test', MAILCHIMP_LANG_DOMAIN)?></th>
						<td><strong style="color:#006633"><?php _e('Passed', MAILCHIMP_LANG_DOMAIN)?></strong>
						</td>
					</tr>
    				<tr class="form-field form-required">
        				<th scope="row"><?php _e( 'Auto Opt-in', MAILCHIMP_LANG_DOMAIN )?></th>
        					<td>
        						<select name="mailchimp_auto_opt_in" id="mailchimp_auto_opt_in">
				                    <option value="yes" <?php selected( $mailchimp_auto_opt_in, 'yes' ); ?> ><?php _e('Yes', MAILCHIMP_LANG_DOMAIN); ?></option>
				                    <option value="no" <?php selected( $mailchimp_auto_opt_in, 'no' ); ?> ><?php _e('No', MAILCHIMP_LANG_DOMAIN); ?></option>
				                </select>
        						<span class="description"><?php _e( 'Automatically opt-in new users to the mailing list. Users will not receive an email confirmation. Use at your own risk.', MAILCHIMP_LANG_DOMAIN ); ?></span>
        					</td>
		            </tr>
		            <tr class="form-field form-required">
		                <th scope="row"><?php _e('Ignore email addresses including + signs', MAILCHIMP_LANG_DOMAIN)?></th>
		                <td>
		                	<select name="mailchimp_ignore_plus" id="mailchimp_ignore_plus">
		                    	<option value="yes" <?php selected( $mailchimp_ignore_plus, 'yes' ); ?> ><?php _e( 'Yes', MAILCHIMP_LANG_DOMAIN ); ?></option>
		                    	<option value="no" <?php selected( $mailchimp_ignore_plus, 'no' ); ?> ><?php _e( 'No', MAILCHIMP_LANG_DOMAIN ); ?></option>
		                	</select>
		                	<span class="description"><?php _e( 'Ignore email address including + signs. These are usually duplicate accounts.', MAILCHIMP_LANG_DOMAIN ); ?></span>
		                </td>
		            </tr>

				    <?php if ( ! is_array( $mailchimp_lists ) || ! count( $mailchimp_lists ) ): ?>
				    	<p><?php _e('You must have at least one MailChimp mailing list in order to use this plugin. Please create a mailing list via the MailChimp admin panel.', MAILCHIMP_LANG_DOMAIN); ?></p>
					<?php else: ?>
						<tr class="form-field form-required">
			                <th scope="row"><?php _e('Mailing List', MAILCHIMP_LANG_DOMAIN)?></th>
			                <td>
			                  	<select name="mailchimp_mailing_list" id="mailchimp_mailing_list">
            						<?php if ( empty( $mailchimp_mailing_list ) ): ?>
										<option value="" selected="selected" ><?php _e('Please select a mailing list', MAILCHIMP_LANG_DOMAIN); ?></option>
              						<?php endif; ?>
									<?php foreach ( $mailchimp_lists as $mailchimp_list ): ?>
										<option value="<?php echo $mailchimp_list['id']; ?>" <?php selected( $mailchimp_mailing_list, $mailchimp_list['id'] ); ?>><?php echo $mailchimp_list['name']; ?></option>
									<?php endforeach; ?>
								</select>
              					<span class="description"><?php _e('Select a mailing list you want to have new users added to.', MAILCHIMP_LANG_DOMAIN); ?></span>
			                </td>
			            </tr>
					<?php endif; ?>

					<?php if ( is_multisite() ): ?>
	      				<tr class="form-field form-required">
				            <th scope="row"><?php _e('Allow widget in all subsites', MAILCHIMP_LANG_DOMAIN)?></th>
				            <td><input type="checkbox" name="allow_widget" id="allow_widget" <?php checked( $mailchimp_allow_widget ); ?> style="width:inherit;"/>
				            </td>
				        </tr>
				        <tr class="form-field form-required">
				            <th scope="row"><?php _e('Allow shortcode use in all subsites', MAILCHIMP_LANG_DOMAIN)?></th>
				            <td><input type="checkbox" name="allow_shortcode" id="allow_shortcode" <?php checked( $mailchimp_allow_shortcode ); ?> style="width:inherit;"/>
				            </td>
				        </tr>
				    <?php endif; ?>

				<?php else: // if ! $api_error ?>
					<tr class="form-field form-required">
						<th scope="row"><?php _e( 'API Key Test', MAILCHIMP_LANG_DOMAIN )?></th>
						<td><strong style="color:#990000"><?php _e('Failed - Please check your key and try again.', MAILCHIMP_LANG_DOMAIN)?></strong>
						</td>
					</tr>
				<?php endif; ?>								

	    	<?php endif; // if ( ! empty( $mailchimp_apikey ) ) ?>

    	</table>

    	<?php
	}

	private function render_import_tab() {
		$mailchimp_apikey = get_site_option('mailchimp_apikey', '');
		$mailchimp_mailing_list = get_site_option('mailchimp_mailing_list');
		$mailchimp_auto_opt_in = get_site_option('mailchimp_auto_opt_in');
		$mailchimp_ignore_plus = get_site_option('mailchimp_ignore_plus');
		$mailchimp_allow_widget = get_site_option('mailchimp_allow_widget', false);

		if ( ! empty( $mailchimp_apikey ) ) {
			$api = mailchimp_load_API();
			if ( is_wp_error( $api ) )
				$api_error = $api->get_error_message();
        	
        	$mailchimp_lists = mailchimp_get_lists();
			$api_error = ! empty( $api_error );
		}
		

		if ( is_array( $mailchimp_lists ) && count( $mailchimp_lists ) ): ?>
			<h3><?php _e('Sync Existing Users', MAILCHIMP_LANG_DOMAIN) ?></h3>
			<span class="description"><?php _e('This function will syncronize all existing users on your install with your MailChimp list, adding new ones, updating the first/last name of previously imported users, and removing spammed or deleted users from your selected list. Note you really only need to do this once after installing, it is carried on automatically after installation.', MAILCHIMP_LANG_DOMAIN) ?></span>
		<?php endif; ?>

		<table class="form-table">
			<tr class="form-field form-required">
    			<th scope="row"><?php _e('Mailing List', MAILCHIMP_LANG_DOMAIN)?></th>
				<td>
					<select name="mailchimp_import_mailing_list" id="mailchimp_import_mailing_list">
						<?php
			            foreach ( $mailchimp_lists as $mailchimp_list ) {
			              ?><option value="<?php echo $mailchimp_list['id']; ?>" ><?php echo $mailchimp_list['name']; ?></option><?php
			            }
			            ?>
					</select><br />
					<?php _e('The mailing list you want to import existing users to.', MAILCHIMP_LANG_DOMAIN); ?>
	            </td>
	        </tr>
				<tr class="form-field form-required">
				<th scope="row"><?php _e('Auto Opt-in', MAILCHIMP_LANG_DOMAIN)?></th>
				<td>
					<select name="mailchimp_import_auto_opt_in" id="mailchimp_import_auto_opt_in">
		                <option value="yes" ><?php _e('Yes', MAILCHIMP_LANG_DOMAIN); ?></option>
		                <option value="no" ><?php _e('No', MAILCHIMP_LANG_DOMAIN); ?></option>
		            </select><br />
					<?php _e('Automatically opt-in new users to the mailing list. Users will not receive an email confirmation. Use at your own risk.', MAILCHIMP_LANG_DOMAIN); ?>
				</td>
			</tr>
		</table>
		<?php
	}

	private function render_error_log_tab() {
		$error_log = get_site_option( 'mailchimp_error_log' );

        $content = '';

        if ( is_array( $error_log ) ) {
            $error_log = array_reverse( $error_log );

            $content = '';
            if ( ! empty( $error_log ) ) {
            	$content = array();
            	foreach ( $error_log as $error ) {
            		$content[] = '[' . $error['date'] . '] [CODE:' . $error['code'] . '] [EMAIL:' . $error['email'] . '] - ' . $error['message'];
            	} 
            	$content = implode( "\n", $content );
            }
        }
        ?>
        	<h3><?php _e( 'Error log', MAILCHIMP_LANG_DOMAIN ); ?> <span class="description"><?php printf( __( '(Last %d lines)', MAILCHIMP_LANG_DOMAIN ), MAILCHIMP_MAX_LOG_LINES ); ?></span></h3>
			<textarea name="" id="" cols="30" rows="10" disabled class="widefat code"><?php echo esc_textarea( $content ); ?></textarea>
        <?php
	}


}