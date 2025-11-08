<?php

namespace RunthingsSecrets\Options;

if (!defined('WPINC')) {
    die;
}

include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'options/sections/PagesSettings.php';
include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'options/sections/SpamProtectionSettings.php';
include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'options/sections/RateLimitSettings.php';
include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'options/sections/AdvancedSettings.php';
include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'options/sections/StatsSettings.php';
include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'options/sections/EncryptionSettings.php';

class OptionsPage
{
    public function __construct()
    {
        new Sections\PagesSettings();
        new Sections\SpamProtectionSettings();
        new Sections\RateLimitSettings();
        new Sections\AdvancedSettings();
        new Sections\EncryptionSettings();
        new Sections\StatsSettings();

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

new OptionsPage();
