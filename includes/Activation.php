<?php

namespace RunthingsSecrets;

if (!defined('WPINC')) {
    die;
}

/**
 * Plugin activation and deactivation handler
 */
class Activation
{
    /**
     * Run activation tasks
     * 
     * @return void
     */
    public static function activate()
    {
        self::activate_database();
        self::activate_options();
    }

    /**
     * Run deactivation tasks
     * 
     * @return void
     */
    public static function deactivate()
    {
        self::deactivate_scheduled_tasks();
    }

    /**
     * Create database tables
     * 
     * @return void
     */
    private static function activate_database()
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
            allow_delete tinyint(1) NOT NULL DEFAULT 1,
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

    /**
     * Create default options
     * 
     * @return void
     */
    private static function activate_options()
    {
        add_option('runthings_secrets_enqueue_form_styles', 1, '', 'no');
        add_option('runthings_secrets_stats_total_secrets', 0, '', 'no');
        add_option('runthings_secrets_stats_total_views', 0, '', 'no');
        add_option('runthings_secrets_recaptcha_score', 0.5, '', 'no');
    }

    /**
     * Clear scheduled tasks
     * 
     * @return void
     */
    private static function deactivate_scheduled_tasks()
    {
        $tasks = array('runthings_secrets_clear_expired_secrets');
        foreach ($tasks as $task) {
            wp_clear_scheduled_hook($task);
        }
    }
}

