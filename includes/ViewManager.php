<?php

namespace RunthingsSecrets;

if (!defined('WPINC')) {
    die;
}

class ViewManager
{
    private $secrets;

    public function __construct()
    {
        include_once RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'lib/Secrets.php';
        $this->secrets = new Secrets();
    }

    public function get_secret($uuid)
    {
        return $this->secrets->get_secret($uuid, 'view');
    }

    public function get_secret_meta($uuid)
    {
        return $this->secrets->get_secret($uuid, 'created');
    }

    public function add_secret($secret, $max_views, $expiration_local)
    {
        return $this->secrets->add_secret($secret, $max_views, $expiration_local);
    }
}

