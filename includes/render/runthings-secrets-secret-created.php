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

if (!class_exists('runthings_secrets_Secret_Created')) {
    class runthings_secrets_Secret_Created
    {
        private $manage;

        public function __construct()
        {
            include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'runthings-secrets-manage.php';
            $this->manage = new runthings_secrets_Manage();
        }

        public function render()
        {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
            add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

            $uuid = isset($_GET['secret']) ? sanitize_text_field($_GET['secret']) : null;
            $secret = $this->manage->get_secret_meta($uuid);

            if (is_wp_error($secret)) {
                return $this->handle_error($secret);
            }

            // Generate the viewing URL.
            $view_page_id = get_option('runthings_secrets_view_page');
            $viewing_url = get_permalink($view_page_id) . '?secret=' . $secret->uuid;

            $template = new runthings_secrets_Template_Loader();

            ob_start();

            $data = array(
                "secret" => $secret,
                "viewing_url" => $viewing_url
            );

            $template
                ->set_template_data($data, 'context')
                ->get_template_part('secret-created');

            return ob_get_clean();
        }

        private function handle_error($error)
        {
            $template = new runthings_secrets_Template_Loader();

            ob_start();

            $data = array(
                "error_message" => $error->get_error_message()
            );

            $template
                ->set_template_data($data, 'context')
                ->get_template_part('error');

            return ob_get_clean();
        }

        public function enqueue_styles()
        {
            $tippy_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/vendor/tippy/tippy.css';
            wp_enqueue_style('tippy', $tippy_url, array(), null, 'all');

            $style_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/css/runthings-secrets.css';
            wp_enqueue_style('runthings-secrets-styles', $style_url, array(), null, 'all');
        }

        public function enqueue_scripts()
        {
            $popper_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/vendor/tippy/popper.min.js';
            wp_enqueue_script('popper', $popper_url, array(), null, true);

            $tippy_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/vendor/tippy/tippy-bundle.umd.min.js';
            wp_enqueue_script('tippy', $tippy_url, array('popper'), null, true);

            $script_url = RUNTHINGS_SECRETS_PLUGIN_URL . '/js/runthings-secrets.js';
            wp_enqueue_script('runthings-secrets-script', $script_url, array('tippy'), null, true);
        }
    }
}
