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

include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'runthings-secrets-sodium-encryption.php';

class runthings_secrets_Encryption_Settings
{
    private $crypt;

    public function __construct()
    {
        $this->crypt = runthings_secrets_Sodium_Encryption::get_instance();

        if (!$this->crypt->is_sodium_enabled()) {
            return;
        }

        add_action('admin_init', [$this, 'regenerate_internal_encryption_key_check']);
        add_action('admin_init', [$this, 'settings_init']);
    }

    public function regenerate_internal_encryption_key_check()
    {
        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : null;
        $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : null;

        if ($page === 'runthings-secrets' && $action === 'regenerate_internal_encryption_key') {
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'regenerate_key_action')) {
                wp_die('Security check failed');
            }

            $this->regenerate_internal_encryption_key();
        }
    }

    private function regenerate_internal_encryption_key()
    {
        if (current_user_can('manage_options')) {
            $this->crypt->generate_and_store_key();
            add_action('admin_notices', [$this, 'regenerate_internal_encryption_key_notice']);
        }
    }

    public function regenerate_internal_encryption_key_notice()
    {
        $message = esc_html__('Internal encryption key has been regenerated. Consider using the delete all secrets feature to clear out old secrets which are no longer decipherable.', 'runthings-secrets');
        printf('<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html($message));
    }

    public function settings_init()
    {
        add_settings_section(
            'runthings_secrets_encryption_key_section',
            __('Encryption Key', 'runthings-secrets'),
            [$this, 'encryption_key_section_callback'],
            'runthings-secrets'
        );

        add_settings_field(
            'runthings_secrets_encryption_key',
            __('Encryption Key', 'runthings-secrets'),
            [$this, 'encryption_key_callback'],
            'runthings-secrets',
            'runthings_secrets_encryption_key_section'
        );

        add_settings_field(
            'runthings_secrets_internal_encryption_key',
            __('Internal Encryption Key', 'runthings-secrets'),
            [$this, 'internal_encryption_key_callback'],
            'runthings-secrets',
            'runthings_secrets_encryption_key_section'
        );

        add_settings_field(
            'runthings_secrets_encryption_key_method',
            __('Current Encryption Method', 'runthings-secrets'),
            [$this, 'encryption_key_method_callback'],
            'runthings-secrets',
            'runthings_secrets_encryption_key_section'
        );
    }

    public function encryption_key_section_callback()
    {
        echo '<p>' . esc_html(__('The plugin has generated a default internal encryption key automatically, and stored it as a WordPress option.', 'runthings-secrets')) . '</p>';
        echo '<p>' . esc_html(__('You can optionally override this using the snippet below in your wp-config.php. This lets you store the key in an environment variable, or 3rd-party key storage service.', 'runthings-secrets')) . '</p>';
        echo '<p><strong>' . esc_html(__('Important', 'runthings-secrets')) . ':</strong> ' . esc_html(__('If you change the encryption key, any existing secrets will become unreadable. You should then use the "Delete All Secrets" feature to clear out the database, or users will see garbled text when they view their secrets.', 'runthings-secrets')) . '</p>';
    }

    public function encryption_key_callback()
    {
        $new_key = $this->crypt->generate_key();
        echo '<input type="text" readonly="readonly" value="' . esc_attr("define('RUNTHINGS_SECRETS_ENCRYPTION_KEY', '$new_key');") . '" onclick="this.select();" style="width: 100%;">';
        echo '<p class="description">' . esc_html__('Refresh the page to generate another key.', 'runthings-secrets') . '</p>';
    }

    public function internal_encryption_key_callback()
    {
        $url = admin_url('options-general.php?page=runthings-secrets&action=regenerate_internal_encryption_key');
        $nonce_url = wp_nonce_url($url, 'regenerate_key_action');
        $confirm_message = __('Are you sure you want to regenerate the internal encryption key? This action cannot be undone.', 'runthings-secrets');
        echo '<a href="' . esc_url($nonce_url) . '" class="button danger-button" onclick="return confirm(\'' . esc_js($confirm_message) . '\');">' . esc_html(__('Regenerate Internal Key', 'runthings-secrets')) . '</a>';
        echo '<p class="description"> ' . esc_html(__('The internal encryption key is used if you haven\'t specified one using the define() method above.', 'runthings-secrets')) . '</p>';
    }

    public function encryption_key_method_callback()
    {
        $key_method = $this->crypt->get_key_method();
        echo esc_html($key_method);
    }
}
