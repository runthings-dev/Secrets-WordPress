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

if (!class_exists('runthings_secrets_Manage')) {
    class runthings_secrets_Manage
    {
        private $crypt;

        public function __construct()
        {
            include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'runthings-secrets-sodium-encryption.php';
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
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[4][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid)) {
                return new WP_Error('invalid_uuid_format', __("Invalid UUID format.", 'runthings-secrets'));
            }

            global $wpdb;
            $table_name = $wpdb->prefix . 'runthings_secrets';
            $secret = $wpdb->get_row($wpdb->prepare("SELECT * FROM %i WHERE uuid = %s", $table_name, $uuid));

            if (!$secret) {
                return new WP_Error('invalid_secret_url', __("Invalid secret sharing URL.", 'runthings-secrets'));
            }

            // Check if the secret has expired or reached its maximum number of views.
            if ($this->has_expired_or_maxed_out($secret)) {
                $this->handle_expired_secret($secret);
                return new WP_Error('secret_expired', __("This secret has expired or reached its maximum number of views.", 'runthings-secrets'));
            } else {
                $this->update_secret_views($secret, $context);
                $this->apply_secret_value($secret, $context);
            }

            $secret->formatted_expiration_gmt = $this->format_expiration_date_gmt($secret->expiration);
            $secret->formatted_expiration = $this->format_expiration_date_local($secret->expiration);
            $secret->days_left = $this->get_days_left($secret->expiration);
            $secret->views_left = $this->get_views_left($secret->max_views - $secret->views);
            return $secret;
        }

        private function has_expired_or_maxed_out($secret)
        {
            return $secret->expiration < current_time('mysql') || $secret->views > $secret->max_views;
        }

        private function handle_expired_secret($secret)
        {
            global $wpdb;
            $wpdb->delete($wpdb->prefix . 'runthings_secrets', ['id' => $secret->id]);
        }

        private function update_secret_views($secret, $context)
        {
            if ($context != 'view') {
                return;
            }

            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'runthings_secrets',
                ['views' => $secret->views + 1],
                ['id' => $secret->id]
            );

            $this->incremement_global_views_total_stat();
        }

        private function apply_secret_value($secret, $context)
        {
            if ($context == 'view') {
                // decrypt and display the secret to the user
                $secret->secret = $this->crypt->decrypt($secret->secret);
            } else {
                // Clear out the secret value if context is meta only
                $secret->secret = null;
            }
        }

        private function format_expiration_date_gmt($expiration)
        {
            $timestamp = strtotime($expiration);
            return gmdate('Y-m-d', $timestamp);
        }

        private function format_expiration_date_local($expiration)
        {
            $timestamp = strtotime($expiration);
            $timezone = get_option('timezone_string') ? new DateTimeZone(get_option('timezone_string')) : new DateTimeZone('UTC');
            $date = new DateTime();
            $date->setTimestamp($timestamp);
            $date->setTimezone($timezone);
            return $date->format('Y-m-d');
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

        private function get_days_left($expiration_date)
        {
            $current_date = new DateTime(current_time('mysql'));

            // create DateTime object for the expiration date and add one day to include the end day fully
            $expiration = new DateTime($expiration_date);
            $expiration->modify('+1 day');

            $interval = $current_date->diff($expiration);
            $days_left = $interval->format('%r%a');

            $pluralized_days = sprintf(_n('%s day', '%s days', $days_left, 'runthings-secrets'), $days_left);

            return $pluralized_days;
        }

        private function get_views_left($views_difference)
        {
            $pluralized_views = sprintf(_n('%s view', '%s views', $views_difference, 'runthings-secrets'), $views_difference);

            return $pluralized_views;
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
