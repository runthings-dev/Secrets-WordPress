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

if (!class_exists('runthings_secrets_View_Manager')) {
    class runthings_secrets_View_Manager
    {
        private $secrets;

        public function __construct()
        {
            include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'lib/runthings-secrets-secrets.php';
            $this->secrets = new runthings_secrets_Secrets();
        }

        public function get_secret($uuid)
        {
            return $this->secrets->get_secret($uuid, 'view');
        }

        public function get_secret_meta($uuid)
        {
            return $this->secrets->get_secret($uuid, 'created');
        }

        public function add_secret($secret, $max_views, $expiration_local)
        {
            return $this->secrets->add_secret($secret, $max_views, $expiration_local);
        }
    }
}

