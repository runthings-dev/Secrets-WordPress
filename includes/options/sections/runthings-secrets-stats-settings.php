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

class runthings_secrets_Stats_Settings
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'settings_init']);
    }

    public function settings_init()
    {
        add_settings_section(
            'runthings_secrets_stats_section',
            __('Statistics', 'runthings-secrets'),
            [$this, 'stats_section_callback'],
            'runthings-secrets'
        );
    }

    public function stats_section_callback()
    {
        $total_views_count = intval(get_option('runthings_secrets_stats_total_views', 0));
        $total_secrets_count = intval(get_option('runthings_secrets_stats_total_secrets', 0));
        $secrets_in_database = intval($this->get_secrets_in_database());
?>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e('Total Secrets Created', 'runthings-secrets'); ?></th>
                    <td><?php echo esc_html($total_secrets_count); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Total Secrets Viewed', 'runthings-secrets'); ?></th>
                    <td><?php echo esc_html($total_views_count); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Secrets In Database', 'runthings-secrets'); ?></th>
                    <td><?php echo esc_html($secrets_in_database); ?></td>
                </tr>
            </tbody>
        </table>
<?php
    }

    private function get_secrets_in_database()
    {
        global $wpdb;
        $secrets_table = $wpdb->prefix . 'runthings_secrets';
        $cache_key = 'runthings_secrets_count';
        $cache_group = 'runthings_secrets';

        $current_secrets_count = wp_cache_get($cache_key, $cache_group);

        if ($current_secrets_count === false) {
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
            // Direct query is required as there is no built-in WordPress function to get the count of rows in a table
            $current_secrets_count = $wpdb->get_var(
                $wpdb->prepare("SELECT COUNT(*) FROM %i", $secrets_table)
            );
            // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

            wp_cache_set($cache_key, $current_secrets_count, $cache_group);
        }

        return $current_secrets_count;
    }
}
