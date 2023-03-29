<?php
/*
Plugin Name: Secrets
Plugin URI: https://runthings.dev
Description: Share secrets securely
Version: 0.5.0
Author: Matthew Harris, runthings.dev
Author URI: https://runthings.dev/
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

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

class runthings_secrets_Plugin
{
    public function __construct()
    {
        add_action('init', [$this, 'init']);
        add_action('plugins_loaded', [$this, 'load_textdomain']);

        add_shortcode('runthings_secrets', [$this, 'add_secret_shortcode']);
        add_shortcode('runthings_secrets_view', [$this, 'view_secret_shortcode']);

        include plugin_dir_path(__FILE__) . 'runthings-secrets-options-page.php';
    }

    public function init()
    {
    }

    public function activate()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'runthings_secrets';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
          id int(11) NOT NULL AUTO_INCREMENT,
          uuid varchar(255) NOT NULL,
          secret text NOT NULL,
          max_views int(11) NOT NULL,
          views int(11) NOT NULL,
          expiration datetime NOT NULL,
          created_at datetime NOT NULL,
          PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function deactivate()
    {
        // delete table(s)
    }

    public function uninstall()
    {
    }

    public function load_textdomain()
    {
        load_plugin_textdomain('runthings-secrets', false, dirname(plugin_basename(__FILE__)) . '/languages');
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

    public function view_secret_shortcode()
    {
        $uuid = isset($_GET['secret']) ? $_GET['secret'] : null;
        $secret = $this->get_secret($uuid);

        ob_start();

        include plugin_dir_path(__FILE__) . 'templates/view-secret.php';

        return ob_get_clean();
    }

    private function form_submit_handler()
    {
        // Verify nonce field.
        if (!wp_verify_nonce($_POST['runthings_secrets_add_nonce'], 'runthings_secrets_add')) {
            return;
        }

        // Validate form inputs.
        $secret = sanitize_textarea_field($_POST['secret']);
        $expiration = sanitize_text_field($_POST['expiration']);
        $max_views = intval($_POST['max_views']);

        // Encrypt the secret.
        $encrypted_secret = $secret; // Your encryption code goes here.

        // Store the secret in the database table.
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

    private function get_secret($uuid)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'runthings_secrets';

        $secret = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE uuid = %s",
                $uuid
            )
        );

        if ($secret) {
            // Check if the secret has expired or reached its maximum number of views.
            if ($secret->expiration < current_time('mysql') || $secret->views >= $secret->max_views) {
                // set error state
                $secret->is_error = true;
                $secret->error_message = "This secret has expired or reached its maximum number of views.";

                // Delete the secret from the database.
                $wpdb->delete(
                    $table_name,
                    array('id' => $secret->id)
                );
            } else {
                // set error state
                $secret->is_error = false;
                $secret->error_message = "";

                // Increment the views count.
                $wpdb->update(
                    $table_name,
                    array('views' => $secret->views + 1),
                    array('id' => $secret->id)
                );

                // Decrypt and display the secret to the user.
                // Not needed right now as not doing anything
                // $decrypted_secret = $secret->secret; // Your decryption code goes here.
                // echo '<p>Your secret is: ' . $decrypted_secret . '</p>';
            }
        } else {
            // TODO make proper class for the view secret object
            $secret = new stdClass();

            $secret->is_error = true;
            $secret->error_message = "Invalid secret sharing URL.";
        }

        return $secret;
    }
}

$runthings_secrets = new runthings_secrets_Plugin();

register_activation_hook(__FILE__, array($runthings_secrets, 'activate'));
