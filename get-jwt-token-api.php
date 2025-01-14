<?php
/**
 * Plugin Name: get-jwt-token REST API
 * Description: Adds the get-jwt-token functionality. So you can get a jwt token (and some details) from the user that is currently logged into wordpress. ATTENTION: No guarantee that this is not a flawed concept. No Nonce-Renewal: Token lives a week.
 * Version: 1.0
 * Author: tofi
 * Author: chatgpt
 */
require_once ABSPATH . 'vendor/autoload.php';

if (!class_exists('Firebase\\JWT\\JWT')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>';
        _e('The Firebase JWT library is missing. Please run <code>composer install</code> in the plugin directory.', 'text-domain');
        echo '</p></div>';
    });
    return;
}

use Firebase\JWT\JWT;

// Your custom PHP code here
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/token', [
        'methods'             => 'POST',
        'callback'            => 'generate_token_for_logged_in_user',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ]);
    register_rest_route('custom/v1', '/verify', [
        'methods'             => 'POST',
        'callback'            => 'verify_jwt_token',
        'permission_callback' => '__return_true',
    ]);
});

function pass_rest_nonce_to_vue() {
            echo '<script>';
                echo 'window.wpRestNonce = "' . esc_js( wp_create_nonce( 'wp_rest' ) ) . '";';
                echo '</script>';
}
add_action( 'wp_head', 'pass_rest_nonce_to_vue' );

function verify_jwt_token(WP_REST_Request $request) {
    $token = $request->get_param('token');
    $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;

    if (!$secret_key) {
        return new WP_Error('jwt_auth_bad_config', __('JWT is not configured properly.', 'text-domain'));
    }

    try {
        $decoded = Firebase\JWT\JWT::decode($token, new Firebase\JWT\Key($secret_key, 'HS256'));
        return ['success' => true, 'data' => $decoded];
    } catch (Exception $e) {
        return new WP_Error('jwt_invalid', __('Invalid token.', 'text-domain'));
    }
}

function generate_token_for_logged_in_user() {
    $user = wp_get_current_user();

    if (!$user->ID) {
        return new WP_Error(
            'rest_forbidden',
            __('You must be logged in to generate a token.', 'text-domain'),
            ['status' => 403]
        );
    }

    $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;

    if (!$secret_key) {
        return new WP_Error('jwt_auth_bad_config', __('JWT is not configured properly.', 'text-domain'));
    }

    $issued_at  = time();
    $not_before = $issued_at;
    $expire     = $issued_at + (DAY_IN_SECONDS * 7); // 7-day token validity

    $payload = [
        'iss'  => get_bloginfo('url'),
        'iat'  => $issued_at,
        'nbf'  => $not_before,
        'exp'  => $expire,
        'data' => [
            'user' => [
                    'id' => $user->ID,
                    'roles' => $user->roles,
                    'user_id' => $user->ID,
                    'email' => $user->user_email,
                    'user_nicename' => $user->user_nicename,
                    'user_display_name' => $user->display_name,
            ],
        ],
    ];

    $token = JWT::encode($payload, $secret_key, 'HS256');

    return [
        'token'       => $token,
        'user_email'  => $user->user_email,
        'user_nicename' => $user->user_nicename,
        'user_display_name' => $user->display_name,
    ];
}
