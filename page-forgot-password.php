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
<h1 id="tcs-recovery-heading">Forgot Password</h1>
<p>Enter your username or email address to request a reset link.</p>
</header>
<?php if ($state['errors']->has_errors()) : ?>
<div class="registration-alert registration-alert--error" role="alert">
<?php foreach (array_unique($state['errors']->get_error_messages()) as $message) : ?>
<p><?php echo esc_html($message); ?></p>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php if ($state['sent']) : ?>
<div class="registration-form" role="status">
<p>If an account matches those details and a request is allowed, we will send a reset link. Check your inbox and spam folder. Please wait a few minutes before trying again.</p>
</div>
<?php else : ?>
<form class="registration-form" method="post" action="<?php echo esc_url(get_permalink()); ?>">
<?php wp_nonce_field('tcs_forgot_password', 'tcs_recovery_nonce'); ?>
<div class="registration-fieldset">
<div class="registration-field">
<label for="tcs-recovery-username">Username or email address</label>
<input id="tcs-recovery-username" type="text" name="user_login" autocomplete="username" autocapitalize="none" spellcheck="false" required>
</div>
<button class="registration-submit" type="submit">Send reset link</button>
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
