<?php if ( $subscribed ): ?>
	<p class="incsub-mailchimp-updated" id="<?php echo $form_id; ?>">
		<?php echo $subscribed_placeholder; ?>
	</p>
<?php  else: ?>
	<form method="post" class="incsub-mailchimp-form <?php echo $form_class; ?>" action="#<?php echo $form_id; ?>" id="<?php echo $form_id; ?>">		        	
		<p>
			<?php echo $text; ?>
		</p>
		<?php if ( ! empty( $errors ) ): ?>
			<ul class="incsub-mailchimp-error">
			<?php foreach ( $errors as $error ): ?>
				<li><?php echo $error; ?></li>
			<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	
		<?php do_action( 'mailchimp_form_before_fields' ); ?>

		<div class="incsub-mailchimp-field-wrap">
			<label class="incsub-mailchimp-label incsub-mailchimp-label-firstname" id="incsub-mailchimp-label-firstname"><?php _e( 'First name', MAILCHIMP_LANG_DOMAIN ); ?></label>
			<input type="text" class="incsub-mailchimp-field" name="subscription-firstname" data-label="firstname" value="<?php echo $firstname; ?>" >
		</div>

		<div class="incsub-mailchimp-field-wrap">
			<label class="incsub-mailchimp-label incsub-mailchimp-label-lastname" id="incsub-mailchimp-label-lastname"><?php _e( 'Last name', MAILCHIMP_LANG_DOMAIN ); ?></label>
			<input type="text" class="incsub-mailchimp-field" name="subscription-lastname" data-label="lastname" value="<?php echo $lastname; ?>" >
		</div>

		<div class="incsub-mailchimp-field-wrap">
			<label class="incsub-mailchimp-label ncsub-mailchimp-label-email" id="incsub-mailchimp-label-email"><?php _e( 'Email', MAILCHIMP_LANG_DOMAIN ); ?></label>
			<input type="email" class="incsub-mailchimp-field" name="subscription-email" data-label="email" value="<?php echo $email; ?>" >
		</div>

		<input type="hidden" class="incsub-mailchimp-field" name="action" value="incsub_mailchimp_subscribe_user">
		<input type="hidden" class="incsub-mailchimp-field" name="form_id" value="<?php echo $form_id; ?>">

		<?php wp_nonce_field( 'mailchimp_subscribe_user' ); ?>

		<?php do_action( 'mailchimp_form_after_fields' ); ?>

		<input type="submit" class="incsub-mailchimp-submit" name="submit-subscribe-user" value="<?php echo $button_text; ?>"> <span class="mailchimp-spinner"></span>
	</form>

<?php  endif; ?>
