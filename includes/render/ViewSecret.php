<?php

namespace RunthingsSecrets\Render;

if (!defined('WPINC')) {
    die;
}

class ViewSecret
{
    private $view_manager;

    public function __construct()
    {
        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'ViewManager.php';
        $this->view_manager = new \RunthingsSecrets\ViewManager();
        }

        public function render()
        {
            do_action('runthings_secrets_check_rate_limit', 'view');

            add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
            add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

            // phpcs:disable WordPress.Security.NonceVerification.Recommended
            // Disabling nonce verification due to the long-lived nature of public access links.
            // This code uses GUID-based security with rate limiting to handle threats.
            $id = isset($_GET['secret']) ? sanitize_text_field(wp_unslash($_GET['secret'])) : null;
            // phpcs:enable WordPress.Security.NonceVerification.Recommended

            $secret = $this->view_manager->get_secret($id);

            if (is_wp_error($secret)) {
                return $this->handle_error($secret);
            }

            $timezone = wp_timezone_string();

            $copy_icon = \RunthingsSecrets\CopyToClipboardIcon::get_icon('link-icon', true);
            $copy_icon_allowed_html = \RunthingsSecrets\CopyToClipboardIcon::get_allowed_html('link-icon');

            $template = new \RunthingsSecrets\TemplateLoader();

            ob_start();

            $data = array(
                "secret" => $secret,
                "timezone" => esc_html($timezone),
                "copy_to_clipboard_icon" => $copy_icon,
                "copy_to_clipboard_icon_allowed_html" => $copy_icon_allowed_html,
            );

            $template
                ->set_template_data($data, 'context')
                ->get_template_part('view-secret');

            return ob_get_clean();
        }

        private function handle_error($error)
        {
            $template = new \RunthingsSecrets\TemplateLoader();

            ob_start();

            $data = array(
                "error_message" => esc_html($error->get_error_message())
            );

            $template
                ->set_template_data($data, 'context')
                ->get_template_part('error');

            return ob_get_clean();
        }

        public function enqueue_styles()
        {
            $tippy_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/vendor/tippy/tippy.css';
            wp_enqueue_style('tippy', $tippy_url, array(), RUNTHINGS_SECRETS_PLUGIN_VERSION, 'all');

            $style_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/css/runthings-secrets.css';
            wp_enqueue_style('runthings-secrets-styles', $style_url, array(), RUNTHINGS_SECRETS_PLUGIN_VERSION, 'all');
        }

        public function enqueue_scripts()
        {
            $popper_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/vendor/tippy/popper.min.js';
            wp_enqueue_script('popper', $popper_url, array(), RUNTHINGS_SECRETS_PLUGIN_VERSION, true);

            $tippy_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/vendor/tippy/tippy-bundle.umd.min.js';
            wp_enqueue_script('tippy', $tippy_url, array('popper'), RUNTHINGS_SECRETS_PLUGIN_VERSION, true);

            $script_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/js/runthings-secrets.js';
            wp_enqueue_script('runthings-secrets-script', $script_url, array('tippy'), RUNTHINGS_SECRETS_PLUGIN_VERSION, true);

            $script_options = array(
                'i18n' => array(
                    'copyToClipboard' => __('Copy to clipboard', 'runthings-secrets'),
                    'copied' => __('Copied!', 'runthings-secrets')
                )
            );

            wp_localize_script('runthings-secrets-script', 'runthings_secrets', $script_options);
        }
    }
