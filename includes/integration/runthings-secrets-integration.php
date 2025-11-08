<?php

if (!defined('WPINC')) {
    die;
}

class runthings_secrets_Integration
{
    public function __construct()
    {
        // Create renderers
        $this->include_renderers();
        $add_secret = new runthings_secrets_Add_Secret();
        $secret_created = new runthings_secrets_Secret_Created();
        $view_secret = new runthings_secrets_View_Secret();

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
        new runthings_secrets_Blocks_Integration($add_secret, $secret_created, $view_secret);
    }

    // Placeholder for future Elementor integration
    /*
    private function include_elementor_widgets()
    {
        // Include Elementor widgets integration here
    }
    */
}
