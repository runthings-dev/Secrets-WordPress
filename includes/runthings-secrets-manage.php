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

            $cache_key = 'secret_' . $uuid;
            $cache_group = 'runthings_secrets';

            $secret = wp_cache_get($cache_key, $cache_group);
            if (!$secret) {
                // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
                // Direct query is required as only way to access custom table data
                $secret = $wpdb->get_row($wpdb->prepare("SELECT * FROM %i WHERE uuid = %s", $table_name, $uuid));
                // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

                if (!$secret) {
                    return new WP_Error('invalid_secret_url', __("Invalid secret sharing URL.", 'runthings-secrets'));
                }

                wp_cache_set($cache_key, $secret, $cache_group);
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
            $secret->days_left = $this->get_days_left($secret->formatted_expiration);
            $secret->views_left = $this->get_views_left($secret->max_views - $secret->views);

            return $secret;
        }

        private function has_expired_or_maxed_out($secret)
        {
            $expiration_datetime_utc = new DateTime($secret->expiration, new DateTimeZone('UTC'));
            $current_datetime_utc = new DateTime('now', new DateTimeZone('UTC'));

            return $expiration_datetime_utc <= $current_datetime_utc || $secret->views > $secret->max_views;
        }

        private function handle_expired_secret($secret)
        {
            global $wpdb;

            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
            // Direct query is required as only way to delete custom table data
            $result = $wpdb->delete($wpdb->prefix . 'runthings_secrets', ['id' => $secret->id]);
            // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

            if ($result) {
                wp_cache_delete('secret_' . $secret->uuid, 'runthings_secrets');
            }
        }

        private function update_secret_views($secret, $context)
        {
            if ($context != 'view') {
                return;
            }

            $cache_key = 'secret_' . $secret->uuid;
            $cache_group = 'runthings_secrets';

            $cached_secret = wp_cache_get($cache_key, $cache_group);
            if (!$cached_secret) {
                // If not cached, assume the passed $secret is up-to-date and cache it
                wp_cache_set($cache_key, $secret, $cache_group);
            } else {
                // Use the cached secret to ensure we're working with the most current data
                $secret = $cached_secret;
            }

            global $wpdb;

            $new_views = $secret->views + 1;

            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
            // Direct query is required as only way to access custom table data
            $wpdb->update(
                $wpdb->prefix . 'runthings_secrets',
                ['views' => $new_views],
                ['id' => $secret->id]
            );
            // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

            // Update the secret object and re-cache it
            $secret->views = $new_views;
            wp_cache_set($cache_key, $secret, $cache_group);

            $this->increment_global_views_total_stat();
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
            $date = new DateTime($expiration, new DateTimeZone('UTC'));
            return $date->format('Y-m-d');
        }

        private function format_expiration_date_local($expiration)
        {
            $date = new DateTime($expiration, new DateTimeZone('UTC'));
            $date->setTimezone(new DateTimeZone(wp_timezone_string()));
            return $date->format('Y-m-d');
        }

        public function add_secret($secret, $max_views, $expiration_local)
        {
            // encrypt the secret
            $encrypted_secret = $this->crypt->encrypt($secret);

            // store the secret in the database table
            global $wpdb;
            $table_name = $wpdb->prefix . 'runthings_secrets';

            $uuid = wp_generate_uuid4();

            $expiration_datetime = new DateTime($expiration_local, new DateTimeZone(wp_timezone_string()));
            $expiration_datetime->setTimezone(new DateTimeZone('UTC'));
            $expiration_utc = $expiration_datetime->format('Y-m-d H:i:s');

            $created_at_datetime = new DateTime('now', new DateTimeZone('UTC'));
            $created_at_utc = $created_at_datetime->format('Y-m-d H:i:s');

            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
            // Direct query is required as only way to insert custom table data
            $inserted = $wpdb->insert(
                $table_name,
                array(
                    'uuid' => $uuid,
                    'secret' => $encrypted_secret,
                    'max_views' => $max_views,
                    'views' => 0,
                    'expiration' => $expiration_utc,
                    'created_at' => $created_at_utc
                )
            );
            // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

            if ($inserted) {
                wp_cache_delete('runthings_secrets_count', 'runthings_secrets');

                $this->increment_global_secrets_total_stat();
            }

            return $uuid;
        }

        private function get_days_left($expiration_date)
        {
            $current_date = new DateTime('now', new DateTimeZone(wp_timezone_string()));

            // create DateTime object for the expiration date
            $expiration = new DateTime($expiration_date, new DateTimeZone(wp_timezone_string()));
            $expiration->modify('+1 day'); // add one day to include the end day fully

            $interval = $current_date->diff($expiration);
            $days_left = $interval->format('%r%a');

            /* translators: %s: Number of days left */
            $pluralized_days = sprintf(_n('%s day', '%s days', $days_left, 'runthings-secrets'), $days_left);

            return $pluralized_days;
        }

        private function get_views_left($views_difference)
        {
            /* translators: %s: Number of views left */
            $pluralized_views = sprintf(_n('%s view', '%s views', $views_difference, 'runthings-secrets'), $views_difference);

            return $pluralized_views;
        }

        private function increment_global_secrets_total_stat()
        {
            $total_count = get_option('runthings_secrets_stats_total_secrets', 0);

            update_option('runthings_secrets_stats_total_secrets', ++$total_count);
        }

        private function increment_global_views_total_stat()
        {
            $total_count = get_option('runthings_secrets_stats_total_views', 0);

            update_option('runthings_secrets_stats_total_views', ++$total_count);
        }
    }
}
