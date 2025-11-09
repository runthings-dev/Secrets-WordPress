<?php

namespace RunthingsSecrets;

/**
 * Plugin Name: RunThings Secrets
 * Plugin URI: https://runthings.dev/wordpress-plugins/secrets/
 * Repository URI: https://github.com/runthings-dev/Secrets-WordPress
 * Description: Share secrets securely
 * Version: 1.7.0
 * Author: runthingsdev
 * Author URI: https://runthings.dev/
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 6.2
 * Requires PHP: 7.2
 * Text Domain: runthings-secrets
 * Domain Path: /languages
 */
/*
Copyright 2023-2025 Matthew Harris

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

define('RUNTHINGS_SECRETS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES', plugin_dir_path(__FILE__) . "includes/");
define('RUNTHINGS_SECRETS_PLUGIN_URL', plugins_url('', __FILE__));

define('RUNTHINGS_SECRETS_PLUGIN_VERSION', '1.7.0');

class Plugin
{
    protected static $single_instance = null;

    protected function __construct()
    {
        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'integration/Integration.php';
        new \RunthingsSecrets\Integration\Integration();

        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'options/OptionsPage.php';

        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'Activation.php';
        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'CopyToClipboardIcon.php';
        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'DatabaseMigration.php';
        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'RateLimit.php';
        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'TemplateChecker.php';
        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'TemplateLoader.php';
    }

    public static function get_instance()
    {
        if (self::$single_instance === null) {
            self::$single_instance = new self();
        }

        return self::$single_instance;
    }

    public function hooks()
    {
        add_action('init', [$this, 'init'], ~PHP_INT_MAX);
    }

    public function init()
    {
        $this->load_textdomain();

        // Run database migrations
        DatabaseMigration::run();

        new \RunthingsSecrets\TemplateChecker();

        add_filter('plugin_action_links_runthings-secrets/runthings-secrets.php', [$this, 'add_settings_link']);

        add_action('init', [$this, 'schedule_clear_expired_secrets']);
        add_action('runthings_secrets_clear_expired_secrets', array($this, 'clear_expired_secrets'));
    }

    public function activate()
    {
        Activation::activate();
    }

    public function deactivate()
    {
        Activation::deactivate();
    }

    public function load_textdomain()
    {
        load_plugin_textdomain('runthings-secrets', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function add_settings_link($links)
    {
        $settings_link = '<a href="options-general.php?page=runthings-secrets">' . __('Settings', 'runthings-secrets') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function clear_expired_secrets()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'runthings_secrets';

        $current_time_datetime = new \DateTime('now', new \DateTimeZone('UTC'));

        $current_time = $current_time_datetime->format('Y-m-d H:i:s');

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
        // Direct query is required as $wpdb->delete() does not support deleting rows based on a condition
        $rows_deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM %i WHERE expiration <= %s",
                $table_name,
                $current_time
            )
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

        if ($rows_deleted > 0) {
            wp_cache_delete('runthings_secrets_count', 'runthings_secrets');
        }
    }

    public function schedule_clear_expired_secrets()
    {
        $hook = 'runthings_secrets_clear_expired_secrets';

        if (!wp_next_scheduled($hook)) {
            $timezone = wp_timezone_string();

            $targetTime = new \DateTime('today 00:15', new \DateTimeZone($timezone));

            $currentTime = new \DateTime('now', new \DateTimeZone($timezone));

            // If it's already past 00:15 today, schedule for 00:15 the next day
            if ($currentTime > $targetTime) {
                $targetTime->modify('+1 day');
            }

            wp_schedule_event($targetTime->getTimestamp(), 'daily', $hook);
        }
    }
}

if (!function_exists('runthings_secrets_uninstall')) {
    function runthings_secrets_uninstall()
    {
        // delete plugin options
        $options = array(
            'runthings_secrets_db_version',
            'runthings_secrets_first_run_completed',
            'runthings_secrets_add_page',
            'runthings_secrets_created_page',
            'runthings_secrets_view_page',
            'runthings_secrets_recaptcha_enabled',
            'runthings_secrets_recaptcha_public_key',
            'runthings_secrets_recaptcha_private_key',
            'runthings_secrets_recaptcha_score',
            'runthings_secrets_rate_limit_enabled',
            'runthings_secrets_rate_limit_tries_add',
            'runthings_secrets_rate_limit_tries_created',
            'runthings_secrets_rate_limit_tries_view',
            'runthings_secrets_rate_limit_tries_delete',
            'runthings_secrets_rate_limit_exemption_enabled',
            'runthings_secrets_rate_limit_exemption_roles',
            'runthings_secrets_enqueue_form_styles',
            'runthings_secrets_stats_total_secrets',
            'runthings_secrets_stats_total_views',
            'runthings_secrets_encryption_key',
        );
        foreach ($options as $option) {
            delete_option($option);
        }

        // drop all plugin tables
        global $wpdb;
        $tables = array(
            'runthings_secrets'
        );
        foreach ($tables as $table) {
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
            // Nothing to cache as its a drop table query
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange
            // Schema change is required to drop tables
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
            // Direct query is required as only way to remove custom tables
            $wpdb->query(
                $wpdb->prepare('DROP TABLE IF EXISTS %i', $wpdb->prefix . $table)
            );
            // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
            // phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange
            // phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching
        }
    }
}

if (!function_exists('runthings_secrets')) {
    function runthings_secrets()
    {
        return Plugin::get_instance();
    }
}

// start
add_action('plugins_loaded', array(runthings_secrets(), 'hooks'));

// activation and deactivation hooks
register_activation_hook(__FILE__, array(runthings_secrets(), 'activate'));
register_deactivation_hook(__FILE__, array(runthings_secrets(), 'deactivate'));
register_uninstall_hook(__FILE__, 'RunthingsSecrets\\runthings_secrets_uninstall');
