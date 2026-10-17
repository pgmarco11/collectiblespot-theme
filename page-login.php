<?php
/**
 * Template Name: Login Template
 * Template Post Type: page
 */
defined('ABSPATH') || exit;
$state = tcs_login_page_state();
get_header();
?>
<div class="d-flex flex-column flex-md-row w-100">
    <main class="site-main flex-fill">
        <section class="registration-page tcs-login-page" aria-labelledby="tcs-login-heading">
            <div class="registration-card">
                <header class="registration-header">
                    <span class="registration-eyebrow">Welcome back to The Collectible Spot</span>
                    <h1 id="tcs-login-heading">Sign In</h1>
                    <p>Manage your collection and wishlist.</p>
                </header>

                <?php if ($state['errors']->has_errors()) : ?>
                    <div class="registration-alert registration-alert--error" role="alert">
                        <strong>We could not sign you in.</strong>
                        <ul>
                            <?php foreach ($state['errors']->get_error_messages() as $message) : ?>
                                <li><?php echo wp_kses_post($message); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form class="registration-form" method="post" action="<?php echo esc_url(home_url('/login/')); ?>">
                    <?php wp_nonce_field('tcs_login_page', 'tcs_login_nonce'); ?>
                    <input type="hidden" name="redirect_to" value="<?php echo esc_attr($state['redirect']); ?>">
                    <div class="registration-fieldset">
                        <div class="registration-field">
                            <label for="tcs-page-login-username">Username or email address</label>
                            <input id="tcs-page-login-username" name="log" type="text" value="<?php echo esc_attr($state['username']); ?>" autocomplete="username" autocapitalize="none" spellcheck="false" required>
                        </div>
                        <div class="registration-field">
                            <label for="tcs-page-login-password">Password</label>
                            <input id="tcs-page-login-password" name="pwd" type="password" autocomplete="current-password" required>
                        </div>
                        <div class="registration-check tcs-login-remember">
                            <input id="tcs-page-login-remember" name="rememberme" type="checkbox" value="forever" <?php checked($state['remember']); ?>>
                            <label for="tcs-page-login-remember">Remember me</label>
                        </div>
                        <button class="registration-submit" type="submit">
                            <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                            Sign In
                        </button>
                    </div>
                </form>

                <footer class="registration-footer tcs-login-footer">
                    <p>New here? <a href="<?php echo esc_url(home_url('/register/')); ?>">Create an account</a></p>
                    <a href="<?php echo esc_url(wp_lostpassword_url(home_url('/login/'))); ?>">Forgot your password?</a>
                    <div class="tcs-login-verification">
                        <p>Already registered but not verified?</p>
                        <a class="btn btn-secondary" href="<?php echo esc_url(home_url('/register/')); ?>">Resend verification email</a>
                    </div>
                </footer>
            </div>
        </section>
    </main>
</div>
<?php get_footer(); ?>
