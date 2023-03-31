<?php
/*
Plugin Name: Secrets
Plugin URI: https://runthings.dev
Description: Share secrets securely
Version: 0.5.0
Author: Matthew Harris, runthings.dev
Author URI: https://runthings.dev/
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Copyright 2023 Matthew Harris

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

class runthings_secrets_Plugin
{
    public function __construct()
    {
        add_action('init', [$this, 'init']);
        add_action('plugins_loaded', [$this, 'load_textdomain']);

        include plugin_dir_path(__FILE__) . 'runthings-secrets-add-secret.php';
        include plugin_dir_path(__FILE__) . 'runthings-secrets-view-secret.php';
        include plugin_dir_path(__FILE__) . 'runthings-secrets-options-page.php';
    }

    public function init()
    {
    }

    public function activate()
    {
        $this->activate_database();
    }

    public function deactivate()
    {
        // delete table(s)
    }

    public function uninstall()
    {
    }

    public function load_textdomain()
    {
        load_plugin_textdomain('runthings-secrets', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    private function activate_database()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'runthings_secrets';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
          id int(11) NOT NULL AUTO_INCREMENT,
          uuid varchar(255) NOT NULL,
          secret text NOT NULL,
          max_views int(11) NOT NULL,
          views int(11) NOT NULL,
          expiration datetime NOT NULL,
          created_at datetime NOT NULL,
          PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function deactivate()
    {
        // delete table(s)
    }

    public function uninstall()
    {
    }

    public function load_textdomain()
    {
        load_plugin_textdomain('runthings-secrets', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}

$runthings_secrets = new runthings_secrets_Plugin();

register_activation_hook(__FILE__, array($runthings_secrets, 'activate'));
