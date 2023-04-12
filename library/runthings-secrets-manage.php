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

if (!class_exists('runthings_secrets_Manage')) {
    class runthings_secrets_Manage
    {
        private $crypt;

        public function __construct()
        {
            include plugin_dir_path(__FILE__) . './runthings-secrets-sodium-encryption.php';
            $this->crypt = runthings_secrets_Sodium_Encryption::get_instance();
        }

        public function get_secret($uuid)
        {
            return $this->get_secret_data($uuid);
        }

        public function get_secret_meta($uuid)
        {
            return $this->get_secret_data($uuid, 'created');
        }

        private function get_secret_data($uuid, $context = 'view')
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
                    $secret->error_message = __("This secret has expired or reached its maximum number of views.", 'runthings-secrets');

                    // Delete the secret from the database.
                    $wpdb->delete(
                        $table_name,
                        array('id' => $secret->id)
                    );
                } else {
                    // set error state
                    $secret->is_error = false;
                    $secret->error_message = "";

                    if ($context == 'view') {
                        // Increment the views count.
                        $wpdb->update(
                            $table_name,
                            array('views' => $secret->views + 1),
                            array('id' => $secret->id)
                        );

                        $this->incremement_global_views_total_stat();

                        // decrypt and display the secret to the user
                        $secret->secret = $this->crypt->decrypt($secret->secret);
                    } else {
                        // $context is meta only, so clear out the secret value
                        $secret->secret = null;
                    }
                    
                }
            } else {
                // TODO make proper class for the view secret object
                $secret = new stdClass();

                $secret->is_error = true;
                $secret->error_message = __("Invalid secret sharing URL.", 'runthings-secrets');
            }

            return $secret;
        }

        public function add_secret($secret, $max_views, $expiration)
        {
            // encrypt the secret
            $encrypted_secret = $this->crypt->encrypt($secret);

            // store the secret in the database table
            global $wpdb;
            $table_name = $wpdb->prefix . 'runthings_secrets';

            $uuid = wp_generate_uuid4();
            $created_at = current_time('mysql');

            $wpdb->insert(
                $table_name,
                array(
                    'uuid' => $uuid,
                    'secret' => $encrypted_secret,
                    'max_views' => $max_views,
                    'views' => 0,
                    'expiration' => $expiration,
                    'created_at' => $created_at
                )
            );

            $this->incremement_global_secrets_total_stat();

            return $uuid;
        }

        private function incremement_global_secrets_total_stat()
        {
            $total_count = get_option('runthings_secrets_stats_total_secrets', 0);

            update_option('runthings_secrets_stats_total_secrets', ++$total_count);
        }

        private function incremement_global_views_total_stat()
        {
            $total_count = get_option('runthings_secrets_stats_total_views', 0);

            update_option('runthings_secrets_stats_total_views', ++$total_count);
        }
    }
}
