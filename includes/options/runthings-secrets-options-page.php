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

include RUNTHINGS_SECRETS_PLUGIN_DIR_INCLUDES . 'runthings-secrets-sodium-encryption.php';

class runthings_secrets_Options_Page
{
    private $crypt;

    public function __construct()
    {
        add_action('admin_notices', [$this, 'admin_notices']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        add_action('admin_init', [$this, 'delete_all_secrets_check']);
        add_action('admin_init', [$this, 'regenerate_internal_encryption_key_check']);
        add_action('admin_menu', [$this, 'options_page']);
        add_action('admin_init', [$this, 'settings_init']);
        add_action('admin_footer', [$this, 'admin_footer']);

        $this->crypt = runthings_secrets_Sodium_Encryption::get_instance();
    }

    public function admin_notices()
    {
        $add_secret_page = get_option('runthings_secrets_add_page');
        $created_secret_page = get_option('runthings_secrets_created_page');
        $view_secret_page = get_option('runthings_secrets_view_page');
        if (empty($add_secret_page) || empty($created_secret_page) || empty($view_secret_page)) {
            $message = __('Please set the "Add Secret Page", "Created Secret Page" and "View Secret Page" options in the <a href="%s">RunThings Secrets settings</a>.', 'runthings-secrets');
            printf('<div class="notice notice-warning"><p>%s</p></div>', sprintf($message, admin_url('options-general.php?page=runthings-secrets')));
        }
    }

    public function admin_enqueue_scripts()
    {
        wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', [], '4.0.13');
        wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', ['jquery'], '4.0.13', true);
    }

    public function delete_all_secrets_check()
    {
        if (
            isset($_GET['page']) && $_GET['page'] === 'runthings-secrets'
            && isset($_GET['action']) && $_GET['action'] === 'delete_all_secrets'
        ) {
            $this->delete_all_secrets();
        }
    }

    private function delete_all_secrets()
    {
        if (current_user_can('manage_options')) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'runthings_secrets';
            $wpdb->query("DELETE FROM {$table_name}");
            add_action('admin_notices', [$this, 'deleted_secrets_notice']);
        }
    }

    public function deleted_secrets_notice()
    {
        $message = __('All secrets have been deleted.', 'runthings-secrets');
        printf('<div class="notice notice-success is-dismissible"><p>%s</p></div>', $message);
    }


    public function regenerate_internal_encryption_key_check()
    {
        if (
            isset($_GET['page']) && $_GET['page'] === 'runthings-secrets'
            && isset($_GET['action']) && $_GET['action'] === 'regenerate_internal_encryption_key'
        ) {
            $this->regenerate_internal_encryption_key();
        }
    }

    private function regenerate_internal_encryption_key()
    {
        if (current_user_can('manage_options')) {
            $this->crypt->generate_and_store_key();
            add_action('admin_notices', [$this, 'regenerate_internal_encryption_key_notice']);
        }
    }

    public function regenerate_internal_encryption_key_notice()
    {
        $message = __('Internal encryption key has been regenerated. Consider using the delete all secrets feature to clear out old secrets which are no longer decipherable.', 'runthings-secrets');
        printf('<div class="notice notice-success is-dismissible"><p>%s</p></div>', $message);
    }

    public function options_page()
    {
        add_options_page(
            __('RunThings Secrets', 'runthings-secrets'),
            __('RunThings Secrets', 'runthings-secrets'),
            'manage_options',
            'runthings-secrets',
            [$this, 'options_page_callback']
        );
    }

    public function options_page_callback()
    {
?>
        <div class="wrap">
            <h1><?php _e('RunThings Secrets Settings', 'runthings-secrets'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('runthings-secrets-settings');
                do_settings_sections('runthings-secrets');
                submit_button();
                ?>
            </form>
        </div>
    <?php
    }

    public function settings_init()
    {
        add_settings_section(
            'runthings_secrets_pages_section',
            __('Secret Pages', 'runthings-secrets'),
            [$this, 'pages_section_callback'],
            'runthings-secrets'
        );

        add_settings_field(
            'runthings_secrets_add_page',
            __('Add Secret Page', 'runthings-secrets'),
            [$this, 'add_page_callback'],
            'runthings-secrets',
            'runthings_secrets_pages_section'
        );

        add_settings_field(
            'runthings_secrets_created_page',
            __('Secret Created Page', 'runthings-secrets'),
            [$this, 'created_page_callback'],
            'runthings-secrets',
            'runthings_secrets_pages_section'
        );

        add_settings_field(
            'runthings_secrets_view_page',
            __('View Secret Page', 'runthings-secrets'),
            [$this, 'view_page_callback'],
            'runthings-secrets',
            'runthings_secrets_pages_section'
        );

        add_settings_section(
            'runthings_secrets_spam_protection_section',
            __('Spam Protection', 'runthings-secrets'),
            [$this, 'spam_protection_section_callback'],
            'runthings-secrets'
        );

        add_settings_field(
            'runthings_secrets_recaptcha_enabled',
            __('Enable reCAPTCHA v3', 'runthings-secrets'),
            [$this, 'recaptcha_enabled_callback'],
            'runthings-secrets',
            'runthings_secrets_spam_protection_section'
        );

        add_settings_field(
            'runthings_secrets_recaptcha_public_key',
            __('reCAPTCHA v3 Public Key', 'runthings-secrets'),
            [$this, 'recaptcha_public_key_callback'],
            'runthings-secrets',
            'runthings_secrets_spam_protection_section'
        );

        add_settings_field(
            'runthings_secrets_recaptcha_private_key',
            __('reCAPTCHA v3 Private Key', 'runthings-secrets'),
            [$this, 'recaptcha_private_key_callback'],
            'runthings-secrets',
            'runthings_secrets_spam_protection_section'
        );

        add_settings_field(
            'runthings_secrets_recaptcha_score',
            __('reCAPTCHA v3 Score', 'runthings-secrets'),
            [$this, 'recaptcha_score_callback'],
            'runthings-secrets',
            'runthings_secrets_spam_protection_section'
        );

        if ($this->crypt->is_sodium_enabled()) {
            add_settings_section(
                'runthings_secrets_encryption_key_section',
                __('Encryption Key', 'runthings-secrets'),
                [$this, 'encryption_key_section_callback'],
                'runthings-secrets'
            );

            add_settings_field(
                'runthings_secrets_encryption_key',
                __('Encryption Key', 'runthings-secrets'),
                [$this, 'encryption_key_callback'],
                'runthings-secrets',
                'runthings_secrets_encryption_key_section'
            );

            add_settings_field(
                'runthings_secrets_internal_encryption_key',
                __('Internal Encryption Key', 'runthings-secrets'),
                [$this, 'internal_encryption_key_callback'],
                'runthings-secrets',
                'runthings_secrets_encryption_key_section'
            );

            add_settings_field(
                'runthings_secrets_encryption_key_method',
                __('Current Encryption Method', 'runthings-secrets'),
                [$this, 'encryption_key_method_callback'],
                'runthings-secrets',
                'runthings_secrets_encryption_key_section'
            );
        }

        add_settings_section(
            'runthings_secrets_advanced_section',
            __('Advanced', 'runthings-secrets'),
            [$this, 'enqueue_advanced_section_callback'],
            'runthings-secrets'
        );

        add_settings_field(
            'runthings_secrets_enqueue_form_styles',
            __('Enqueue Form Styles', 'runthings-secrets'),
            [$this, 'enqueue_stylesheet_callback'],
            'runthings-secrets',
            'runthings_secrets_advanced_section'
        );

        add_settings_field(
            'runthings_secrets_delete_all_secrets',
            __('Delete All Secrets', 'runthings-secrets'),
            [$this, 'delete_all_secrets_callback'],
            'runthings-secrets',
            'runthings_secrets_advanced_section'
        );

        add_settings_section(
            'runthings_secrets_stats_section',
            __('Statistics', 'runthings-secrets'),
            [$this, 'stats_section_callback'],
            'runthings-secrets'
        );

        register_setting(
            'runthings-secrets-settings',
            'runthings_secrets_add_page',
            'intval'
        );

        register_setting(
            'runthings-secrets-settings',
            'runthings_secrets_created_page',
            'intval'
        );

        register_setting(
            'runthings-secrets-settings',
            'runthings_secrets_view_page',
            'intval'
        );

        register_setting(
            'runthings-secrets-settings',
            'runthings_secrets_recaptcha_enabled',
            'intval'
        );

        register_setting(
            'runthings-secrets-settings',
            'runthings_secrets_recaptcha_public_key',
            'sanitize_text_field'
        );

        register_setting(
            'runthings-secrets-settings',
            'runthings_secrets_recaptcha_private_key',
            'sanitize_text_field'
        );

        register_setting(
            'runthings-secrets-settings',
            'runthings_secrets_recaptcha_score',
            array(
                'type' => 'float',
                'default' => 0.5,
                'sanitize_callback' => [$this, 'validate_float']
            )
        );

        register_setting(
            'runthings-secrets-settings',
            'runthings_secrets_enqueue_form_styles',
            array(
                'type' => 'boolean',
                'default' => 1
            )
        );
    }

    public function spam_protection_section_callback()
    {
        echo '<p>' . __('Protect your secrets from spam by enabling reCAPTCHA v3.', 'runthings-secrets') . '</p>';
        echo '<p>' . __('Get your reCAPTCHA v3 keys <a target="_blank" href="https://www.google.com/recaptcha/admin/create">here</a>.', 'runthings-secrets') . '</p>';
    }

    public function recaptcha_enabled_callback()
    {
        $recaptcha_enabled = get_option('runthings_secrets_recaptcha_enabled');
        echo '<input type="checkbox" name="runthings_secrets_recaptcha_enabled" value="1" ' . checked(1, $recaptcha_enabled, false) . ' />';
    }

    public function recaptcha_public_key_callback()
    {
        $recaptcha_public_key = get_option('runthings_secrets_recaptcha_public_key');
        echo '<input type="text" class="regular-text" name="runthings_secrets_recaptcha_public_key" value="' . esc_attr($recaptcha_public_key) . '" />';
    }

    public function recaptcha_private_key_callback()
    {
        $recaptcha_private_key = get_option('runthings_secrets_recaptcha_private_key');
        echo '<input type="text" class="regular-text" name="runthings_secrets_recaptcha_private_key" value="' . esc_attr($recaptcha_private_key) . '" />';
    }

    public function recaptcha_score_callback()
    {
        $recaptcha_score = get_option('runthings_secrets_recaptcha_score', 0.5);
        echo '<input type="text" class="regular-text" name="runthings_secrets_recaptcha_score" value="' . esc_attr($recaptcha_score) . '" />';
        echo '<p class="description">' . __('Set the reCAPTCHA v3 score threshold (0 to 1). A lower value is less strict, a higher value is more strict.', 'runthings-secrets') . '</p>';
    }

    public function pages_section_callback()
    {
        echo '<p>' . __('Select the WordPress pages to use for adding and viewing secrets.', 'runthings-secrets') . '</p>';
    }

    public function add_page_callback()
    {
        $add_page_id = get_option('runthings_secrets_add_page');
        echo '<select name="runthings_secrets_add_page" class="runthings-secrets-select2">';
        echo '<option value="">' . __('(no page selected)', 'runthings-secrets') . '</option>';
        $pages = get_pages();
        foreach ($pages as $page) {
            $selected = ($add_page_id == $page->ID) ? 'selected="selected"' : '';
            echo '<option value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . '</option>';
        }
        echo '</select>';
    }

    public function created_page_callback()
    {
        $created_page_id = get_option('runthings_secrets_created_page');
        echo '<select name="runthings_secrets_created_page" class="runthings-secrets-select2">';
        echo '<option value="">' . __('(no page selected)', 'runthings-secrets') . '</option>';
        $pages = get_pages();
        foreach ($pages as $page) {
            $selected = ($created_page_id == $page->ID) ? 'selected="selected"' : '';
            echo '<option value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . '</option>';
        }
        echo '</select>';
    }

    public function view_page_callback()
    {
        $view_page_id = get_option('runthings_secrets_view_page');
        echo '<select name="runthings_secrets_view_page" class="runthings-secrets-select2">';
        echo '<option value="">' . __('(no page selected)', 'runthings-secrets') . '</option>';
        $pages = get_pages();
        foreach ($pages as $page) {
            $selected = ($view_page_id == $page->ID) ? 'selected="selected"' : '';
            echo '<option value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . '</option>';
        }
        echo '</select>';
    }

    public function enqueue_advanced_section_callback()
    {
    }

    public function enqueue_stylesheet_callback()
    {
        $enqueue_stylesheet = get_option('runthings_secrets_enqueue_form_styles', 1);
        echo '<input type="checkbox" name="runthings_secrets_enqueue_form_styles" value="1" ' . checked(1, $enqueue_stylesheet, false) . ' />';
        echo '<span class="description"> ' . __('Enqueue the stylesheet for the \'add secret\' form.', 'runthings-secrets') . '</span>';
    }

    public function delete_all_secrets_callback()
    {
        $url = admin_url('options-general.php?page=runthings-secrets&action=delete_all_secrets');
        $confirm_message = __('Are you sure you want to delete all secrets? This action cannot be undone.', 'runthings-secrets');
        echo '<a href="' . $url . '" class="button delete-all-secrets" onclick="return confirm(\'' . esc_js($confirm_message) . '\');">' . __('Delete All Secrets', 'runthings-secrets') . '</a>';
    }

    public function encryption_key_section_callback()
    {
        echo "<p>" . __('The plugin has generated a default internal encryption key automatically, and stored it as a WordPress option.', 'runthings-secrets') . "</p>";
        echo "<p>" . __('You can optionally override this using the snippet below in your wp-config.php. This lets you store the key in an environment variable, or 3rd-party key storage service.', 'runthings-secrets') . "</p>";
        echo "<p><strong>" . __('Important', 'runthings-secrets') . ":</strong> " . __('If you change the encryption key, any existing secrets will become unreadable. You should then use the "Delete All Secrets" feature to clear out the database, or users will see garbled text when they view their secrets.', 'runthings-secrets') . "</p>";
    }

    public function encryption_key_callback()
    {
        $new_key = $this->crypt->generate_key();
    ?>
        <input type="text" readonly="readonly" value="define('RUNTHINGS_SECRETS_ENCRYPTION_KEY', '<?php echo $new_key; ?>');" onclick="this.select();" style="width: 100%;">
        <p class="description"><?php _e('Refresh the page to generate another key.', 'runthings-secrets'); ?></p>
    <?php
    }

    public function internal_encryption_key_callback()
    {
        $url = admin_url('options-general.php?page=runthings-secrets&action=regenerate_internal_encryption_key');
        $confirm_message = __('Are you sure you want to regenerate the internal encryption key? This action cannot be undone.', 'runthings-secrets');
        echo '<a href="' . $url . '" class="button delete-all-secrets" onclick="return confirm(\'' . esc_js($confirm_message) . '\');">' . __('Regenerate Internal Key', 'runthings-secrets') . '</a>';
        echo '<p class="description"> ' . __('The internal encryption key is used if you haven\'t specified one using the define() method above.', 'runthings-secrets') . '</p>';
    }

    public function encryption_key_method_callback()
    {
        echo $this->crypt->get_key_method();
    }

    public function stats_section_callback()
    {
        $total_views_count = get_option('runthings_secrets_stats_total_views', 0);
        $total_secrets_count = get_option('runthings_secrets_stats_total_secrets', 0);
        $secrets_in_database = $this->get_secrets_in_database();
    ?>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php _e('Total Secrets Created', 'runthings-secrets'); ?></th>
                    <td><?php echo $total_secrets_count; ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Total Secrets Viewed', 'runthings-secrets'); ?></th>
                    <td><?php echo $total_views_count; ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Secrets In Database', 'runthings-secrets') ?></th>
                    <td><?php echo $secrets_in_database; ?></td>
                </tr>
            </tbody>
        </table>
    <?php
    }


    public function validate_float($input)
    {
        $value = floatval($input);
        if ($value < 0) {
            $value = 0;
        } elseif ($value > 1) {
            $value = 1;
        }
        return $value;
    }

    public function admin_footer()
    {
    ?>
        <style>
            .wp-core-ui .button.delete-all-secrets {
                background-color: #e03c3c;
                border-color: #dc3232;
                color: #fff;
                text-decoration: none;
            }

            .wp-core-ui .button.delete-all-secrets:hover,
            .wp-core-ui .button.delete-all-secrets:focus {
                background-color: #c03232;
                border-color: #a82828;
                color: #fff;
            }
        </style>
        <script>
            jQuery(document).ready(function($) {
                $('.runthings-secrets-select2').select2();
            });
        </script>
<?php
    }

    private function get_secrets_in_database()
    {
        global $wpdb;
        $secrets_table = $wpdb->prefix . 'runthings_secrets';

        $current_secrets_count = $wpdb->get_var("SELECT COUNT(*) FROM $secrets_table");

        return $current_secrets_count;
    }
}

new runthings_secrets_Options_Page();
