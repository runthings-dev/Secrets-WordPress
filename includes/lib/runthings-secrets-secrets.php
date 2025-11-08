<?php

if (!defined('WPINC')) {
    die;
}

if (!class_exists('runthings_secrets_Secrets')) {
    class runthings_secrets_Secrets
    {
        private $crypt;

        public function __construct()
        {
            include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'runthings-secrets-sodium-encryption.php';
            $this->crypt = runthings_secrets_Sodium_Encryption::get_instance();
        }

        /**
         * Get secret data from database
         *
         * @param string $uuid The secret UUID
         * @param string $context 'view' to decrypt secret and increment views, 'created' for metadata only
         * @return object|WP_Error Secret object or WP_Error on failure
         */
        public function get_secret($uuid, $context = 'view')
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
                $this->delete_secret($secret);
                return new WP_Error('secret_expired', __("This secret has expired or reached its maximum number of views.", 'runthings-secrets'));
            }

            if ($context == 'view') {
                $this->increment_views($secret);
                $secret->secret = $this->crypt->decrypt($secret->secret);
            } else {
                // Clear out the secret value if context is meta only
                $secret->secret = null;
            }

            // Add formatted fields
            $secret->formatted_expiration_gmt = $this->format_expiration_date_gmt($secret->expiration);
            $secret->formatted_expiration = $this->format_expiration_date_local($secret->expiration);
            $secret->days_left = $this->get_days_left($secret->formatted_expiration);
            $secret->views_left = $this->get_views_left($secret->max_views - $secret->views);

            return $secret;
        }

        /**
         * Add a new secret to the database
         *
         * @param string $secret The secret text to store
         * @param int $max_views Maximum number of views allowed
         * @param string $expiration_local Expiration date in local timezone
         * @return string UUID of the created secret
         */
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

        /**
         * Delete a secret from the database
         *
         * @param object $secret The secret object to delete
         * @return bool True on success, false on failure
         */
        public function delete_secret($secret)
        {
            global $wpdb;

            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
            // Direct query is required as only way to delete custom table data
            $result = $wpdb->delete($wpdb->prefix . 'runthings_secrets', ['id' => $secret->id]);
            // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

            if ($result) {
                wp_cache_delete('secret_' . $secret->uuid, 'runthings_secrets');
                return true;
            }

            return false;
        }

        /**
         * Increment view count for a secret
         *
         * @param object $secret The secret object to update
         * @return void
         */
        private function increment_views($secret)
        {
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

        /**
         * Check if secret has expired or reached max views
         *
         * @param object $secret The secret object to check
         * @return bool True if expired or maxed out
         */
        private function has_expired_or_maxed_out($secret)
        {
            $expiration_datetime_utc = new DateTime($secret->expiration, new DateTimeZone('UTC'));
            $current_datetime_utc = new DateTime('now', new DateTimeZone('UTC'));

            return $expiration_datetime_utc <= $current_datetime_utc || $secret->views > $secret->max_views;
        }

        /**
         * Increment global secrets total stat
         *
         * @return void
         */
        private function increment_global_secrets_total_stat()
        {
            $total_count = get_option('runthings_secrets_stats_total_secrets', 0);
            update_option('runthings_secrets_stats_total_secrets', ++$total_count);
        }

        /**
         * Increment global views total stat
         *
         * @return void
         */
        private function increment_global_views_total_stat()
        {
            $total_count = get_option('runthings_secrets_stats_total_views', 0);
            update_option('runthings_secrets_stats_total_views', ++$total_count);
        }

        /**
         * Format expiration date as GMT
         *
         * @param string $expiration UTC datetime string
         * @return string Formatted date Y-m-d
         */
        private function format_expiration_date_gmt($expiration)
        {
            $date = new DateTime($expiration, new DateTimeZone('UTC'));
            return $date->format('Y-m-d');
        }

        /**
         * Format expiration date in local timezone
         *
         * @param string $expiration UTC datetime string
         * @return string Formatted date Y-m-d
         */
        private function format_expiration_date_local($expiration)
        {
            $date = new DateTime($expiration, new DateTimeZone('UTC'));
            $date->setTimezone(new DateTimeZone(wp_timezone_string()));
            return $date->format('Y-m-d');
        }

        /**
         * Get days left until expiration
         *
         * @param string $expiration_date Formatted expiration date
         * @return string Pluralized days left string
         */
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

        /**
         * Get views left
         *
         * @param int $views_difference Number of views remaining
         * @return string Pluralized views left string
         */
        private function get_views_left($views_difference)
        {
            /* translators: %s: Number of views left */
            $pluralized_views = sprintf(_n('%s view', '%s views', $views_difference, 'runthings-secrets'), $views_difference);

            return $pluralized_views;
        }
    }
}

