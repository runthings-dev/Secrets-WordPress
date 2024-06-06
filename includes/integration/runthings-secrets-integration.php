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

class runthings_secrets_Integration
{
    private $plugin_version;

    public function __construct($plugin_version)
    {
        $this->plugin_version = sanitize_text_field($plugin_version);

        // Create renderers
        $this->include_renderers();
        $add_secret = new runthings_secrets_Add_Secret($this->plugin_version);
        $secret_created = new runthings_secrets_Secret_Created($this->plugin_version);
        $view_secret = new runthings_secrets_View_Secret($this->plugin_version);

        // Integrate shortcodes
        $this->include_shortcodes($add_secret, $secret_created, $view_secret);

        // Integrate blocks
        $this->include_blocks($add_secret, $secret_created, $view_secret);

        // Integrate Elementor widgets (future implementation)
        // $this->include_elementor_widgets();
    }

    private function include_renderers()
    {
        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'render/runthings-secrets-add-secret.php';
        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'render/runthings-secrets-secret-created.php';
        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'render/runthings-secrets-view-secret.php';
    }

    private function include_shortcodes($add_secret, $secret_created, $view_secret)
    {
        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'integration/shortcode/runthings-secrets-shortcodes.php';
        new runthings_secrets_Shortcodes_Integration($add_secret, $secret_created, $view_secret);
    }

    private function include_blocks($add_secret, $secret_created, $view_secret)
    {
        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'integration/block-editor/runthings-secrets-blocks.php';
        new runthings_secrets_Blocks_Integration($this->plugin_version, $add_secret, $secret_created, $view_secret);
    }

    // Placeholder for future Elementor integration
    /*
    private function include_elementor_widgets()
    {
        // Include Elementor widgets integration here
    }
    */
}
