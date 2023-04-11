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
        public function render()
        {
            $secret = isset($_GET['secret']) ? $_GET['secret'] : '';

            $templates = new runthings_secrets_Template_Loader();

            ob_start();

            if (empty($secret) || !$this->is_valid_guid($secret)) {
                $error_message = __("Invalid secret id", 'runthings-secrets');
                $templates->get_template_part('error');
            } else {
                $templates->get_template_part('secret-created');
            }

            return ob_get_clean();
        }

        private function is_valid_guid($guid)
        {
            if (preg_match('/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i', $guid)) {
                return true;
            }
            return false;
        }
    }
}
