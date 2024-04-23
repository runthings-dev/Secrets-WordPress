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
        public function __construct()
        {
            $this->check_rate_limit();
        }

        private function check_rate_limit()
        {
            $user_ip = $_SERVER['REMOTE_ADDR'];
            $salt = wp_salt('nonce');
            $hashed_ip = hash('sha256', $user_ip . $salt);
            $transient_key = 'runthings_secrets_view_attempts_' . $hashed_ip;
            $attempts = get_transient($transient_key);

            if ($attempts >= 3) {
                wp_die('Too many requests. Please try again later.', '429 Too Many Requests', 429);
            } else {
                $new_attempts = $attempts ? $attempts + 1 : 1;
                set_transient($transient_key, $new_attempts, MINUTE_IN_SECONDS);
            }
        }
    }
}
