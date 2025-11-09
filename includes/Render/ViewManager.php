<?php

namespace RunthingsSecrets\Render;

use RunthingsSecrets\Data\SecretRepository;

if (!defined('WPINC')) {
    die;
}

class ViewManager
{
    private $secrets;

    public function __construct()
    {
        $this->secrets = new SecretRepository();
    }

    public function get_secret($id)
    {
        return $this->secrets->get_secret($id, 'view');
    }

    public function get_secret_meta($id)
    {
        return $this->secrets->get_secret($id, 'metadata');
    }

    public function add_secret($secret, $max_views, $expiration_local, $allow_delete = true)
    {
        return $this->secrets->add_secret($secret, $max_views, $expiration_local, $allow_delete);
    }

    public function delete_secret($id)
    {
        $secret = $this->secrets->get_secret($id, 'metadata');

        if (is_wp_error($secret)) {
            return $secret;
        }

        if (!$secret->allow_delete) {
            return new \WP_Error('delete_not_allowed', __("This secret cannot be manually deleted.", 'runthings-secrets'));
        }

        return $this->secrets->delete_secret($secret);
    }
}

