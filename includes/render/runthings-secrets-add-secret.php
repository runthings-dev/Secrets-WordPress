<?php
/*
Secrets by runthings.dev

Copyright 2023-2024 Matthew Harris

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 3, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if (!defined('WPINC')) {
    die;
}

if (!class_exists('runthings_secrets_Add_Secret')) {
    class runthings_secrets_Add_Secret
    {
        private $plugin_version;
        private $manage;

        public function __construct($plugin_version)
        {
            $this->plugin_version = $plugin_version;

            include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'runthings-secrets-manage.php';
            $this->manage = new runthings_secrets_Manage();

            add_action('template_redirect', [$this, 'handle_form_submit']);
        }

        public function render()
        {
            do_action('runthings_secrets_check_rate_limit', 'add');

            add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue_form_styles']);
            add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue_recaptcha']);

            $default_expiration = current_time('Y-m-d', strtotime('+7 days'));
            $default_max_views = 5;
            $current_date = current_time('Y-m-d');
            $timezone = wp_timezone_string();

            $template = new runthings_secrets_Template_Loader();

            ob_start();

            $data = array(
                "default_expiration" => $default_expiration,
                "default_max_views" => $default_max_views,
                "current_date" => $current_date,
                "timezone" => $timezone,
            );

            $template
                ->set_template_data($data, 'context')
                ->get_template_part('add-secret-form');

            return ob_get_clean();
        }

        public function handle_form_submit()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return;
            }

            if (!wp_verify_nonce($_POST['runthings_secrets_add_nonce'], 'runthings_secrets_add')) {
                return;
            }

            if (!isset($_POST['secret'])) {
                return;
            }

            $uuid = $this->create_secret();

            if ($uuid) {
                $created_page_id = get_option('runthings_secrets_created_page');
                $created_page_url = get_permalink($created_page_id);

                if ($created_page_url !== false) {
                    $redirect_url = add_query_arg('secret', $uuid, $created_page_url);
                    wp_redirect($redirect_url);
                    exit;
                }
            }
        }

        public function maybe_enqueue_form_styles()
        {
            if (get_option('runthings_secrets_enqueue_form_styles', 1) == 1) {
                $style_url = plugins_url('/runthings-secrets/css/add-secret-form.css');
                wp_enqueue_style('add-secret-form-styles', $style_url, array(), $this->plugin_version, 'all');
            }
        }

        public function maybe_enqueue_recaptcha()
        {
            $recaptcha_enabled = get_option('runthings_secrets_recaptcha_enabled');
            $recaptcha_public_key = get_option('runthings_secrets_recaptcha_public_key');
            $recaptcha_private_key = get_option('runthings_secrets_recaptcha_private_key');

            if ($recaptcha_enabled && !empty($recaptcha_public_key) && !empty($recaptcha_private_key)) {
                wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . $recaptcha_public_key, [], $this->plugin_version, true);

                wp_add_inline_script(
                    'google-recaptcha',
                    'grecaptcha.ready(function() {
                        grecaptcha.execute("' . $recaptcha_public_key . '", {
                            action: "add_secret"
                        }).then(function(token) {
                            document.getElementById("recaptcha_token").value = token;
                        });
                    });'
                );
            }
        }

        private function create_secret()
        {
            // phpcs:disable WordPress.Security.NonceVerification.Missing
            // Nonce already checked in handle_form_submit()
            // DO NOT SANITIZE SECRET - it is encrypted and stored as is, and displayed safely at the end with esc_html
            $secret = is_string($_POST['secret']) ? $_POST['secret'] : '';
            $expiration = sanitize_text_field($_POST['expiration']);
            $max_views = intval($_POST['max_views']);
            // phpcs:enable WordPress.Security.NonceVerification.Missing

            $recaptcha_enabled = get_option('runthings_secrets_recaptcha_enabled');
            $recaptcha_public_key = get_option('runthings_secrets_recaptcha_public_key');
            $recaptcha_private_key = get_option('runthings_secrets_recaptcha_private_key');

            if ($recaptcha_enabled && !empty($recaptcha_public_key) && !empty($recaptcha_private_key)) {
                if (!$this->verify_recaptcha_token()) {
                    wp_die(
                        esc_html__('reCAPTCHA verification failed, please try again.', 'runthings-secrets'),
                        esc_html__('Failed reCAPTCHA Security Checks.', 'runthings-secrets'),
                        403
                    );
                    return;
                }
            }

            return $this->manage->add_secret($secret, $max_views, $expiration);
        }

        private function verify_recaptcha_token()
        {
            $recaptcha_private_key = get_option('runthings_secrets_recaptcha_private_key');

            // phpcs:disable WordPress.Security.NonceVerification.Missing
            // Nonce already checked in handle_form_submit()
            $recaptcha_token = $_POST['recaptcha_token'];
            // phpcs:enable WordPress.Security.NonceVerification.Missing

            $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
                'body' => array(
                    'secret' => $recaptcha_private_key,
                    'response' => $recaptcha_token
                )
            ));

            if (!is_wp_error($response)) {
                $response_body = json_decode(wp_remote_retrieve_body($response), true);
                $score_threshold = get_option('runthings_secrets_recaptcha_score', 0.5);
                if ($response_body['success'] && $response_body['score'] >= $score_threshold) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }
}
