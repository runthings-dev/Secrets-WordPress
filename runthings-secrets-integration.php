<?php
/*
Secrets by runthings.dev

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

class runthings_secrets_Integration
{
    public function __construct()
    {
        // create renderers
        include plugin_dir_path(__FILE__) . 'render/runthings-secrets-add-secret.php';
        include plugin_dir_path(__FILE__) . 'render/runthings-secrets-secret-created.php';
        include plugin_dir_path(__FILE__) . 'render/runthings-secrets-view-secret.php';

        $add_secret = new runthings_secrets_Add_Secret();
        $secret_created = new runthings_secrets_Secret_Created();
        $view_secret = new runthings_secrets_View_Secret();

        // integrate shortcodes
        include plugin_dir_path(__FILE__) . 'integration/shortcode/runthings-secrets-shortcodes.php';
        new runthings_secrets_Shortcodes_Integration($add_secret, $secret_created, $view_secret);

        // integrate blocks
        include plugin_dir_path(__FILE__) . 'integration/block-editor/runthings-secrets-blocks.php';
        new runthings_secrets_Blocks_Integration($add_secret, $secret_created, $view_secret);

        // integrate elementor widgets
        // todo
    }
}

new runthings_secrets_Integration();