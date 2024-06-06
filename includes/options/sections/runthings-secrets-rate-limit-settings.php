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

class runthings_secrets_Rate_Limit_Settings
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'settings_init']);
    }

    public function settings_init()
    {
        add_settings_section(
            'runthings_secrets_rate_limit_section',
            esc_html__('Rate Limit Settings', 'runthings-secrets'),
            [$this, 'rate_limit_section_callback'],
            'runthings-secrets'
        );

        $this->add_rate_limits_settings();
        $this->add_role_exemption_settings();
    }

    private function add_rate_limits_settings()
    {
        add_settings_field(
            'runthings_secrets_rate_limit_enabled',
            __('Enable Rate Limiting', 'runthings-secrets'),
            [$this, 'rate_limit_enable_callback'],
            'runthings-secrets',
            'runthings_secrets_rate_limit_section'
        );

        register_setting(
            'runthings-secrets-settings',
            'runthings_secrets_rate_limit_enabled',
            'boolval'
        );

        $this->add_rate_limit_setting('add', __('Maximum Add Secret Requests per Minute', 'runthings-secrets'));
        $this->add_rate_limit_setting('created', __('Maximum Secret Created Requests per Minute', 'runthings-secrets'));
        $this->add_rate_limit_setting('view', __('Maximum View Secret Requests per Minute', 'runthings-secrets'));
    }

    private function add_role_exemption_settings()
    {
        add_settings_field(
            'runthings_secrets_rate_limit_exemption_enabled',
            __('Enable Role-Based Rate Limit Exemption', 'runthings-secrets'),
            [$this, 'rate_limit_exemption_enable_callback'],
            'runthings-secrets',
            'runthings_secrets_rate_limit_section'
        );
        register_setting('runthings-secrets-settings', 'runthings_secrets_rate_limit_exemption_enabled', 'boolval');

        add_settings_field(
            'runthings_secrets_rate_limit_exemption_roles',
            __('Select Exempt Roles', 'runthings-secrets'),
            [$this, 'rate_limit_exemption_roles_callback'],
            'runthings-secrets',
            'runthings_secrets_rate_limit_section'
        );
        register_setting('runthings-secrets-settings', 'runthings_secrets_rate_limit_exemption_roles', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_exempt_roles']
        ]);
    }

    public function sanitize_exempt_roles($input)
    {
        if (!is_array($input)) {
            return [];
        }

        return array_map('sanitize_text_field', $input);
    }

    private function add_rate_limit_setting($renderer, $label)
    {
        $option_name = 'runthings_secrets_rate_limit_tries_' . $renderer;
        add_settings_field(
            $option_name,
            $label,
            [$this, 'rate_limit_tries_callback'],
            'runthings-secrets',
            'runthings_secrets_rate_limit_section',
            ['renderer' => $renderer]
        );

        register_setting(
            'runthings-secrets-settings',
            $option_name,
            'intval'
        );
    }

    public function rate_limit_section_callback()
    {
        echo '<p>' . esc_html__('Configure the rate limiting for different operations within the plugin.', 'runthings-secrets') . '</p>';
    }

    public function rate_limit_enable_callback()
    {
        $rate_limit_enabled = get_option('runthings_secrets_rate_limit_enabled', 1);
        echo '<input type="checkbox" id="runthings_secrets_rate_limit_enabled" name="runthings_secrets_rate_limit_enabled" value="1" ' . checked(1, $rate_limit_enabled, false) . ' />';
        echo '<label for="runthings_secrets_rate_limit_enabled">' . esc_html__('Enable rate limiting', 'runthings-secrets') . '</label>';
    }

    public function rate_limit_tries_callback($args)
    {
        $option_name = 'runthings_secrets_rate_limit_tries_' . $args['renderer'];

        switch ($args['renderer']) {
            case 'add':
                $default_value = 25;
                break;
            case 'created':
            case 'view':
            default:
                $default_value = 10;
                break;
        }

        $rate_limit_tries = get_option($option_name, $default_value);
        echo '<input type="number" id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '" value="' . esc_attr($rate_limit_tries) . '" min="1" />';
        echo '<p class="description">' . esc_html__('Number of attempts allowed per minute from a single IP address.', 'runthings-secrets') . '</p>';
    }

    public function rate_limit_exemption_enable_callback()
    {
        $exemption_enabled = get_option('runthings_secrets_rate_limit_exemption_enabled', 0);
        echo '<input type="checkbox" id="runthings_secrets_rate_limit_exemption_enabled" name="runthings_secrets_rate_limit_exemption_enabled" value="1" ' . checked(1, $exemption_enabled, false) . ' />';
        echo '<label for="runthings_secrets_rate_limit_exemption_enabled">' . esc_html__('Enable rate limit exemptions for selected roles', 'runthings-secrets') . '</label>';
    }

    public function rate_limit_exemption_roles_callback()
    {
        $exempt_roles = get_option('runthings_secrets_rate_limit_exemption_roles', []);
        if (!is_array($exempt_roles)) {
            $exempt_roles = [];
        }

        global $wp_roles;
        $all_roles = $wp_roles->roles;

        foreach ($all_roles as $role_key => $role_info) {
            $checked = in_array($role_key, $exempt_roles) ? 'checked' : '';
            echo '<input type="checkbox" id="exempt_role_' . esc_attr($role_key) . '" name="runthings_secrets_rate_limit_exemption_roles[]" value="' . esc_attr($role_key) . '" ' . esc_attr($checked) . ' />';
            echo '<label for="exempt_role_' . esc_attr($role_key) . '">' . esc_html($role_info['name']) . '</label><br />';
        }
    }
}
