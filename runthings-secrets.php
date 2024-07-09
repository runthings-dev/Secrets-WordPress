<?php

/**
 * Plugin Name: RunThings Secrets
 * Plugin URI: https://runthings.dev/wordpress-plugins/secrets/
 * Repository URI: https://github.com/runthings-dev/Secrets-WordPress
 * Description: Share secrets securely
 * Version: 1.6.0
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

define('RUNTHINGS_SECRETS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES', plugin_dir_path(__FILE__) . "includes/");
define('RUNTHINGS_SECRETS_PLUGIN_URL', plugins_url('', __FILE__));

define('RUNTHINGS_SECRETS_PLUGIN_VERSION', '1.6.0');

class runthings_secrets_Plugin
{
    protected static $single_instance = null;

    protected function __construct()
    {
        include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'integration/runthings-secrets-integration.php';
        new runthings_secrets_Integration();

        include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'options/runthings-secrets-options-page.php';

        include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'runthings-secrets-copy-to-clipboard-icon.php';
        include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'runthings-secrets-rate-limit.php';
        include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'runthings-secrets-template-checker.php';
        include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'runthings-secrets-template-loader.php';
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

        new runthings_secrets_Template_Checker();

        add_filter('plugin_action_links_runthings-secrets/runthings-secrets.php', [$this, 'add_settings_link']);

        add_action('init', [$this, 'schedule_clear_expired_secrets']);
        add_action('runthings_secrets_clear_expired_secrets', array($this, 'clear_expired_secrets'));
    }

    public function activate()
    {
        $this->activate_database();
        $this->activate_options();
    }

    public function deactivate()
    {
        $this->deactivate_scheduled_tasks();
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

        $current_time_datetime = new DateTime('now', new DateTimeZone('UTC'));

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

            $targetTime = new DateTime('today 00:15', new DateTimeZone($timezone));

            $currentTime = new DateTime('now', new DateTimeZone($timezone));

            // If it's already past 00:15 today, schedule for 00:15 the next day
            if ($currentTime > $targetTime) {
                $targetTime->modify('+1 day');
            }

            wp_schedule_event($targetTime->getTimestamp(), 'daily', $hook);
        }
    }


    private function activate_database()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'runthings_secrets';
        $charset_collate = $wpdb->get_charset_collate();

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        // Charset cannot be passed in with %s placeholder because it wraps it in single quotes and creates a sql syntax error
        $sql = $wpdb->prepare("CREATE TABLE %i (
            id int(11) NOT NULL AUTO_INCREMENT,
            uuid varchar(255) NOT NULL,
            secret text NOT NULL,
            max_views int(11) NOT NULL,
            views int(11) NOT NULL,
            expiration datetime NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id)
          ) $charset_collate;", $table_name);
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        add_option('runthings_secrets_db_version', RUNTHINGS_SECRETS_PLUGIN_VERSION, '', 'no');
    }

    private function activate_options()
    {
        add_option('runthings_secrets_enqueue_form_styles', 1, '', 'no');
        add_option('runthings_secrets_stats_total_secrets', 0, '', 'no');
        add_option('runthings_secrets_stats_total_views', 0, '', 'no');
        add_option('runthings_secrets_recaptcha_score', 0.5, '', 'no');
    }

    private function deactivate_scheduled_tasks()
    {
        $tasks = array('runthings_secrets_clear_expired_secrets');
        foreach ($tasks as $task) {
            wp_clear_scheduled_hook($task);
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
        return runthings_secrets_Plugin::get_instance();
    }
}

// start
add_action('plugins_loaded', array(runthings_secrets(), 'hooks'));

// activation and deactivation hooks
register_activation_hook(__FILE__, array(runthings_secrets(), 'activate'));
register_deactivation_hook(__FILE__, array(runthings_secrets(), 'deactivate'));
register_uninstall_hook(__FILE__, 'runthings_secrets_uninstall');
