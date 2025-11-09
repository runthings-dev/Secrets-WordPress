<?php

namespace RunthingsSecrets\Admin;

if (!defined('WPINC')) {
    die;
}

/**
 * Database migration handler
 *
 * Manages database schema updates across plugin versions
 */
class DatabaseMigration
{
    /**
     * Run all pending migrations
     *
     * @return void
     */
    public static function run()
    {
        $current_version = get_option('runthings_secrets_db_version', '0.0.0');

        // Run migrations in order
        if (version_compare($current_version, '1.8.0', '<')) {
            self::migrate_to_1_8_0();
        }

        // Update db version to current plugin version
        update_option('runthings_secrets_db_version', RUNTHINGS_SECRETS_PLUGIN_VERSION);
    }
    
    /**
     * Migration to version 1.8.0
     * Adds allow_delete column to secrets table
     * 
     * @return void
     */
    private static function migrate_to_1_8_0()
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'runthings_secrets';
        
        // Check if column already exists
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
        $column_exists = $wpdb->get_results(
            $wpdb->prepare(
                "SHOW COLUMNS FROM %i LIKE %s",
                $table_name,
                'allow_delete'
            )
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
        
        if (empty($column_exists)) {
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange
            $wpdb->query(
                $wpdb->prepare(
                    "ALTER TABLE %i ADD COLUMN allow_delete tinyint(1) NOT NULL DEFAULT 1 AFTER max_views",
                    $table_name
                )
            );
            // phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange
            // phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching
            // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
        }
    }
}

