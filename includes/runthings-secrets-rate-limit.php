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

if (!class_exists('runthings_secrets_Rate_Limit')) {
    class runthings_secrets_Rate_Limit
    {
        private $allowed_renderers = ['view', 'created', 'add'];

        public function __construct()
        {
            add_action('runthings_secrets_check_rate_limit', [$this, 'handle_action'], 10, 1);
        }

        /**
         * Hook handler to check if the rate limit can be checked
         */
        public function handle_action($renderer)
        {
            if (!in_array($renderer, $this->allowed_renderers, true)) {
                wp_die(
                    esc_html__('Invalid renderer specified.', 'runthings-secrets'),
                    esc_html__('Invalid Request', 'runthings-secrets'),
                    403
                );
            }

            if (!did_action('runthings_secrets_check_rate_limit')) {
                wp_die(
                    esc_html__('This function is restricted to specific hook calls.', 'runthings-secrets'),
                    esc_html__('Invalid Access', 'runthings-secrets'),
                    403
                );
            }

            $this->check_rate_limit($renderer);
        }

        /**
         * Check if the request should be rate limited
         */
        private function check_rate_limit($renderer)
        {
            $rate_limit_enabled = get_option('runthings_secrets_rate_limit_enabled', 1);

            if (!$rate_limit_enabled) {
                return;
            }

            $exemption_enabled = get_option('runthings_secrets_rate_limit_exemption_enabled', 0);
            if ($exemption_enabled && is_user_logged_in() && $this->is_user_role_exempt()) {
                return;
            }

            $option_name = 'runthings_secrets_rate_limit_tries_' . sanitize_key($renderer);
            $max_attempts = get_option($option_name, 10);

            $user_ip = $this->get_user_ip();
            if (!$user_ip) {
                wp_die(
                    esc_html__('Unable to determine your IP address.', 'runthings-secrets'),
                    esc_html__('Error', 'runthings-secrets'),
                    400
                );
            }

            $salt = wp_salt('nonce');
            $hashed_ip = hash('sha256', $user_ip . $salt);
            $transient_key = 'runthings_secrets_' . sanitize_key($renderer) . '_attempts_' . $hashed_ip;
            $attempts = get_transient($transient_key);

            if ($attempts >= $max_attempts) {
                wp_die(
                    esc_html__('Too many requests. Please try again later.', 'runthings-secrets'),
                    esc_html__('429 Too Many Requests', 'runthings-secrets'),
                    429
                );
            } else {
                $new_attempts = $attempts ? $attempts + 1 : 1;
                set_transient($transient_key, $new_attempts, MINUTE_IN_SECONDS);
            }
        }

        /**
         * Get the user's IP address from REMOTE_ADDR
         * Don't use other headers as they can be spoofed.
         */
        private function get_user_ip()
        {
            if (isset($_SERVER['REMOTE_ADDR'])) {
                return sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
            }
            return null;
        }

        /**
         * Check if the current user's role is one of the exempt roles.
         */
        private function is_user_role_exempt()
        {
            $exempt_roles = get_option('runthings_secrets_rate_limit_exemption_roles', []);
            if (!is_array($exempt_roles) || empty($exempt_roles)) {
                return false;
            }

            $current_user = wp_get_current_user();
            foreach ($current_user->roles as $role) {
                if (in_array($role, $exempt_roles, true)) {
                    return true;
                }
            }
            return false;
        }
    }

    new runthings_secrets_Rate_Limit();
}
