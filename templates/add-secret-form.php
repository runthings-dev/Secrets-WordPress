<?php

/**
 * The template for displaying the 'add secret' form.
 *
 * This template can be overridden by copying it to yourtheme/runthings-secrets/add-secret-form.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the 
 * theme developer) will need to copy the new files to your theme to maintain 
 * compatibility. We try to do this as little as possible, but it does happen. 
 * When this occurs the version of the template file will be bumped and the 
 * readme will list any important changes.
 *
 * @version 1.3.0
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly 
?>
<form method="post" class="add-secret-form">
    <?php wp_nonce_field('runthings_secrets_add', 'runthings_secrets_add_nonce'); ?>
    <div>
        <label for="secret"><?php esc_html_e('Secret:', 'runthings-secrets'); ?></label>
        <textarea name="secret" required></textarea>
    </div>
    <div>
        <label for="expiration"><?php echo sprintf(
                                    /* translators: %s: Timezone string (e.g., "America/New_York") */
                                    esc_html__('Expiration date (Timezone: %s):', 'runthings-secrets'),
                                    esc_html($context->timezone)
                                ); ?></label>
        <input type="date" name="expiration" required min="<?php echo esc_attr($context->minimum_date); ?>" value="<?php echo esc_attr($context->default_expiration); ?>">
    </div>
    <div>
        <label for="max_views"><?php esc_html_e('Maximum number of views:', 'runthings-secrets'); ?></label>
        <input type="number" name="max_views" min="1" max="10" required value="<?php echo esc_attr($context->default_max_views); ?>">
    </div>
    <div>
        <input type="hidden" name="recaptcha_token" id="recaptcha_token">
        <input type="submit" value="<?php esc_attr_e('Submit', 'runthings-secrets'); ?>">
    </div>
</form>