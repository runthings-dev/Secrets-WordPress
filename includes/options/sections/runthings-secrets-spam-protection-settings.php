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

class runthings_secrets_Spam_Protection_Settings
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'settings_init']);
    }

    public function settings_init()
    {
        add_settings_section(
            'runthings_secrets_spam_protection_section',
            __('Spam Protection', 'runthings-secrets'),
            [$this, 'spam_protection_section_callback'],
            'runthings-secrets'
        );

        add_settings_field(
            'runthings_secrets_recaptcha_enabled',
            __('Enable reCAPTCHA v3', 'runthings-secrets'),
            [$this, 'recaptcha_enabled_callback'],
            'runthings-secrets',
            'runthings_secrets_spam_protection_section'
        );

        add_settings_field(
            'runthings_secrets_recaptcha_public_key',
            __('reCAPTCHA v3 Public Key', 'runthings-secrets'),
            [$this, 'recaptcha_public_key_callback'],
            'runthings-secrets',
            'runthings_secrets_spam_protection_section'
        );

        add_settings_field(
            'runthings_secrets_recaptcha_private_key',
            __('reCAPTCHA v3 Private Key', 'runthings-secrets'),
            [$this, 'recaptcha_private_key_callback'],
            'runthings-secrets',
            'runthings_secrets_spam_protection_section'
        );

        add_settings_field(
            'runthings_secrets_recaptcha_score',
            __('reCAPTCHA v3 Score', 'runthings-secrets'),
            [$this, 'recaptcha_score_callback'],
            'runthings-secrets',
            'runthings_secrets_spam_protection_section'
        );

        register_setting(
            'runthings-secrets-settings',
            'runthings_secrets_recaptcha_enabled',
            'intval'
        );

        register_setting(
            'runthings-secrets-settings',
            'runthings_secrets_recaptcha_public_key',
            'sanitize_text_field'
        );

        register_setting(
            'runthings-secrets-settings',
            'runthings_secrets_recaptcha_private_key',
            'sanitize_text_field'
        );

        register_setting(
            'runthings-secrets-settings',
            'runthings_secrets_recaptcha_score',
            array(
                'type' => 'float',
                'default' => 0.5,
                'sanitize_callback' => [$this, 'validate_float']
            )
        );
    }

    public function spam_protection_section_callback()
    {
        echo '<p>' . esc_html__('Protect your secrets from spam by enabling Google reCAPTCHA v3.', 'runthings-secrets') . '</p>';
        echo '<p>' . wp_kses(
            __('Get your reCAPTCHA v3 keys <a target="_blank" href="https://www.google.com/recaptcha/admin/create">here</a>.', 'runthings-secrets'),
            array(
                'a' => array(
                    'href' => array(),
                    'target' => array()
                )
            )
        ) . '</p>';

        echo '<p>' . wp_kses(
            __('Note: When you enable Google reCAPTCHA, it will send user data, such as the user\'s IP address and other data, to Google for verification. <a href="https://www.google.com/recaptcha" target="_blank">Google reCAPTCHA</a>, <a href="https://www.google.com/recaptcha/terms" target="_blank">Google reCAPTCHA Terms of Use</a>, <a href="https://policies.google.com/privacy" target="_blank">Google Privacy Policy</a>.', 'runthings-secrets'),
            array(
                'a' => array(
                    'href' => array(),
                    'target' => array()
                )
            )
        ) . '</p>';
    }

    public function recaptcha_enabled_callback()
    {
        $recaptcha_enabled = get_option('runthings_secrets_recaptcha_enabled');
        echo '<input type="checkbox" name="runthings_secrets_recaptcha_enabled" value="1" ' . checked(1, $recaptcha_enabled, false) . ' />';
    }

    public function recaptcha_public_key_callback()
    {
        $recaptcha_public_key = get_option('runthings_secrets_recaptcha_public_key');
        echo '<input type="text" class="regular-text" name="runthings_secrets_recaptcha_public_key" value="' . esc_attr($recaptcha_public_key) . '" />';
    }

    public function recaptcha_private_key_callback()
    {
        $recaptcha_private_key = get_option('runthings_secrets_recaptcha_private_key');
        echo '<input type="text" class="regular-text" name="runthings_secrets_recaptcha_private_key" value="' . esc_attr($recaptcha_private_key) . '" />';
    }

    public function recaptcha_score_callback()
    {
        $recaptcha_score = get_option('runthings_secrets_recaptcha_score', 0.5);
        echo '<input type="number" step="0.01" min="0" max="1" class="regular-text" name="runthings_secrets_recaptcha_score" value="' . esc_attr($recaptcha_score) . '" />';
        echo '<p class="description">' . esc_html__('Set the reCAPTCHA v3 score threshold (0 to 1). A lower value is less strict, a higher value is more strict.', 'runthings-secrets') . '</p>';
    }

    public function validate_float($input)
    {
        $value = floatval($input);
        if ($value < 0) {
            $value = 0;
        } elseif ($value > 1) {
            $value = 1;
        }
        return $value;
    }
}
