<?php

namespace RunthingsSecrets\Integration\Shortcodes;

if (!defined('WPINC')) {
    die;
}

class Shortcodes
{
    private $add_renderer;

    private $created_renderer;

    private $view_renderer;

    public function __construct($add_renderer, $created_renderer, $view_renderer)
    {
        $this->add_renderer = $add_renderer;
        $this->created_renderer = $created_renderer;
        $this->view_renderer = $view_renderer;

        $this->register_shortcodes();
    }

    private function register_shortcodes()
    {
        add_shortcode('runthings_secrets_add', [$this, 'add_secret_shortcode']);
        add_shortcode('runthings_secrets_created', [$this, 'secret_created_shortcode']);
        add_shortcode('runthings_secrets_view', [$this, 'view_secret_shortcode']);
    }

    public function add_secret_shortcode()
    {
        return $this->add_renderer->render();
    }

    public function secret_created_shortcode()
    {
        return $this->created_renderer->render();
    }

    public function view_secret_shortcode()
    {
        return $this->view_renderer->render();
    }
}
