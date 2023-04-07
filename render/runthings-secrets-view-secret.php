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

if (!class_exists('runthings_secrets_View_Secret')) {
    class runthings_secrets_View_Secret
    {
        private $manage;

        public function __construct()
        {
            include plugin_dir_path(__FILE__) . '../library/runthings-secrets-manage.php';
            $this->manage = new runthings_secrets_Manage();
        }

        public function render()
        {
            $uuid = isset($_GET['secret']) ? $_GET['secret'] : null;
            $secret = $this->manage->get_secret($uuid);

            ob_start();

            include plugin_dir_path(__FILE__) . '../templates/view-secret.php';

            return ob_get_clean();
        }
    }
}