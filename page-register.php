<?php
/**
 * Template Name: User Registration
 * Template Post Type: page
 */

if (!defined('ABSPATH')) {
    exit;
}

nocache_headers();

/*
 * Administrators may preview the registration page.
 * Other logged-in users are redirected to their collection.
 */
$is_admin_preview = (
    is_user_logged_in() &&
    current_user_can('manage_options')
);

if (is_user_logged_in() && !$is_admin_preview) {
    $collection_url = get_post_type_archive_link('collection');

    wp_safe_redirect(
        $collection_url ?: home_url('/')
    );
    exit;
}

$registration_errors = new WP_Error();

$username = '';
$email    = '';

if (
    !$is_admin_preview &&
    'POST' === $_SERVER['REQUEST_METHOD'] &&
    isset($_POST['tcs_registration_action'])
) {
    /*
     * CSRF protection.
     */
    if (
        !isset($_POST['tcs_registration_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(
                wp_unslash($_POST['tcs_registration_nonce'])
            ),
            'tcs_register_user'
        )
    ) {
        $registration_errors->add(
            'invalid_nonce',
            'Your session expired. Please reload the page and try again.'
        );
    }

    /*
     * Respect WordPress Settings → General → Membership.
     */
    if (!get_option('users_can_register')) {
        $registration_errors->add(
            'registration_disabled',
            'New account registration is currently unavailable.'
        );
    }

    /*
     * Basic honeypot field for automated bots.
     */
    $website = isset($_POST['website'])
    ? trim((string) wp_unslash($_POST['website']))
    : '';

    if ('' !== $website) {
        $registration_errors->add(
            'spam_detected',
            'Registration could not be completed.'
        );
    }

    $username = isset($_POST['username'])
        ? sanitize_user(
            wp_unslash($_POST['username']),
            true
        )
        : '';

    $email = isset($_POST['email'])
        ? sanitize_email(
            wp_unslash($_POST['email'])
        )
        : '';

    $password = isset($_POST['password'])
        ? (string) wp_unslash($_POST['password'])
        : '';

    $password_confirm = isset($_POST['password_confirm'])
        ? (string) wp_unslash($_POST['password_confirm'])
        : '';

    $accepted_terms = isset($_POST['accept_terms']);

    /*
     * Simple per-IP rate limit.
     */
    $remote_address = isset($_SERVER['REMOTE_ADDR'])
        ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']))
        : 'unknown';

    $rate_limit_key = 'tcs_registration_' . md5($remote_address);
    $attempts       = (int) get_transient($rate_limit_key);

    if ($attempts >= 5) {
        $registration_errors->add(
            'too_many_attempts',
            'Too many registration attempts. Please wait ten minutes and try again.'
        );
    } else {
        set_transient(
            $rate_limit_key,
            $attempts + 1,
            10 * MINUTE_IN_SECONDS
        );
    }

    /*
     * Validate the submitted account information.
     */
    if ('' === $username) {
        $registration_errors->add(
            'empty_username',
            'Please enter a username.'
        );
    } elseif (strlen($username) < 3) {
        $registration_errors->add(
            'short_username',
            'Your username must contain at least three characters.'
        );
    } elseif (username_exists($username)) {
        $registration_errors->add(
            'username_exists',
            'That username is already registered.'
        );
    }

    if (!is_email($email)) {
        $registration_errors->add(
            'invalid_email',
            'Please enter a valid email address.'
        );
    } elseif (email_exists($email)) {
        $registration_errors->add(
            'email_exists',
            'That email address is already registered.'
        );
    }

    if (strlen($password) < 10) {
        $registration_errors->add(
            'weak_password',
            'Your password must contain at least ten characters.'
        );
    }

    if ($password !== $password_confirm) {
        $registration_errors->add(
            'password_mismatch',
            'The passwords do not match.'
        );
    }

    $privacy_policy_url = get_privacy_policy_url();

    if ($privacy_policy_url && !$accepted_terms) {
        $registration_errors->add(
            'terms_required',
            'You must accept the privacy policy to create an account.'
        );
    }

    /*
     * Create the user only when validation succeeds.
     */
    if (!$registration_errors->has_errors()) {
        $user_id = wp_insert_user([
            'user_login' => $username,
            'user_email' => $email,
            'user_pass'  => $password,
            'role'       => get_option('default_role', 'subscriber'),
        ]);

        if (is_wp_error($user_id)) {
            foreach ($user_id->get_error_messages() as $message) {
                $registration_errors->add(
                    'registration_failed',
                    $message
                );
            }
        } else {
            /*
             * Sign the new user in.
             */
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id, true);

            /**
             * Allow other plugins to react to registration.
             */
            do_action('tcs_user_registered', $user_id);

            delete_transient($rate_limit_key);

            $collection_url = get_post_type_archive_link('collection');

            wp_safe_redirect(
                add_query_arg(
                    'registration',
                    'success',
                    $collection_url ?: home_url('/')
                )
            );
            exit;
        }
    }
}

get_header();
?>

<div class="d-flex flex-column flex-md-row w-100">
    <main class="site-main flex-fill">
        <section class="registration-page">
            <div class="registration-card">
                <header class="registration-header">
                    <span class="registration-eyebrow">
                        Join The Collectible Spot
                    </span>

                    <h1>Create Your Account</h1>

                    <p>
                        Create an account to manage your comic collection
                        and wishlist.
                    </p>
                </header>

                <?php if ($is_admin_preview) : ?>
                    <div
                        class="registration-alert registration-alert--preview"
                        role="status"
                    >
                        <strong>Administrator preview:</strong>
                        You can review this page, but registration submission is disabled
                        while you are logged in.
                    </div>
                <?php endif; ?>

                <?php if ($registration_errors->has_errors()) : ?>
                    <div
                        class="registration-alert registration-alert--error"
                        role="alert"
                        aria-live="assertive"
                    >
                        <strong>Please correct the following:</strong>

                        <ul>
                            <?php
                            foreach (
                                $registration_errors->get_error_messages()
                                as $message
                            ) :
                                ?>
                                <li><?php echo esc_html($message); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!get_option('users_can_register')) : ?>
                    <div
                        class="registration-alert registration-alert--error"
                        role="alert"
                    >
                        New account registration is currently unavailable.
                    </div>
                <?php else : ?>
                    <form
                        class="registration-form"
                        method="post"
                        action="<?php echo esc_url(get_permalink()); ?>"
                    >
                        <?php
                        wp_nonce_field(
                            'tcs_register_user',
                            'tcs_registration_nonce'
                        );
                        ?>

                        <input
                            type="hidden"
                            name="tcs_registration_action"
                            value="register"
                        >

                        <fieldset
                            class="registration-fieldset"
                            <?php disabled($is_admin_preview); ?>
                        >

                        <div
                            class="registration-honeypot"
                            aria-hidden="true"
                        >
                            <label for="website">
                                Leave this field empty
                            </label>

                            <input
                                id="website"
                                name="website"
                                type="text"
                                tabindex="-1"
                                autocomplete="off"
                            >
                        </div>

                        <div class="registration-field">
                            <label for="registration-username">
                                Username
                            </label>

                            <input
                                id="registration-username"
                                name="username"
                                type="text"
                                value="<?php echo esc_attr($username); ?>"
                                minlength="3"
                                maxlength="60"
                                autocomplete="username"
                                required
                            >

                            <small>
                                Use at least three letters or numbers.
                            </small>
                        </div>

                        <div class="registration-field">
                            <label for="registration-email">
                                Email address
                            </label>

                            <input
                                id="registration-email"
                                name="email"
                                type="email"
                                value="<?php echo esc_attr($email); ?>"
                                maxlength="100"
                                autocomplete="email"
                                required
                            >
                        </div>

                        <div class="registration-field">
                            <label for="registration-password">
                                Password
                            </label>

                            <input
                                id="registration-password"
                                name="password"
                                type="password"
                                minlength="10"
                                autocomplete="new-password"
                                required
                            >

                            <small>
                                Use at least ten characters.
                            </small>
                        </div>

                        <div class="registration-field">
                            <label for="registration-password-confirm">
                                Confirm password
                            </label>

                            <input
                                id="registration-password-confirm"
                                name="password_confirm"
                                type="password"
                                minlength="10"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <?php
                        $privacy_policy_url = get_privacy_policy_url();

                        if ($privacy_policy_url) :
                            ?>
                            <div class="registration-check">
                                <input
                                    id="registration-terms"
                                    name="accept_terms"
                                    type="checkbox"
                                    value="1"
                                    required
                                >

                                <label for="registration-terms">
                                    I agree to the
                                    <a
                                        href="<?php echo esc_url($privacy_policy_url); ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        privacy policy
                                    </a>.
                                </label>
                            </div>
                        <?php endif; ?>

                        <button
                            class="registration-submit"
                            type="submit"
                        >
                            <i
                                class="bi bi-person-plus"
                                aria-hidden="true"
                            ></i>

                            Create Account
                        </button>
                    </form>
                <?php endif; ?>

                <footer class="registration-footer">
                    <p>
                        Already have an account?

                        <a href="<?php echo esc_url(wp_login_url()); ?>">
                            Sign in
                        </a>
                    </p>
                </footer>
            </div>
        </section>
    </main>
</div>

<?php get_footer(); ?>