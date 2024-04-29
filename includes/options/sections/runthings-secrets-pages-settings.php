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

class runthings_secrets_Pages_Settings
{
    public function __construct()
    {
        add_action('admin_notices', [$this, 'admin_notices']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        add_action('admin_init', [$this, 'settings_init']);
        add_action('admin_footer', [$this, 'admin_footer']);
    }

    public function admin_notices()
    {
        $add_secret_page = get_option('runthings_secrets_add_page');
        $created_secret_page = get_option('runthings_secrets_created_page');
        $view_secret_page = get_option('runthings_secrets_view_page');
        if (empty($add_secret_page) || empty($created_secret_page) || empty($view_secret_page)) {
            $settings_page_url = admin_url('options-general.php?page=runthings-secrets');
            echo '<div class="notice notice-warning"><p>' . sprintf(
                wp_kses(
                    /* translators: %s: URL link to the settings page */
                    __('Please set the "Add Secret Page", "Created Secret Page" and "View Secret Page" options in the <a href="%s">RunThings Secrets settings</a>.', 'runthings-secrets'),
                    ['a' => ['href' => []]] // Allows only <a> tags with href attributes
                ),
                esc_url($settings_page_url)
            ) . '</p></div>';
        }
    }

    public function admin_enqueue_scripts()
    {
        wp_enqueue_style('select2', RUNTHINGS_SECRETS_PLUGIN_URL . '/vendor/select2/css/select2.min.css', [], '4.0.13');
        wp_enqueue_script('select2', RUNTHINGS_SECRETS_PLUGIN_URL . '/vendor/select2/js/select2.min.js', ['jquery'], '4.0.13', true);
    }


    public function settings_init()
    {
        add_settings_section(
            'runthings_secrets_pages_section',
            __('Secret Pages', 'runthings-secrets'),
            [$this, 'pages_section_callback'],
            'runthings-secrets'
        );

        add_settings_field(
            'runthings_secrets_add_page',
            __('Add Secret Page', 'runthings-secrets'),
            [$this, 'add_page_callback'],
            'runthings-secrets',
            'runthings_secrets_pages_section'
        );

        add_settings_field(
            'runthings_secrets_created_page',
            __('Secret Created Page', 'runthings-secrets'),
            [$this, 'created_page_callback'],
            'runthings-secrets',
            'runthings_secrets_pages_section'
        );

        add_settings_field(
            'runthings_secrets_view_page',
            __('View Secret Page', 'runthings-secrets'),
            [$this, 'view_page_callback'],
            'runthings-secrets',
            'runthings_secrets_pages_section'
        );

        register_setting(
            'runthings-secrets-settings',
            'runthings_secrets_add_page',
            'intval'
        );

        register_setting(
            'runthings-secrets-settings',
            'runthings_secrets_created_page',
            'intval'
        );

        register_setting(
            'runthings-secrets-settings',
            'runthings_secrets_view_page',
            'intval'
        );
    }

    public function pages_section_callback()
    {
        echo '<p>' . esc_html__('Select the WordPress pages to use for adding and viewing secrets.', 'runthings-secrets') . '</p>';
    }

    public function add_page_callback()
    {
        $add_page_id = get_option('runthings_secrets_add_page');
        echo '<select name="runthings_secrets_add_page" class="runthings-secrets-select2">';
        echo '<option value="">' . esc_html__('(no page selected)', 'runthings-secrets') . '</option>';
        $pages = get_pages();
        foreach ($pages as $page) {
            $selected = ($add_page_id == $page->ID) ? 'selected="selected"' : '';
            echo '<option value="' . esc_attr($page->ID) . '" ' . esc_attr($selected) . '>' . esc_html($page->post_title) . '</option>';
        }
        echo '</select>';
    }

    public function created_page_callback()
    {
        $created_page_id = get_option('runthings_secrets_created_page');
        echo '<select name="runthings_secrets_created_page" class="runthings-secrets-select2">';
        echo '<option value="">' . esc_html__('(no page selected)', 'runthings-secrets') . '</option>';
        $pages = get_pages();
        foreach ($pages as $page) {
            $selected = ($created_page_id == $page->ID) ? 'selected="selected"' : '';
            echo '<option value="' . esc_attr($page->ID) . '" ' . esc_attr($selected) . '>' . esc_html($page->post_title) . '</option>';
        }
        echo '</select>';
    }

    public function view_page_callback()
    {
        $view_page_id = get_option('runthings_secrets_view_page');
        echo '<select name="runthings_secrets_view_page" class="runthings-secrets-select2">';
        echo '<option value="">' . esc_html__('(no page selected)', 'runthings-secrets') . '</option>';
        $pages = get_pages();
        foreach ($pages as $page) {
            $selected = ($view_page_id == $page->ID) ? 'selected="selected"' : '';
            echo '<option value="' . esc_attr($page->ID) . '" ' . esc_attr($selected) . '>' . esc_html($page->post_title) . '</option>';
        }
        echo '</select>';
    }

    public function admin_footer()
    {
?>
        <script>
            jQuery(document).ready(function($) {
                $('.runthings-secrets-select2').select2();
            });
        </script>
<?php
    }
}
