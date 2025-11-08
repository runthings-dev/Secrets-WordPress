<?php

namespace RunthingsSecrets;

if (!defined('WPINC')) {
    die;
}

class CopyToClipboardIcon
{
    public static function get_icon($context, $embed = true)
        {
            $asset_path = RUNTHINGS_SECRETS_PLUGIN_DIR . 'assets/copy-icon.svg';
            $asset_output = '';

            if ($embed) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                WP_Filesystem();
                global $wp_filesystem;

                if ($wp_filesystem->exists($asset_path)) {
                    $asset_output = $wp_filesystem->get_contents($asset_path);
                    if (false === $asset_output) {
                        // File exists but couldn't be read, fallback to img tag
                        $asset_output = '';
                    }
                } else {
                    // File doesn't exist, fallback to img tag
                    $asset_output = '';
                }
            }

            // If we don't have SVG content (either embed=false or file read failed), create img tag
            if (empty($asset_output)) {
                $asset_url = plugin_dir_url(__FILE__) . 'assets/copy-icon.svg';
                // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
                // This is a plugin asset, not a media library attachment, so wp_get_attachment_image() is not applicable
                $asset_output = '<img src="' . esc_url($asset_url) . '" alt="' . esc_attr(__('Copy to clipboard', 'runthings-secrets')) . '" />';
                // phpcs:enable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
            }

            return apply_filters('runthings_secrets_copy_to_clipboard_icon', $asset_output, $context, $embed);
        }

        public static function get_allowed_html($context)
        {
            return apply_filters('runthings_secrets_copy_to_clipboard_icon_allowed_html', self::kses_allowed_html(), $context);
        }

        private static function kses_allowed_html()
        {
            return array(
                'img' => array(
                    'src' => true,
                    'width' => true,
                    'height' => true,
                    'alt' => true,
                    'class' => true,
                    'id' => true,
                    'style' => true
                ),
                'svg' => array(
                    'version' => true,
                    'class' => true,
                    'fill' => true,
                    'height' => true,
                    'xml:space' => true,
                    'xmlns' => true,
                    'xmlns:xlink' => true,
                    'viewbox' => true,
                    'enable-background' => true,
                    'width' => true,
                    'x' => true,
                    'y' => true,
                    'stroke' => true,
                    'stroke-width' => true,
                    'stroke-linecap' => true,
                    'stroke-linejoin' => true,
                ),
                'path' => array(
                    'clip-rule' => true,
                    'd' => true,
                    'fill' => true,
                    'fill-rule' => true,
                    'stroke' => true,
                    'stroke-width' => true,
                ),
                'g' => array(
                    'class' => true,
                    'clip-rule' => true,
                    'd' => true,
                    'transform' => true,
                    'fill' => true,
                    'fill-rule' => true,
                    'stroke' => true,
                    'stroke-width' => true,
                ),
                'rect' => array(
                    'clip-rule' => true,
                    'd' => true,
                    'x' => true,
                    'y' => true,
                    'rx' => true,
                    'ry' => true,
                    'transform' => true,
                    'fill' => true,
                    'fill-rule' => true,
                    'stroke' => true,
                    'stroke-width' => true,
                    'width' => true,
                    'height' => true,
                ),
                'polygon' => array(
                    'clip-rule' => true,
                    'd' => true,
                    'fill' => true,
                    'fill-rule' => true,
                    'stroke' => true,
                    'stroke-width' => true,
                    'points' => true,
                ),
                'circle' => array(
                    'clip-rule' => true,
                    'd' => true,
                    'fill' => true,
                    'fill-rule' => true,
                    'stroke' => true,
                    'stroke-width' => true,
                    'cx' => true,
                    'cy' => true,
                    'r' => true,
                ),
                'lineargradient' => array(
                    'id' => true,
                    'gradientunits' => true,
                    'x' => true,
                    'y' => true,
                    'x2' => true,
                    'y2' => true,
                    'gradienttransform' => true,
                ),
                'stop' => array(
                    'offset' => true,
                    'style' => true,
                ),
                'image' => array(
                    'height' => true,
                    'width' => true,
                    'xlink:href' => true,
                ),
                'defs' => array(
                    'clipPath' => true,
                ),
            );
        }
    }
