<?php
/*
Secrets by runthings.dev

Copyright 2023 Matthew Harris

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
        private $manage;

        public function __construct()
        {
            include plugin_dir_path(__FILE__) . '../library/runthings-secrets-manage.php';
            $this->manage = new runthings_secrets_Manage();

            add_action('template_redirect', [$this, 'handle_form_submit']);
        }

        public function render()
        {
            add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue_form_styles']);
            add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue_recaptcha']);

            $templates = new runthings_secrets_Template_Loader();

            ob_start();

            $templates->get_template_part('add-secret-form');

            return ob_get_clean();
        }

        public function handle_form_submit()
        {
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
                $style_url = plugins_url('/runthings-secrets/add-secret-form.css');
                wp_enqueue_style('add-secret-form-styles', $style_url, array(), null, 'all');
            }
        }

        public function maybe_enqueue_recaptcha()
        {
            $recaptcha_enabled = get_option('runthings_secrets_recaptcha_enabled');
            $recaptcha_public_key = get_option('runthings_secrets_recaptcha_public_key');
            $recaptcha_private_key = get_option('runthings_secrets_recaptcha_private_key');
        
            if ($recaptcha_enabled && !empty($recaptcha_public_key) && !empty($recaptcha_private_key)) {
                wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . $recaptcha_public_key, [], null, true);
                
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
            if (!wp_verify_nonce($_POST['runthings_secrets_add_nonce'], 'runthings_secrets_add')) {
                return;
            }

            // validate form inputs
            $secret = sanitize_textarea_field($_POST['secret']);
            $expiration = sanitize_text_field($_POST['expiration']);
            $max_views = intval($_POST['max_views']);

            $recaptcha_enabled = get_option('runthings_secrets_recaptcha_enabled');
            $recaptcha_public_key = get_option('runthings_secrets_recaptcha_public_key');
            $recaptcha_private_key = get_option('runthings_secrets_recaptcha_private_key');

            if ($recaptcha_enabled && !empty($recaptcha_public_key) && !empty($recaptcha_private_key)) {
                if (!$this->verify_recaptcha_token()) {
                    return; // TODO - improve handling
                    // $error_message = __('reCAPTCHA verification failed, please try again.', 'runthings-secrets');
                }
            }

            return $this->manage->add_secret($secret, $max_views, $expiration);
        }

        private function verify_recaptcha_token()
        {
            $recaptcha_private_key = get_option('runthings_secrets_recaptcha_private_key');
            $recaptcha_token = $_POST['recaptcha_token'];
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
