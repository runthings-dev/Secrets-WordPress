<?php

namespace RunthingsSecrets\Render;

if (!defined('WPINC')) {
    die;
}

class AddSecret
{
    private $view_manager;

    public function __construct()
    {
        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'ViewManager.php';
        $this->view_manager = new \RunthingsSecrets\ViewManager();

            add_action('template_redirect', [$this, 'handle_form_submit']);
        }

        public function render()
        {
            do_action('runthings_secrets_check_rate_limit', 'add');

            add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue_form_styles']);
            add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue_form_scripts']);
            add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue_recaptcha']);

            $default_expiration_local = new \DateTime('+7 days', new \DateTimeZone(wp_timezone_string()));
            $default_max_views = 5;
            $minimum_date_local = new \DateTime('+1 days', new \DateTimeZone(wp_timezone_string()));
            $timezone = wp_timezone_string();

            // Calculate validation thresholds
            $max_views_warning_threshold = apply_filters('runthings_secrets_max_views_warning_threshold', 25);

            // Calculate the default warning date (6 months from now) and allow filtering
            $default_warning_date = new \DateTime('now', new \DateTimeZone(wp_timezone_string()));
            $default_warning_date->add(new \DateInterval('P6M'));
            $expiration_warning_date_string = apply_filters('runthings_secrets_expiration_warning_date', $default_warning_date->format('Y-m-d'));

            $template = new \RunthingsSecrets\TemplateLoader();

            ob_start();

            $data = array(
                "default_expiration" => esc_attr($default_expiration_local->format('Y-m-d')),
                "default_max_views" => esc_attr($default_max_views),
                "minimum_date" => esc_attr($minimum_date_local->format('Y-m-d')),
                "timezone" => esc_attr($timezone),
                "expiration_warning_date" => esc_attr($expiration_warning_date_string),
                "max_views_warning_threshold" => esc_attr($max_views_warning_threshold),
            );

            $template
                ->set_template_data($data, 'context')
                ->get_template_part('add-secret-form');

            return ob_get_clean();
        }

        public function handle_form_submit()
        {
            if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
                return;
            }

            if (!isset($_POST['runthings_secrets_add_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['runthings_secrets_add_nonce'])), 'runthings_secrets_add')) {
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
                    $redirect_url = add_query_arg('secret', urlencode($uuid), $created_page_url);
                    wp_safe_redirect(esc_url_raw($redirect_url));
                    exit;
                }
            }
        }

        public function maybe_enqueue_form_styles()
        {
            $tippy_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/vendor/tippy/tippy.css';
            wp_enqueue_style('tippy', $tippy_url, array(), RUNTHINGS_SECRETS_PLUGIN_VERSION, 'all');

            if (get_option('runthings_secrets_enqueue_form_styles', 1) == 1) {
                $style_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/css/add-secret-form.css';
                wp_enqueue_style('add-secret-form-styles', $style_url, array(), RUNTHINGS_SECRETS_PLUGIN_VERSION, 'all');
            }
        }

        public function maybe_enqueue_recaptcha()
        {
            $recaptcha_enabled = get_option('runthings_secrets_recaptcha_enabled');
            $recaptcha_public_key = get_option('runthings_secrets_recaptcha_public_key');
            $recaptcha_private_key = get_option('runthings_secrets_recaptcha_private_key');

            if ($recaptcha_enabled && !empty($recaptcha_public_key) && !empty($recaptcha_private_key)) {
                wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . esc_attr($recaptcha_public_key), [], RUNTHINGS_SECRETS_PLUGIN_VERSION, true);

                wp_add_inline_script(
                    'google-recaptcha',
                    'grecaptcha.ready(function() {
                        grecaptcha.execute("' . esc_js($recaptcha_public_key) . '", {
                            action: "add_secret"
                        }).then(function(token) {
                            document.getElementById("recaptcha_token").value = token;
                        });
                    });'
                );
            }
        }

        public function maybe_enqueue_form_scripts()
        {
            $popper_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/vendor/tippy/popper.min.js';
            wp_enqueue_script('popper', $popper_url, array(), RUNTHINGS_SECRETS_PLUGIN_VERSION, true);

            $tippy_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/vendor/tippy/tippy-bundle.umd.min.js';
            wp_enqueue_script('tippy', $tippy_url, array('popper'), RUNTHINGS_SECRETS_PLUGIN_VERSION, true);

            $script_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/js/runthings-secrets-add-secret.js';
            wp_enqueue_script('runthings-secrets-add-secret-script', $script_url, array('tippy'), RUNTHINGS_SECRETS_PLUGIN_VERSION, true);

            $script_options = array(
                'i18n' => array(
                    'expirationWarning' => __('Consider setting a shorter expiration date. It\'s better to keep expiration dates just long enough to share with the recipient.', 'runthings-secrets')
                )
            );

            wp_localize_script('runthings-secrets-add-secret-script', 'runthings_secrets', $script_options);
        }

        private function create_secret()
        {
            // phpcs:disable WordPress.Security.NonceVerification.Missing
            // Nonce already checked in handle_form_submit()
            // DO NOT SANITIZE SECRET - it is encrypted and stored as is, and displayed safely at the end with esc_html
            // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $secret = isset($_POST['secret']) && is_string($_POST['secret']) ? wp_unslash($_POST['secret']) : '';
            // phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $expiration_local = isset($_POST['expiration']) ? sanitize_text_field(wp_unslash($_POST['expiration'])) : '';
            $max_views = isset($_POST['max_views']) ? intval($_POST['max_views']) : 5;
            $allow_delete = isset($_POST['allow_delete']) && $_POST['allow_delete'] === '1';
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

            return $this->view_manager->add_secret($secret, $max_views, $expiration_local, $allow_delete);
        }

        private function verify_recaptcha_token()
        {
            $recaptcha_private_key = get_option('runthings_secrets_recaptcha_private_key');

            // phpcs:disable WordPress.Security.NonceVerification.Missing
            // Nonce already checked in handle_form_submit()
            $recaptcha_token = isset($_POST['recaptcha_token']) ? sanitize_text_field(wp_unslash($_POST['recaptcha_token'])) : '';
            // phpcs:enable WordPress.Security.NonceVerification.Missing

            $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
                'body' => array(
                    'secret' => $recaptcha_private_key,
                    'response' => $recaptcha_token
                )
            ));

            if (!is_wp_error($response)) {
                $response_body = json_decode(wp_remote_retrieve_body($response), true);
                $score_threshold = floatval(get_option('runthings_secrets_recaptcha_score', 0.5));
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
