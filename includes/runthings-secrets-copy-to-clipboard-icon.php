<?php
/*
Secrets by runthings.dev

Copyright 2023-2024 Matthew Harris

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

if (!class_exists('runthings_secrets_Copy_To_Clipboard_Icon')) {
    class runthings_secrets_Copy_To_Clipboard_Icon
    {
        public static function get_icon($context, $embed = true)
        {
            $asset_path = RUNTHINGS_SECRETS_PLUGIN_DIR . 'assets/copy-icon.svg';
            $asset_output = '';

            if ($embed) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                WP_Filesystem();
                global $wp_filesystem;

                $asset_output = $wp_filesystem->get_contents($asset_path);
            } else {
                $asset_url = plugin_dir_url(__FILE__) . 'assets/copy-icon.svg';
                $asset_output = '<img src="' . esc_url($asset_url) . '" alt="" />';
            }

            return apply_filters('runthings_secrets_copy_to_clipboard_icon', $asset_output, $context, $embed);
        }

        public static function get_allowed_html($context)
        {
            return apply_filters('runthings_secrets_copy_to_clipboard_icon_allowed_html', array(
                'svg' => array(
                    'xmlns' => array(),
                    'fill' => array(),
                    'viewbox' => array(),
                    'width' => array(),
                    'height' => array(),
                    'class' => array(),
                    'id' => array(),
                    'aria-hidden' => array(),
                    'role' => array(),
                    'style' => array()
                ),
                'img' => array(
                    'src' => array(),
                    'width' => array(),
                    'height' => array(),
                    'alt' => array(),
                    'class' => array(),
                    'id' => array(),
                    'style' => array()
                )
            ), $context);
        }
    }
}
