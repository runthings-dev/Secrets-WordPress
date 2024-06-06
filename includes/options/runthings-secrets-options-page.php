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

include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'options/sections/runthings-secrets-pages-settings.php';
include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'options/sections/runthings-secrets-spam-protection-settings.php';
include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'options/sections/runthings-secrets-rate-limit-settings.php';
include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'options/sections/runthings-secrets-advanced-settings.php';
include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'options/sections/runthings-secrets-stats-settings.php';
include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'options/sections/runthings-secrets-encryption-settings.php';

class runthings_secrets_Options_Page
{
    public function __construct()
    {
        new runthings_secrets_Pages_Settings();
        new runthings_secrets_Spam_Protection_Settings();
        new runthings_secrets_Rate_Limit_Settings();
        new runthings_secrets_Advanced_Settings();
        new runthings_secrets_Encryption_Settings();
        new runthings_secrets_Stats_Settings();

        add_action('admin_menu', [$this, 'options_page']);
        add_action('admin_footer', [$this, 'admin_footer']);
    }

    public function options_page()
    {
        add_options_page(
            __('RunThings Secrets', 'runthings-secrets'),
            __('RunThings Secrets', 'runthings-secrets'),
            'manage_options',
            'runthings-secrets',
            [$this, 'options_page_callback']
        );
    }

    public function options_page_callback()
    {
?>
        <div class="wrap">
            <h1><?php esc_html_e('RunThings Secrets Settings', 'runthings-secrets'); ?></h1>
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

    public function admin_footer()
    {
        $screen = get_current_screen();
        if ($screen->id !== 'settings_page_runthings-secrets') {
            return;
        }

    ?>
        <style>
            .wp-core-ui .button.danger-button {
                background-color: #e03c3c;
                border-color: #dc3232;
                color: #fff;
                text-decoration: none;
            }

            .wp-core-ui .button.danger-button:hover,
            .wp-core-ui .button.danger-button:focus {
                background-color: #c03232;
                border-color: #a82828;
                color: #fff;
            }
        </style>
<?php
    }
}

new runthings_secrets_Options_Page();
