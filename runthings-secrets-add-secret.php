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

class runthings_secrets_Add_Secret
{
    public function __construct()
    {
        add_shortcode('runthings_secrets', [$this, 'add_secret_shortcode']);
    }

    public function add_secret_shortcode()
    {
        ob_start();

        include plugin_dir_path(__FILE__) . 'templates/add-secret-form.php';

        if (isset($_POST['secret'])) {
            $uuid = $this->form_submit_handler();
            include plugin_dir_path(__FILE__) . 'templates/secret-created.php';
        }

        return ob_get_clean();
    }

    private function form_submit_handler()
    {
        if (!wp_verify_nonce($_POST['runthings_secrets_add_nonce'], 'runthings_secrets_add')) {
            return;
        }

        // validate form inputs
        $secret = sanitize_textarea_field($_POST['secret']);
        $expiration = sanitize_text_field($_POST['expiration']);
        $max_views = intval($_POST['max_views']);

        // encrypt the secret
        $encrypted_secret = $secret; // encryption code goes here

        // store the secret in the database table
        global $wpdb;
        $table_name = $wpdb->prefix . 'runthings_secrets';

        $uuid = wp_generate_uuid4();
        $views = 0;
        $created_at = current_time('mysql');

        $wpdb->insert(
            $table_name,
            array(
                'uuid' => $uuid,
                'secret' => $encrypted_secret,
                'max_views' => $max_views,
                'views' => $views,
                'expiration' => $expiration,
                'created_at' => $created_at
            )
        );

        return $uuid;
    }
}