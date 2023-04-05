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
        public function render()
        {
            add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue_form_styles']);
            add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue_recaptcha']);

            ob_start();

            include plugin_dir_path(__FILE__) . '../templates/add-secret-form.php';

            $this->maybe_add_recaptcha_setup_script();

            if (isset($_POST['secret'])) {
                $uuid = $this->form_submit_handler();
                include plugin_dir_path(__FILE__) . '../templates/secret-created.php';
            }

            return ob_get_clean();
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
                wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . $recaptcha_public_key);
            }
        }

        public function maybe_add_recaptcha_setup_script()
        {
            $recaptcha_enabled = get_option('runthings_secrets_recaptcha_enabled');
            $recaptcha_public_key = get_option('runthings_secrets_recaptcha_public_key');
            $recaptcha_private_key = get_option('runthings_secrets_recaptcha_private_key');

            if ($recaptcha_enabled && !empty($recaptcha_public_key) && !empty($recaptcha_private_key)) {
?>
                <script>
                    grecaptcha.ready(function() {
                        grecaptcha.execute('<?php echo $recaptcha_public_key; ?>', {
                            action: 'add_secret'
                        }).then(function(token) {
                            document.getElementById('recaptcha_token').value = token;
                        });
                    });
                </script>
<?php
            }
        }

        private function form_submit_handler()
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

            // encrypt the secret
            $encrypted_secret = $secret; // encryption code goes here

            // store the secret in the database table
            global $wpdb;
            $table_name = $wpdb->prefix . 'runthings_secrets';

            $uuid = wp_generate_uuid4();
            $views = 0;
            $created_at = current_time('mysql');

            $wpdb->insert(
                $table_name,
                array(
                    'uuid' => $uuid,
                    'secret' => $encrypted_secret,
                    'max_views' => $max_views,
                    'views' => $views,
                    'expiration' => $expiration,
                    'created_at' => $created_at
                )
            );

            $this->incremement_global_secrets_total_stat();

            return $uuid;
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
                if ($response_body['success'] && $response_body['score'] >= 0.5) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        private function incremement_global_secrets_total_stat()
        {
            $total_count = get_option('runthings_secrets_stats_total_secrets', 0);

            update_option('runthings_secrets_stats_total_secrets', ++$total_count);
        }
    }

    $runthings_secrets_add_secret = new runthings_secrets_Add_Secret();
}
