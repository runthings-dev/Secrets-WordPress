<?php

namespace RunthingsSecrets;

if (!defined('WPINC')) {
    die;
}

class TemplateChecker
{
    /**
     * Array of templates and their current versions.
     */
    private $plugin_templates = array(
            'add-secret-form.php' => '1.5.0',
            'error.php' => '1.3.0',
            'secret-created.php' => '1.4.0',
            'view-secret.php' => '1.5.0',
        );

        public function __construct()
        {
            add_action('admin_init', array($this, 'check_template_versions'));
        }

        /**
         * Check if the theme has overridden any of the plugin templates and if they are out of date.
         * 
         * @since 1.2.0
         */
        public function check_template_versions()
        {
            $theme_path = get_stylesheet_directory();
            $site_path = ABSPATH;

            $outdated_templates = [];

            foreach ($this->plugin_templates as $template => $version) {
                $theme_file_path = $theme_path . '/runthings-secrets/' . $template;
                if (file_exists($theme_file_path)) {
                    $file_version = $this->get_file_version($theme_file_path);
                    if (version_compare($file_version, $version, '<')) {
                        // Create a server-relative URL
                        $relative_url = str_replace($site_path, '/', $theme_file_path);
                        $outdated_templates[] = [
                            'template' => $relative_url,
                            'current_version' => $version,
                            'user_version' => $file_version
                        ];
                    }
                }
            }

            if (!empty($outdated_templates)) {
                add_action('admin_notices', function () use ($outdated_templates) {
                    echo '<div class="notice notice-warning is-dismissible">';
                    echo '<p>' . esc_html__('The following template overrides are out of date. Please update them to the latest versions:', 'runthings-secrets') . '</p>';
                    echo '<ul>';
                    foreach ($outdated_templates as $template_data) {
                        echo '<li>' . sprintf(
                            /* translators: 1: Template file path, 2: User's version of the template, 3: Current version of the template */
                            esc_html__('Template %1$s is out of date. Your version: %2$s. Current version: %3$s.', 'runthings-secrets'),
                            '<code>' . esc_html($template_data['template']) . '</code>',
                            '<code>' . esc_html($template_data['user_version']) . '</code>',
                            '<code>' . esc_html($template_data['current_version']) . '</code>'
                        ) . '</li>';
                    }
                    echo '</ul>';
                    echo '</div>';
                });
            }
        }

        /**
         * Retrieve metadata from a file. Based on WP Core's get_file_data function, taken from WooCommerce
         *
         * @since  1.2.0
         * @param  string $file Path to the file.
         * @return string
         */
        private function get_file_version($file)
        {
            if (!file_exists($file)) {
                return '';
            }

            // We don't need to write to the file, so just open for reading.
            $fp = fopen($file, 'r'); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
            if (!$fp) {
                return '';
            }

            // Pull only the first 8kiB of the file in.
            $file_data = fread($fp, 8192); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread

            // PHP will close file handle, but we are good citizens.
            fclose($fp); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

            // Make sure we catch CR-only line endings.
            $file_data = str_replace("\r", "\n", $file_data);
            $version = '';

            if (preg_match('/^[ \t\/*#@]*' . preg_quote('@version', '/') . '(.*)$/mi', $file_data, $match) && $match[1]) {
                $version = trim(preg_replace('/\s*(?:\*\/|\?>).*/', '', $match[1]));
            }

            return $version;
        }
    }
