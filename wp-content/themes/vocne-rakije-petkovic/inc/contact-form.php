<?php

if (!defined('ABSPATH')) {
    exit;
}

function vrp_contact_form_post_value($key)
{
    $value = $_POST[$key] ?? '';
    return is_scalar($value) ? wp_unslash((string) $value) : '';
}

function vrp_redirect_contact_form($status)
{
    $redirect_url = wp_get_referer();
    if (!$redirect_url) {
        $contact_page_id = absint(get_theme_mod('vrp_contact_page'));
        $redirect_url = $contact_page_id ? get_permalink($contact_page_id) : home_url('/kontakt/');
    }

    $redirect_url = remove_query_arg('contact', $redirect_url);
    wp_safe_redirect(add_query_arg('contact', $status, $redirect_url) . '#inquiry');
    exit;
}

function vrp_handle_contact_form()
{
    if (
        'POST' !== ($_SERVER['REQUEST_METHOD'] ?? '')
        || !wp_verify_nonce(vrp_contact_form_post_value('vrp_contact_nonce'), 'vrp_contact_form')
    ) {
        vrp_redirect_contact_form('invalid');
    }

    if ('' !== trim(vrp_contact_form_post_value('vrp_website'))) {
        vrp_redirect_contact_form('sent');
    }

    $remote_address = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
        ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']))
        : '';
    if ($remote_address) {
        $rate_limit_key = 'vrp_contact_rate_' . hash_hmac('sha256', $remote_address, wp_salt('auth'));
        $attempts = (int) get_transient($rate_limit_key);
        if ($attempts >= 5) {
            vrp_redirect_contact_form('limited');
        }
        set_transient($rate_limit_key, $attempts + 1, 15 * MINUTE_IN_SECONDS);
    }

    $human_answer = trim(vrp_contact_form_post_value('vrp_human_answer'));
    $human_nonce = vrp_contact_form_post_value('vrp_human_nonce');
    if (
        !preg_match('/^\d{1,2}$/', $human_answer)
        || !wp_verify_nonce($human_nonce, 'vrp_contact_human_' . $human_answer)
    ) {
        vrp_redirect_contact_form('human');
    }

    $name = sanitize_text_field(vrp_contact_form_post_value('vrp_name'));
    $email = sanitize_email(vrp_contact_form_post_value('vrp_email'));
    $phone = sanitize_text_field(vrp_contact_form_post_value('vrp_phone'));
    $message = sanitize_textarea_field(vrp_contact_form_post_value('vrp_message'));
    if (
        !$name
        || strlen($name) > 120
        || !is_email($email)
        || strlen($email) > 254
        || strlen($phone) > 40
        || !$message
        || strlen($message) > 10000
    ) {
        vrp_redirect_contact_form('invalid');
    }

    $recipient = sanitize_email(get_theme_mod('vrp_contact_email', 'info@vocnerakije.hr'));
    if (!$recipient) {
        $recipient = sanitize_email(get_option('admin_email'));
    }
    if (!$recipient || !is_email($recipient)) {
        vrp_redirect_contact_form('failed');
    }

    $subject = sprintf(__('Novi upit s kontakt-stranice — %s', 'vocne-rakije-petkovic'), $name);
    $body = sprintf("Ime: %s\nE-mail: %s\nTelefon: %s\n\n%s", $name, $email, $phone ?: '-', $message);
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: ' . $email,
    ];

    vrp_redirect_contact_form(wp_mail($recipient, $subject, $body, $headers) ? 'sent' : 'failed');
}
add_action('admin_post_nopriv_vrp_contact', 'vrp_handle_contact_form');
add_action('admin_post_vrp_contact', 'vrp_handle_contact_form');
