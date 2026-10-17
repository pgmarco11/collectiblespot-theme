<?php
/** Front-end password recovery. WordPress owns the reset keys and password update. */
defined('ABSPATH') || exit;

function tcs_recovery_string($source, $key) {
    return isset($source[$key]) && is_string($source[$key]) ? wp_unslash($source[$key]) : '';
}

function tcs_recovery_page_url($slug) {
    $page = get_page_by_path($slug);
    return $page && $page->post_status === 'publish' ? get_permalink($page) : '';
}

add_filter('lostpassword_url', function ($url, $redirect) {
    return tcs_recovery_page_url('forgot-password') ?: $url;
}, 20, 2);

add_filter('retrieve_password_message', function ($message, $key, $login, $user) {
    $url = tcs_recovery_page_url('reset-password');
    if (!$url || !$message) {
        return $message;
    }
    $url = add_query_arg(['key' => $key, 'login' => $login], $url);
    return sprintf(
        "Someone requested a password reset for your account on %s.\n\nUsername: %s\n\nTo choose a new password, visit:\n%s\n\nIf you did not request this, ignore this email. Your password has not changed.\n",
        wp_specialchars_decode(get_option('blogname'), ENT_QUOTES),
        $login,
        $url
    );
}, 20, 4);

function tcs_recovery_cookie_name() {
    return 'tcs_resetpass_' . COOKIEHASH;
}

function tcs_recovery_cookie($value) {
    $path = wp_parse_url(tcs_recovery_page_url('reset-password'), PHP_URL_PATH) ?: '/';
    setcookie(tcs_recovery_cookie_name(), $value, [
        'expires' => $value === '' ? time() - 3600 : 0,
        'path' => $path,
        'secure' => is_ssl(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function tcs_recovery_credentials() {
    $raw = tcs_recovery_string($_COOKIE, tcs_recovery_cookie_name());
    $data = json_decode((string) base64_decode($raw, true), true);
    return is_array($data) && isset($data['login'], $data['key'])
        && is_string($data['login']) && is_string($data['key'])
        ? $data : ['login' => '', 'key' => ''];
}

function tcs_recovery_nonce_action($credentials) {
    return 'tcs_reset_' . hash_hmac('sha256', wp_json_encode($credentials), wp_salt('nonce'));
}

function tcs_recovery_state() {
    return $GLOBALS['tcs_recovery_state'] ?? ['errors' => new WP_Error(), 'sent' => false, 'valid' => false, 'success' => false];
}

add_action('template_redirect', function () {
    if (!is_page('forgot-password') && !is_page('reset-password')) {
        return;
    }
    nocache_headers();
    header('Referrer-Policy: no-referrer');
    header('X-Robots-Tag: noindex, nofollow', true);
    $state = ['errors' => new WP_Error(), 'sent' => false, 'valid' => false, 'success' => false];
    $GLOBALS['tcs_recovery_state'] =& $state;
    $post = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';

    if (is_page('forgot-password')) {
        if (!$post) {
            return;
        }
        if (!wp_verify_nonce(tcs_recovery_string($_POST, 'tcs_recovery_nonce'), 'tcs_forgot_password')) {
            $state['errors']->add('nonce', 'Please reload this page and try again.');
            return;
        }
        $identifier = trim(tcs_recovery_string($_POST, 'user_login'));
        if ($identifier === '') {
            $state['errors']->add('empty', 'Enter your username or email address.');
            return;
        }
        // Uniform responses protect account privacy. Throttles limit email abuse.
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $ip_key = 'tcs_forgot_ip_' . hash_hmac('sha256', $ip, wp_salt('auth'));
        $account_key = 'tcs_forgot_user_' . hash_hmac('sha256', strtolower($identifier), wp_salt('auth'));
        $attempts = (int) get_transient($ip_key);
        if ($attempts < 5 && !get_transient($account_key)) {
            set_transient($ip_key, $attempts + 1, 10 * MINUTE_IN_SECONDS);
            set_transient($account_key, 1, MINUTE_IN_SECONDS);
            retrieve_password($identifier);
        }
        $state['sent'] = true;
        return;
    }

    // Consume incoming email credentials before rendering any theme assets.
    if (!$post && (isset($_GET['key']) || isset($_GET['login']))) {
        $credentials = ['login' => tcs_recovery_string($_GET, 'login'), 'key' => tcs_recovery_string($_GET, 'key')];
        $user = check_password_reset_key($credentials['key'], $credentials['login']);
        tcs_recovery_cookie(is_wp_error($user) ? '' : base64_encode(wp_json_encode($credentials)));
        wp_safe_redirect(add_query_arg('link', is_wp_error($user) ? 'invalid' : 'ready', tcs_recovery_page_url('reset-password')));
        exit;
    }
    if (!$post && tcs_recovery_string($_GET, 'reset') === 'success') {
        $state['success'] = true;
        return;
    }
    $credentials = tcs_recovery_credentials();
    $user = check_password_reset_key($credentials['key'], $credentials['login']);
    if (is_wp_error($user)) {
        $state['errors']->add('invalid_key', 'This reset link is invalid or has expired. Please request a new one.');
        return;
    }
    $state['valid'] = true;
    if (!$post) {
        return;
    }
    if (!wp_verify_nonce(tcs_recovery_string($_POST, 'tcs_recovery_nonce'), tcs_recovery_nonce_action($credentials))) {
        $state['errors']->add('nonce', 'Please reload this page and try again.');
        return;
    }
    $password = tcs_recovery_string($_POST, 'pass1');
    if ($password !== tcs_recovery_string($_POST, 'pass2')) {
        $state['errors']->add('mismatch', 'The two passwords must match.');
    }
    if (strlen($password) > 4096) {
        $state['errors']->add('length', 'Please use a shorter password.');
    } elseif (function_exists('tcs_registration_password_errors')) {
        $policy = tcs_registration_password_errors($password);
        foreach ($policy->get_error_messages() as $message) {
            $state['errors']->add('policy', $message);
        }
    } elseif (preg_match_all('/./us', $password) < 12 || !preg_match('/[^\p{L}\p{N}\s]/u', $password)) {
        $state['errors']->add('policy', 'Use at least 12 characters and one special character.');
    }
    // Preserve WordPress and plugin password-reset validation hooks.
    do_action('validate_password_reset', $state['errors'], $user);
    if ($state['errors']->has_errors()) {
        return;
    }
    reset_password($user, $password);
    tcs_recovery_cookie('');
    wp_safe_redirect(add_query_arg('reset', 'success', tcs_recovery_page_url('reset-password')));
    exit;
}, 5);
