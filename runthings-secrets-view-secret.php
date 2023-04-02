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

class runthings_secrets_View_Secret
{
    public function __construct()
    {
        add_shortcode('runthings_secrets_view', [$this, 'view_secret_shortcode']);
    }

    public function view_secret_shortcode()
    {
        $uuid = isset($_GET['secret']) ? $_GET['secret'] : null;
        $secret = $this->get_secret($uuid);

        ob_start();

        include plugin_dir_path(__FILE__) . 'templates/view-secret.php';

        return ob_get_clean();
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
            if ($secret->expiration < current_time('mysql') || $secret->views > $secret->max_views) {
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

                $this->incremement_global_views_total_stat();

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

    private function incremement_global_views_total_stat()
    {
        $total_count = get_option('runthings_secrets_stats_total_views', 0);

        update_option('runthings_secrets_stats_total_views', ++$total_count);
    }
}

new runthings_secrets_View_Secret();