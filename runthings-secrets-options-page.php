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

include plugin_dir_path(__FILE__) . './library/runthings-secrets-sodium-encryption.php';

class runthings_secrets_Options_Page
{
    private $crypt;

    public function __construct()
    {
        add_action('admin_notices', [$this, 'admin_notices']);
        add_action('admin_menu', [$this, 'options_page']);
        add_action('admin_init', [$this, 'settings_init']);

        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
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
        }

        add_settings_field(
            'runthings_secrets_encryption_key',
            __('Encryption Key', 'runthings-secrets'),
            [$this, 'encryption_key_callback'],
            'runthings-secrets',
            'runthings_secrets_encryption_key_section'
        );

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

    public function encryption_key_section_callback()
    {
        _e('A default encyption key has been generated for you. Add the snippet below to your wp-config.php file to use your own encryption key.', 'runthings-secrets');
        echo " <strong>" . __('Important','runthings-secrets') . ":</strong> ";
        _e('If you change the encryption key, any existing secrets will become unreadable.', 'runthings-secrets');
    }

    public function encryption_key_callback()
    {
        $new_key = $this->crypt->generate_key();
    ?>
        <input type="text" readonly="readonly" value="define('RUNTHINGS_SECRETS_ENCRYPTION_KEY', '<?php echo $new_key; ?>');" onclick="this.select();" style="width: 100%;">
        <p class="description"><?php _e('Note: Refresh the page to generate another key.', 'runthings-secrets'); ?></p>
    <?php
    }

    public function stats_section_callback()
    {
        $total_views_count = get_option('runthings_secrets_stats_total_views', 0);
        $total_secrets_count = get_option('runthings_secrets_stats_total_secrets', 0);

        echo '<p><strong>' . __('Total Secrets Created', 'runthings-secrets') . ':</strong> ' . $total_secrets_count  . '</p>';
        echo '<p><strong>' . __('Total Secrets Viewed', 'runthings-secrets') . ':</strong> ' . $total_views_count  . '</p>';
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
        <script>
            jQuery(document).ready(function($) {
                $('.runthings-secrets-select2').select2();
            });
        </script>
<?php
    }
}

new runthings_secrets_Options_Page();
