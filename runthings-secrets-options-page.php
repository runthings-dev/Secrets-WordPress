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

class RunThings_Secrets_Options_Page
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'runthings_secrets_options_page']);
        add_action('admin_init', [$this, 'runthings_secrets_settings_init']);
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
        wp_dropdown_pages(array(
            'name' => 'runthings_secrets_add_page',
            'selected' => $add_page_id,
            'show_option_none' => __('(no page selected)', 'runthings-secrets')
        ));
    }

    public function runthings_secrets_view_page_callback()
    {
        $view_page_id = get_option('runthings_secrets_view_page');
        wp_dropdown_pages(array(
            'name' => 'runthings_secrets_view_page',
            'selected' => $view_page_id,
            'show_option_none' => __('(no page selected)', 'runthings-secrets')
        ));
    }
}

new RunThings_Secrets_Options_Page();
