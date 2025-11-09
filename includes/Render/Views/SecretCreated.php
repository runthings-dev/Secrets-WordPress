<?php

namespace RunthingsSecrets\Render\Views;

use RunthingsSecrets\Render\ViewManager;
use RunthingsSecrets\Template\TemplateLoader;
use RunthingsSecrets\Template\CopyToClipboardIcon;

if (!defined('WPINC')) {
    die;
}

class SecretCreated
{
    private $view_manager;

    public function __construct()
    {
        $this->view_manager = new ViewManager();
    }

        public function render()
        {
            do_action('runthings_secrets_check_rate_limit', 'created');

            add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
            add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

            // phpcs:disable WordPress.Security.NonceVerification.Recommended
            // Disabling nonce verification due to the long-lived nature of public access links.
            // This code uses GUID-based security with rate limiting to handle threats.
            $id = isset($_GET['secret']) ? sanitize_text_field(wp_unslash($_GET['secret'])) : null;
            // phpcs:enable WordPress.Security.NonceVerification.Recommended

            $secret = $this->view_manager->get_secret_meta($id);

            if (is_wp_error($secret)) {
                return $this->handle_error($secret);
            }

            $timezone = wp_timezone_string();

            // Generate the viewing URL.
            $view_page_id = get_option('runthings_secrets_view_page');
            $viewing_url = add_query_arg('secret', $secret->uuid, get_permalink($view_page_id));

            $copy_link_icon = CopyToClipboardIcon::get_icon('link-icon', true);
            $copy_link_icon_allowed_html = CopyToClipboardIcon::get_allowed_html('link-icon');

            $copy_snippet_icon = CopyToClipboardIcon::get_icon('snippet-icon', true);
            $copy_snippet_icon_allowed_html = CopyToClipboardIcon::get_allowed_html('snippet-icon');

            $viewing_snippet = sprintf(
                /* translators: %1$s: Viewing URL, %2$s: Days left, %3$s: Views left */
                esc_html__("Get it from %1\$s (valid for %2\$s / %3\$s).", 'runthings-secrets'),
                esc_url($viewing_url),
                esc_html($secret->days_left),
                esc_html($secret->views_left)
            );

            $viewing_snippet = apply_filters('runthings_secrets_viewing_snippet', $viewing_snippet, $secret, $viewing_url);

            $template = new TemplateLoader();

            ob_start();

            $data = array(
                "secret" => $secret,
                "timezone" => esc_html($timezone),
                "viewing_url" => esc_url($viewing_url),
                "viewing_snippet" => $viewing_snippet,
                "copy_to_clipboard_link_icon" => $copy_link_icon,
                "copy_to_clipboard_link_icon_allowed_html" => $copy_link_icon_allowed_html,
                "copy_to_clipboard_snippet_icon" => $copy_snippet_icon,
                "copy_to_clipboard_snippet_icon_allowed_html" => $copy_snippet_icon_allowed_html
            );

            $template
                ->set_template_data($data, 'context')
                ->get_template_part('secret-created');

            return ob_get_clean();
        }

        private function handle_error($error)
        {
            $template = new TemplateLoader();

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

            $style_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/css/runthings-secrets-shared.css';
            wp_enqueue_style('runthings-secrets-shared-styles', $style_url, array(), RUNTHINGS_SECRETS_PLUGIN_VERSION, 'all');
        }

        public function enqueue_scripts()
        {
            $popper_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/assets/vendor/tippy/popper.min.js';
            wp_enqueue_script('popper', $popper_url, array(), RUNTHINGS_SECRETS_PLUGIN_VERSION, true);

            $tippy_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/assets/vendor/tippy/tippy-bundle.umd.min.js';
            wp_enqueue_script('tippy', $tippy_url, array('popper'), RUNTHINGS_SECRETS_PLUGIN_VERSION, true);

            $script_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/js/runthings-secrets-shared.js';
            wp_enqueue_script('runthings-secrets-shared-script', $script_url, array('tippy'), RUNTHINGS_SECRETS_PLUGIN_VERSION, true);

            $script_options = array(
                'i18n' => array(
                    'copyToClipboard' => __('Copy to clipboard', 'runthings-secrets'),
                    'copied' => __('Copied!', 'runthings-secrets')
                )
            );

            wp_localize_script('runthings-secrets-shared-script', 'runthings_secrets', $script_options);
        }
    }
