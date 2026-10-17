<?php
defined('ABSPATH') || exit;
if (is_user_logged_in()) {
    return;
}
?>
<div class="modal fade tcs-signin-modal" id="tcs-signin-modal" tabindex="-1" aria-labelledby="tcs-signin-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="tcs-signin-title">Sign In</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close sign-in window"></button>
            </div>
            <div class="modal-body">
                <p class="tcs-signin-intro">Sign in to manage your collection and wishlist.</p>
                <?php
                wp_login_form([
                    'redirect' => home_url('/my-collection/'),
                    'form_id' => 'tcs-signin-form',
                    'id_username' => 'tcs-signin-username',
                    'id_password' => 'tcs-signin-password',
                    'id_remember' => 'tcs-signin-remember',
                    'id_submit' => 'tcs-signin-submit',
                    'label_username' => 'Username or registered email address',
                    'label_password' => 'Password',
                    'label_remember' => 'Remember me',
                    'label_log_in' => 'Sign In',
                    'remember' => true,
                    'value_remember' => false,
                ]);
                ?>
                <div class="tcs-signin-links">
                    <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">Forgot your password?</a>
                    <a href="<?php echo esc_url(home_url('/register/')); ?>">Create an account</a>
                    <a href="<?php echo esc_url(home_url('/register/')); ?>">Resend verification email</a>
                </div>
            </div>
        </div>
    </div>
</div>
