<?php

namespace RunthingsSecrets\Integration\BlockEditor;

if (!defined('WPINC')) {
    die;
}

class Blocks
{
    private $add_renderer;

    private $created_renderer;

    private $view_renderer;

    public function __construct($add_renderer, $created_renderer, $view_renderer)
    {
        $this->add_renderer = $add_renderer;
        $this->created_renderer = $created_renderer;
        $this->view_renderer = $view_renderer;

        add_action('init', [$this, 'register_blocks']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_assets']);
    }

    public function register_blocks()
    {
        if (!function_exists('register_block_type')) {
            // Block editor is not available
            return;
        }

        register_block_type(
            plugin_dir_path(__FILE__) . 'add-secret/block.json',
            array(
                'render_callback' => array($this->add_renderer, 'render'),
            )
        );

        register_block_type(
            plugin_dir_path(__FILE__) . 'secret-created/block.json',
            array(
                'render_callback' => array($this->created_renderer, 'render'),
            )
        );

        register_block_type(
            plugin_dir_path(__FILE__) . 'view-secret/block.json',
            array(
                'render_callback' => array($this->view_renderer, 'render'),
            )
        );
    }

    public function enqueue_block_editor_assets()
    {
        wp_enqueue_script(
            'runthings-secrets-block-add',
            plugins_url('add-secret/block-add-secret.js', __FILE__),
            array('wp-blocks', 'wp-editor'),
            RUNTHINGS_SECRETS_PLUGIN_VERSION,
            false // Not $in_footer as block editor needs early access to the script
        );

        wp_enqueue_script(
            'runthings-secrets-block-created',
            plugins_url('secret-created/block-secret-created.js', __FILE__),
            array('wp-blocks', 'wp-editor'),
            RUNTHINGS_SECRETS_PLUGIN_VERSION,
            false // Not $in_footer as block editor needs early access to the script
        );

        wp_enqueue_script(
            'runthings-secrets-block-view',
            plugins_url('view-secret/block-view-secret.js', __FILE__),
            array('wp-blocks', 'wp-editor'),
            RUNTHINGS_SECRETS_PLUGIN_VERSION,
            false // Not $in_footer as block editor needs early access to the script
        );
    }
}
