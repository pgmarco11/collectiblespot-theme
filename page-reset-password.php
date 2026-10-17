<?php
/** Slug-based password recovery template. */
defined('ABSPATH') || exit;
$state = tcs_recovery_state();
get_header();
?>
<div class="d-flex flex-column flex-md-row w-100">
<main class="site-main flex-fill">
<section class="registration-page tcs-login-page" aria-labelledby="tcs-recovery-heading">
<div class="registration-card">
<header class="registration-header">
<span class="registration-eyebrow">The Collectible Spot</span>
<h1 id="tcs-recovery-heading">Reset Password</h1>
<p>Choose a new password for your account.</p>
</header>
<?php if ($state['errors']->has_errors()) : ?>
<div class="registration-alert registration-alert--error" role="alert">
<?php foreach (array_unique($state['errors']->get_error_messages()) as $message) : ?>
<p><?php echo esc_html($message); ?></p>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php if ($state['success']) : ?>
<div class="registration-form" role="status"><p>Your password has been changed. You can now sign in with your new password.</p></div>
<?php elseif ($state['valid']) : ?>
<form class="registration-form" method="post" action="<?php echo esc_url(get_permalink()); ?>">
<?php wp_nonce_field(tcs_recovery_nonce_action(tcs_recovery_credentials()), 'tcs_recovery_nonce'); ?>
<div class="registration-fieldset">
<div class="registration-field">
<label for="tcs-new-password">New password</label>
<input id="tcs-new-password" type="password" name="pass1" autocomplete="new-password" aria-describedby="tcs-password-help" required>
<small id="tcs-password-help">Use at least 12 characters and one special character, such as ! or @.</small>
</div>
<div class="registration-field">
<label for="tcs-confirm-password">Confirm new password</label>
<input id="tcs-confirm-password" type="password" name="pass2" autocomplete="new-password" required>
</div>
<button class="registration-submit" type="submit">Save new password</button>
</div>
</form>
<?php endif; ?>
<footer class="registration-footer tcs-login-footer">
<a class="btn btn-secondary" href="<?php echo esc_url(wp_login_url()); ?>">Back to sign in</a>
<?php if (is_page('reset-password') && !$state['success']) : ?>
<a href="<?php echo esc_url(wp_lostpassword_url()); ?>">Request a new reset link</a>
<?php endif; ?>
</footer>
</div>
</section>
</main>
</div>
<?php get_footer(); ?>
