<?php
/**
 * @author: WPMUDEV, Ignacio Cruz (igmoweb)
 * @version:
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Mailchimp_Sync_Admin' ) ) {
	class Mailchimp_Sync_Admin {
		public function __construct() {
			include_once( 'user-profile.php' );
			include_once( 'class-admin-page.php' );

			new WPMUDEV_MailChimp_Admin_Page();

			$plugin_basename = WPMUDEV_MailChimp_Sync::$basename;
			add_filter( 'network_admin_plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

			if ( is_multisite() ) {
				add_action( 'network_admin_notices', array( $this, 'admin_notices' ) );
			}
			else {
				add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			}
		}

		/**
		 * Add action links in plugins list
		 *
		 * @param $links
		 *
		 * @return array
		 */
		public function add_action_links( $links ) {
			return array_merge(
				array(
					'settings' => '<a href="' . network_admin_url( 'settings.php?page=mailchimp' ) . '">' . __( 'Settings', 'mailchimp' ) . '</a>'
				),
				$links
			);
		}

		/**
		 * Show plugin admin notices
		 */
		public function admin_notices() {
			if ( ( is_multisite() && ! current_user_can( 'manage_network' ) ) || ( ! is_multisite() && ! current_user_can( 'manage_options' ) ) ) {
				return;
			}

			if ( get_site_option( 'mailchimp_sync_set_groups_again_notice' ) ) {
				?>
				<div class="error">
					<p><?php printf(
							__( 'Mailchimp API has been updated. These change affects to groups references. Please, <a href="%s">click here</a> to set the groups again.', 'mailchimp' ),
							is_multisite() ? network_admin_url('settings.php') . '?page=mailchimp' : admin_url('options-general.php') . '?page=mailchimp'
						); ?>
						<a href="#" data-option="mailchimp_sync_set_groups_again_notice" class="mailchimp-dismiss dashicons-dismiss dashicons"><span class="screen-reader-text"><?php _e( 'Dismiss', 'mailchimp' ); ?></span></a>
					</p>
				</div>
				<style>
					.error .mailchimp-dismiss {float:right; text-decoration: none; color:#dc3232 }
					.error .mailchimp-dismiss:hover {color:red;}
				</style>
				<script>
					jQuery(document).ready( function( $ ) {
						$('.mailchimp-dismiss').click( function(e) {
							e.preventDefault();
							var data = {
								action: 'mailchimp_dismiss_notice',
								option: $(this).data( 'option' )
							};
							$(this).parent().parent().remove();
							$.post( ajaxurl, data );
						});

					});
				</script>
				<?php
			}
		}
	}
}
