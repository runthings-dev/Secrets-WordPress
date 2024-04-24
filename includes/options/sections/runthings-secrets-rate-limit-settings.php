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
            __('Rate Limit Settings', 'runthings-secrets'),
            [$this, 'rate_limit_section_callback'],
            'runthings-secrets'
        );

        add_settings_field(
            'runthings_secrets_rate_limit_enabled',
            __('Enable Rate Limiting', 'runthings-secrets'),
            [$this, 'rate_limit_enable_callback'],
            'runthings-secrets',
            'runthings_secrets_rate_limit_section'
        );

        add_settings_field(
            'runthings_secrets_rate_limit_tries',
            __('Maximum View Requests per Minute', 'runthings-secrets'),
            [$this, 'rate_limit_tries_callback'],
            'runthings-secrets',
            'runthings_secrets_rate_limit_section'
        );

        register_setting(
            'runthings-secrets-settings',
            'runthings_secrets_rate_limit_enabled',
            'boolval'
        );

        register_setting(
            'runthings-secrets-settings',
            'runthings_secrets_rate_limit_tries',
            'intval'
        );
    }

    public function rate_limit_section_callback()
    {
        echo '<p>' . __('Configure the rate limiting for viewing secrets.', 'runthings-secrets') . '</p>';
    }

    public function rate_limit_enable_callback()
    {
        $rate_limit_enabled = get_option('runthings_secrets_rate_limit_enabled', 1);
        echo '<input type="checkbox" id="runthings_secrets_rate_limit_enabled" name="runthings_secrets_rate_limit_enabled" value="1"' . checked(1, $rate_limit_enabled, false) . '/>';
        echo '<label for="runthings_secrets_rate_limit_enabledd">' . __('Enable rate limiting', 'runthings-secrets') . '</label>';
    }

    public function rate_limit_tries_callback()
    {
        $rate_limit_tries = get_option('runthings_secrets_rate_limit_tries', 10);
        echo '<input type="number" id="runthings_secrets_rate_limit_tries" name="runthings_secrets_rate_limit_tries" value="' . esc_attr($rate_limit_tries) . '" min="1" />';
        echo '<p class="description">' . __('Number of attempts allowed per minute from a single IP address.', 'runthings-secrets') . '</p>';
    }
}
