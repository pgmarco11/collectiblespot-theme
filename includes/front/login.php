<?php
defined('ABSPATH') || exit;

function tcs_login_request_string($source, $key) {
    return isset($source[$key]) && is_string($source[$key])
        ? wp_unslash($source[$key]) : '';
}

function tcs_login_safe_destination($requested) {
    $fallback = home_url('/my-collection/');

    $requested = is_string($requested)
        ? trim($requested)
        : '';

    if ($requested === '') {
        return $fallback;
    }

    $destination = wp_validate_redirect($requested, $fallback);

    if ($destination === '') {
        return $fallback;
    }

    $host = wp_parse_url($destination, PHP_URL_HOST);

    if (
        $host &&
        strcasecmp(
            $host,
            (string) wp_parse_url(home_url('/'), PHP_URL_HOST)
        ) !== 0
    ) {
        return $fallback;
    }

    $path = untrailingslashit(
        (string) wp_parse_url($destination, PHP_URL_PATH)
    );

    $login_paths = [
        untrailingslashit(
            (string) wp_parse_url(
                home_url('/login/'),
                PHP_URL_PATH
            )
        ),
        untrailingslashit(
            (string) wp_parse_url(
                site_url('wp-login.php'),
                PHP_URL_PATH
            )
        ),
    ];

    return in_array($path, $login_paths, true)
        ? $fallback
        : $destination;
}

// Keep security reauthentication and special core login flows on WordPress's screen.
add_filter('login_url', static function ($url, $redirect, $force_reauth) {
    if ($force_reauth) {
        return $url;
    }
    $page = get_page_by_path('login');
    if (!$page || $page->post_status !== 'publish') {
        return $url;
    }
    $custom = home_url('/login/');
    return $redirect !== '' ? add_query_arg('redirect_to', $redirect, $custom) : $custom;
}, 10, 3);

function tcs_login_page_state() {
    return $GLOBALS['tcs_login_page_state'] ?? [
        'errors' => new WP_Error(),
        'username' => '',
        'remember' => false,
        'redirect' => home_url('/my-collection/'),
    ];
}

add_action('template_redirect', static function () {
    if (!is_page('login')) {
        return;
    }
    nocache_headers();
    $posted = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
    $requested = $posted
        ? tcs_login_request_string($_POST, 'redirect_to')
        : tcs_login_request_string($_GET, 'redirect_to');
    $destination = tcs_login_safe_destination($requested);
    if (is_user_logged_in()) {
        wp_safe_redirect($destination);
        exit;
    }
    $state = [
        'errors' => new WP_Error(),
        'username' => '',
        'remember' => false,
        'redirect' => $destination,
    ];
    if ($posted) {
        $state['username'] = trim(tcs_login_request_string($_POST, 'log'));
        $state['remember'] = tcs_login_request_string($_POST, 'rememberme') === 'forever';
        $password = tcs_login_request_string($_POST, 'pwd');
        $nonce = tcs_login_request_string($_POST, 'tcs_login_nonce');
        if (!wp_verify_nonce($nonce, 'tcs_login_page')) {
            $state['errors']->add('session_expired', 'Your session expired. Reload this page and try again.');
        } elseif ($state['username'] === '' || $password === '') {
            $state['errors']->add('missing_fields', 'Enter your username or registered email address and password.');
        } else {
            $user = wp_signon([
                'user_login'    => $state['username'],
                'user_password' => $password,
                'remember'      => $state['remember'],
            ], is_ssl());

            if (is_wp_error($user)) {
                $state['errors'] = $user;
            } else {
                wp_set_current_user($user->ID);

                $redirect = apply_filters(
                    'login_redirect',
                    $destination,
                    $requested,
                    $user
                );

                wp_safe_redirect(
                    tcs_login_safe_destination(
                        is_string($redirect) ? $redirect : ''
                    )
                );
                exit;
            }
        }
    }
    $GLOBALS['tcs_login_page_state'] = $state;
});
