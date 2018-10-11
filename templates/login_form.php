<?php if ( true ) : ?>
<div class="login-form-container">
	<?php if ( $attributes['show_title'] ) : ?>
		<h2><?php _e( 'Sign In', 'k7' ); ?></h2>
	<?php endif; ?>

	<!-- Show errors if there are any -->
	<?php if ( count( $attributes['errors'] ) > 0 ) : ?>
		<?php foreach ( $attributes['errors'] as $error ) : ?>
			<p class="login-error">
				<?php echo $error; ?>
			</p>
		<?php endforeach; ?>
	<?php endif; ?>

	<!-- Show logged out message if user just logged out -->
	<?php if ( $attributes['logged_out'] ) : ?>
		<p class="login-info">
			<?php _e( 'You have signed out. Would you like to sign in again?', 'k7' ); ?>
		</p>
	<?php endif; ?>

	<?php if ( $attributes['registered'] ) : ?>
		<p class="login-info">
			<?php
				printf(
					__( 'You have successfully registered to <strong>%s</strong>. We have emailed your password to the email address you entered.', 'k7' ),
					get_bloginfo( 'name' )
				);
			?>
		</p>
	<?php endif; ?>

	<?php if ( $attributes['lost_password_sent'] ) : ?>
		<p class="login-info">
			<?php _e( 'Check your email for a link to reset your password.', 'k7' ); ?>
		</p>
	<?php endif; ?>

	<?php if ( $attributes['password_updated'] ) : ?>
		<p class="login-info">
			<?php _e( 'Your password has been changed. You can sign in now.', 'k7' ); ?>
		</p>
	<?php endif; ?>

	<?php
		wp_login_form(
			array(
				'label_username' => __( 'Email', 'k7' ),
				'label_log_in' => __( 'Sign In', 'k7' ),
				'redirect' => $attributes['redirect'],
			)
		);
	?>

	<a class="forgot-password" href="<?php echo wp_lostpassword_url(); ?>">
		<?php _e( 'Forgot your password?', 'k7' ); ?>
	</a>

</div>
<?php else : ?>
	<div class="login-form-container">
		<form method="post" action="<?php echo wp_login_url(); ?>">
			<p class="login-username">
				<label for="user_login"><?php _e( 'Email', 'k7' ); ?></label>
				<input type="text" name="log" id="user_login">
			</p>
			<p class="login-password">
				<label for="user_pass"><?php _e( 'Password', 'k7' ); ?></label>
				<input type="password" name="pwd" id="user_pass">
				<input type="hidden" name="k7_login_form_nonce" value="<?php echo wp_create_nonce('k7_login_form_nonce_field'); ?>">
			</p>
			<p class="login-submit">
				<input type="submit" name="submit_form"> value="<?php _e( 'Sign In', 'k7' ); ?>">
			</p>
		</form>
	</div>
<?php endif; ?>
