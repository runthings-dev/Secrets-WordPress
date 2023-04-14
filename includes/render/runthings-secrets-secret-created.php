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

            $uuid = isset($_GET['secret']) ? $_GET['secret'] : null;
            $secret = $this->manage->get_secret_meta($uuid);

            $templates = new runthings_secrets_Template_Loader();

            ob_start();

            if (is_wp_error($secret)) {
                $data = array(
                    "error_message" => $secret->get_error_message()
                );

                $templates
                    ->set_template_data($data, 'context')
                    ->get_template_part('error');
            } else {
                $data = array(
                    "secret" => $secret
                );

                $templates
                    ->set_template_data($data, 'context')
                    ->get_template_part('secret-created');
            }

            return ob_get_clean();
        }

        public function enqueue_styles()
        {
            $style_url = plugins_url('/runthings-secrets/css/runthings-secrets.css');
            wp_enqueue_style('runthings-secrets-styles', $style_url, array(), null, 'all');
        }

        public function enqueue_scripts()
        {
            $script_url = plugins_url('/runthings-secrets/js/runthings-secrets.js');
            wp_enqueue_script('runthings-secrets-script', $script_url, array(), null, true);
        }
    }
}
