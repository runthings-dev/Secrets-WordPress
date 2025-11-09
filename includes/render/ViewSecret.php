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

        add_action('template_redirect', [$this, 'handle_delete_submit']);
        }

        public function handle_delete_submit()
        {
            if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
                return;
            }

            if (!isset($_POST['runthings_secrets_delete_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['runthings_secrets_delete_nonce'])), 'runthings_secrets_delete')) {
                return;
            }

            if (!isset($_POST['delete_now']) || $_POST['delete_now'] !== '1') {
                return;
            }

            do_action('runthings_secrets_check_rate_limit', 'delete');

            // phpcs:disable WordPress.Security.NonceVerification.Missing
            // Nonce already checked above
            $id = isset($_POST['secret_id']) ? sanitize_text_field(wp_unslash($_POST['secret_id'])) : null;
            // phpcs:enable WordPress.Security.NonceVerification.Missing

            if ($id) {
                $this->view_manager->delete_secret($id);

                // Redirect to avoid form resubmission (POST-Redirect-GET pattern)
                $current_url = remove_query_arg('deleted');
                $redirect_url = add_query_arg('deleted', '1', $current_url);
                wp_redirect(esc_url_raw($redirect_url));
                exit;
            }
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
            $deleted = isset($_GET['deleted']) && $_GET['deleted'] === '1';
            // phpcs:enable WordPress.Security.NonceVerification.Recommended

            $secret = $this->view_manager->get_secret($id);

            if (is_wp_error($secret)) {
                return $this->handle_error($secret, $deleted);
            }

            $timezone = wp_timezone_string();

            $copy_icon = \RunthingsSecrets\CopyToClipboardIcon::get_icon('link-icon', true);
            $copy_icon_allowed_html = \RunthingsSecrets\CopyToClipboardIcon::get_allowed_html('link-icon');

            $template = new \RunthingsSecrets\TemplateLoader();

            ob_start();

            $delete_button_html = '';
            if ($secret->allow_delete) {
                $delete_button_html = $this->get_delete_button($secret);
            }

            $data = array(
                "secret" => $secret,
                "timezone" => esc_html($timezone),
                "copy_to_clipboard_icon" => $copy_icon,
                "copy_to_clipboard_icon_allowed_html" => $copy_icon_allowed_html,
                "delete_button_html" => $delete_button_html,
            );

            $template
                ->set_template_data($data, 'context')
                ->get_template_part('view-secret');

            return ob_get_clean();
        }

        private function get_delete_button($secret)
        {
            if (!$secret->allow_delete) {
                return '';
            }

            $default_button = sprintf(
                '<form method="post" class="rs-delete-form">
                    %s
                    <input type="hidden" name="secret_id" value="%s">
                    <input type="hidden" name="delete_now" value="1">
                    <button type="submit" class="rs-delete-button">%s</button>
                </form>',
                wp_nonce_field('runthings_secrets_delete', 'runthings_secrets_delete_nonce', true, false),
                esc_attr($secret->uuid),
                esc_html__('Delete Now', 'runthings-secrets')
            );

            return apply_filters('runthings_secrets_delete_button', $default_button, $secret);
        }

        private function handle_error($error, $deleted = false)
        {
            $template = new \RunthingsSecrets\TemplateLoader();

            ob_start();

            $data = array(
                "error_message" => esc_html($error->get_error_message()),
                "deleted" => $deleted
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

            $view_style_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/css/runthings-secrets-view-secret.css';
            wp_enqueue_style('runthings-secrets-view-secret-styles', $view_style_url, array(), RUNTHINGS_SECRETS_PLUGIN_VERSION, 'all');
        }

        public function enqueue_scripts()
        {
            $popper_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/vendor/tippy/popper.min.js';
            wp_enqueue_script('popper', $popper_url, array(), RUNTHINGS_SECRETS_PLUGIN_VERSION, true);

            $tippy_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/vendor/tippy/tippy-bundle.umd.min.js';
            wp_enqueue_script('tippy', $tippy_url, array('popper'), RUNTHINGS_SECRETS_PLUGIN_VERSION, true);

            $script_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/js/runthings-secrets-shared.js';
            wp_enqueue_script('runthings-secrets-shared-script', $script_url, array('tippy'), RUNTHINGS_SECRETS_PLUGIN_VERSION, true);

            $view_script_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/js/runthings-secrets-view-secret.js';
            wp_enqueue_script('runthings-secrets-view-secret-script', $view_script_url, array(), RUNTHINGS_SECRETS_PLUGIN_VERSION, true);

            $script_options = array(
                'i18n' => array(
                    'copyToClipboard' => __('Copy to clipboard', 'runthings-secrets'),
                    'copied' => __('Copied!', 'runthings-secrets'),
                    'deleteConfirm' => __('Are you sure you want to delete this secret? This action cannot be undone.', 'runthings-secrets')
                )
            );

            wp_localize_script('runthings-secrets-shared-script', 'runthings_secrets', $script_options);
        }
    }
