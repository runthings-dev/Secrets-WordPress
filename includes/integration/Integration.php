<?php

namespace RunthingsSecrets\Integration;

if (!defined('WPINC')) {
    die;
}

class Integration
{
    public function __construct()
    {
        // Create renderers
        $this->include_renderers();
        $add_secret = new \RunthingsSecrets\Render\AddSecret();
        $secret_created = new \RunthingsSecrets\Render\SecretCreated();
        $view_secret = new \RunthingsSecrets\Render\ViewSecret();

        // Integrate shortcodes
        $this->include_shortcodes($add_secret, $secret_created, $view_secret);

        // Integrate blocks
        $this->include_blocks($add_secret, $secret_created, $view_secret);

        // Integrate Elementor widgets (future implementation)
        // $this->include_elementor_widgets();
    }

    private function include_renderers()
    {
        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'render/AddSecret.php';
        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'render/SecretCreated.php';
        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'render/ViewSecret.php';
    }

    private function include_shortcodes($add_secret, $secret_created, $view_secret)
    {
        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'integration/shortcode/Shortcodes.php';
        new Shortcodes\Shortcodes($add_secret, $secret_created, $view_secret);
    }

    private function include_blocks($add_secret, $secret_created, $view_secret)
    {
        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'integration/block-editor/Blocks.php';
        new BlockEditor\Blocks($add_secret, $secret_created, $view_secret);
    }

    // Placeholder for future Elementor integration
    /*
    private function include_elementor_widgets()
    {
        // Include Elementor widgets integration here
    }
    */
}
