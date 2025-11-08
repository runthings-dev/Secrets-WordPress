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

    public function get_secret($id)
    {
        return $this->secrets->get_secret($id, 'view');
    }

    public function get_secret_meta($id)
    {
        return $this->secrets->get_secret($id, 'created');
    }

    public function add_secret($secret, $max_views, $expiration_local, $allow_delete = true)
    {
        return $this->secrets->add_secret($secret, $max_views, $expiration_local, $allow_delete);
    }
}

