<?php
/*
Secrets by runthings.dev

Copyright 2023 Matthew Harris

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

class runthings_secrets_Options_Page
{
    public function __construct()
    {
        add_action('admin_notices', [$this, 'admin_notices']);
        add_action('admin_menu', [$this, 'runthings_secrets_options_page']);
        add_action('admin_init', [$this, 'runthings_secrets_settings_init']);

        add_action('admin_enqueue_scripts', [$this, 'runthings_secrets_enqueue_scripts']);
        add_action('admin_footer', [$this, 'runthings_secrets_admin_footer']);
    }

    public function admin_notices()
    {
        $add_secret_page = get_option('runthings_secrets_add_secret_page');
        $view_secret_page = get_option('runthings_secrets_view_secret_page');
        if (empty($add_secret_page) || empty($view_secret_page)) {
            $message = __('Please set the "Add Secret Page" and "View Secret Page" options in the <a href="%s">RunThings Secrets settings</a>.', 'runthings-secrets');
            printf('<div class="notice notice-warning"><p>%s</p></div>', sprintf($message, admin_url('options-general.php?page=runthings-secrets')));
        }
    }

    function runthings_secrets_enqueue_scripts()
    {
        wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', [], '4.0.13');
        wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', ['jquery'], '4.0.13', true);
    }

    public function runthings_secrets_options_page()
    {
        add_options_page(
            __('RunThings Secrets', 'runthings-secrets'),
            __('RunThings Secrets', 'runthings-secrets'),
            'manage_options',
            'runthings-secrets',
            [$this, 'runthings_secrets_options_page_callback']
        );
    }

    public function runthings_secrets_options_page_callback()
    {
?>
        <div class="wrap">
            <h1><?php _e('RunThings Secrets Settings', 'runthings-secrets'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('runthings-secrets-settings');
                do_settings_sections('runthings-secrets');
                submit_button();
                ?>
            </form>
        </div>
    <?php
    }

    public function runthings_secrets_settings_init()
    {
        add_settings_section(
            'runthings_secrets_pages_section',
            __('Secret Pages', 'runthings-secrets'),
            [$this, 'runthings_secrets_pages_section_callback'],
            'runthings-secrets'
        );

        add_settings_field(
            'runthings_secrets_add_page',
            __('Add Secret Page', 'runthings-secrets'),
            [$this, 'runthings_secrets_add_page_callback'],
            'runthings-secrets',
            'runthings_secrets_pages_section'
        );

        add_settings_field(
            'runthings_secrets_view_page',
            __('View Secret Page', 'runthings-secrets'),
            [$this, 'runthings_secrets_view_page_callback'],
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
            'runthings_secrets_view_page',
            'intval'
        );
    }

    public function runthings_secrets_pages_section_callback()
    {
        echo '<p>' . __('Select the WordPress pages to use for adding and viewing secrets.', 'runthings-secrets') . '</p>';
    }

    public function runthings_secrets_add_page_callback()
    {
        $add_page_id = get_option('runthings_secrets_add_page');
        echo '<select name="runthings_secrets_add_page" class="runthings-secrets-select2">';
        echo '<option value="">' . __('(no page selected)', 'runthings-secrets') . '</option>';
        $pages = get_pages();
        foreach ($pages as $page) {
            $selected = ($add_page_id == $page->ID) ? 'selected="selected"' : '';
            echo '<option value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . '</option>';
        }
        echo '</select>';
    }

    public function runthings_secrets_view_page_callback()
    {
        $view_page_id = get_option('runthings_secrets_view_page');
        echo '<select name="runthings_secrets_view_page" class="runthings-secrets-select2">';
        echo '<option value="">' . __('(no page selected)', 'runthings-secrets') . '</option>';
        $pages = get_pages();
        foreach ($pages as $page) {
            $selected = ($view_page_id == $page->ID) ? 'selected="selected"' : '';
            echo '<option value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . '</option>';
        }
        echo '</select>';
    }

    function runthings_secrets_admin_footer()
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

new runthings_secrets_Options_Page();
