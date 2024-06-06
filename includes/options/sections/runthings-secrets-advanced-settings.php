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

class runthings_secrets_Advanced_Settings
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'delete_all_secrets_check']);
        add_action('admin_init', [$this, 'settings_init']);
    }

    public function delete_all_secrets_check()
    {
        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : null;
        $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : null;

        if ($page === 'runthings-secrets' && $action === 'delete_all_secrets') {
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'delete_all_secrets_action')) {
                wp_die('Security check failed');
            }

            $this->delete_all_secrets();
        }
    }

    private function delete_all_secrets()
    {
        if (current_user_can('manage_options')) {
            global $wpdb;

            $table_name = $wpdb->prefix . 'runthings_secrets';

            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
            // Direct query is required as $wpdb->delete() does not support deleting all rows
            $rows_deleted = $wpdb->query(
                $wpdb->prepare("DELETE FROM %i", $table_name)
            );
            // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

            if ($rows_deleted > 0) {
                wp_cache_delete('runthings_secrets_count', 'runthings_secrets');
            }

            add_action('admin_notices', [$this, 'deleted_secrets_notice']);
        }
    }

    public function deleted_secrets_notice()
    {
        $message = __('All secrets have been deleted.', 'runthings-secrets');
        printf('<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html($message));
    }

    public function settings_init()
    {
        add_settings_section(
            'runthings_secrets_advanced_section',
            __('Advanced', 'runthings-secrets'),
            [$this, 'enqueue_advanced_section_callback'],
            'runthings-secrets'
        );

        add_settings_field(
            'runthings_secrets_enqueue_form_styles',
            __('Enqueue Form Styles', 'runthings-secrets'),
            [$this, 'enqueue_stylesheet_callback'],
            'runthings-secrets',
            'runthings_secrets_advanced_section'
        );

        add_settings_field(
            'runthings_secrets_delete_all_secrets',
            __('Delete All Secrets', 'runthings-secrets'),
            [$this, 'delete_all_secrets_callback'],
            'runthings-secrets',
            'runthings_secrets_advanced_section'
        );

        register_setting(
            'runthings-secrets-settings',
            'runthings_secrets_enqueue_form_styles',
            [
                'type' => 'boolean',
                'default' => 1,
                'sanitize_callback' => 'rest_sanitize_boolean'
            ]
        );
    }

    public function enqueue_advanced_section_callback()
    {
        // Empty callback
    }

    public function enqueue_stylesheet_callback()
    {
        $enqueue_stylesheet = get_option('runthings_secrets_enqueue_form_styles', 1);
        echo '<input type="checkbox" name="runthings_secrets_enqueue_form_styles" value="1" ' . esc_attr(checked(1, $enqueue_stylesheet, false)) . ' />';
        echo '<span class="description"> ' . esc_html__('Enqueue the stylesheet for the \'add secret\' form.', 'runthings-secrets') . '</span>';
    }

    public function delete_all_secrets_callback()
    {
        $url = admin_url('options-general.php?page=runthings-secrets&action=delete_all_secrets');
        $nonce_url = wp_nonce_url($url, 'delete_all_secrets_action');
        $confirm_message = __('Are you sure you want to delete all secrets? This action cannot be undone.', 'runthings-secrets');
        echo '<a href="' . esc_url($nonce_url) . '" class="button danger-button" onclick="return confirm(\'' . esc_js($confirm_message) . '\');">' . esc_html__('Delete All Secrets', 'runthings-secrets') . '</a>';
    }
}
