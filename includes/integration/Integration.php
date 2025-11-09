<?php

namespace RunthingsSecrets\Integration;

use RunthingsSecrets\Render\Views\AddSecret;
use RunthingsSecrets\Render\Views\SecretCreated;
use RunthingsSecrets\Render\Views\ViewSecret;

if (!defined('WPINC')) {
    die;
}

class Integration
{
    public function __construct()
    {
        // Create renderers
        $add_secret = new AddSecret();
        $secret_created = new SecretCreated();
        $view_secret = new ViewSecret();

        // Integrate shortcodes
        new Shortcodes\Shortcodes($add_secret, $secret_created, $view_secret);

        // Integrate blocks
        new BlockEditor\Blocks($add_secret, $secret_created, $view_secret);

        // Integrate Elementor widgets (future implementation)
        // $this->include_elementor_widgets();
    }

    // Placeholder for future Elementor integration
    /*
    private function include_elementor_widgets()
    {
        // Include Elementor widgets integration here
    }
    */
}
