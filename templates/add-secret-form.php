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
 * @version 1.5.0
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly 
?>
<form method="post" class="add-secret-form">
    <?php wp_nonce_field('runthings_secrets_add', 'runthings_secrets_add_nonce'); ?>
    <div class="form-row">
        <label for="secret"><?php esc_html_e('Secret:', 'runthings-secrets'); ?></label>
        <textarea name="secret" required></textarea>
    </div>
    <div class="form-row">
        <label for="expiration"><?php echo sprintf(
                                    /* translators: %s: Timezone string (e.g., "America/New_York") */
                                    esc_html__('Expiration date (Timezone: %s):', 'runthings-secrets'),
                                    esc_html($context->timezone)
                                ); ?></label>
        <input type="date" name="expiration" required min="<?php echo esc_attr($context->minimum_date); ?>" value="<?php echo esc_attr($context->default_expiration); ?>" data-warning-date="<?php echo esc_attr($context->expiration_warning_date); ?>">
        <?php if (apply_filters('runthings_secrets_show_expiration_warning', true)): ?>
        <div class="expiration-warning" style="display: none;">
            <?php esc_html_e('Warning: This expiration date is more than 6 months away. For better security, consider setting a shorter timeframe.', 'runthings-secrets'); ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="form-row">
        <label for="max_views"><?php esc_html_e('Maximum number of views:', 'runthings-secrets'); ?></label>
        <input type="number" name="max_views" min="1" required value="<?php echo esc_attr($context->default_max_views); ?>" data-warning-threshold="<?php echo esc_attr($context->max_views_warning_threshold); ?>">
        <?php if (apply_filters('runthings_secrets_show_max_views_warning', true)): ?>
        <div class="max-views-warning" style="display: none;">
            <?php esc_html_e('Warning: High view counts increase the risk of unauthorized access. Consider if this many views are really necessary.', 'runthings-secrets'); ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="form-row form-row-checkbox">
        <label>
            <input type="checkbox" name="allow_delete" value="1" checked>
            <?php esc_html_e('Allow manual deletion of this secret', 'runthings-secrets'); ?>
            <span class="help-tip" data-tippy-content="<?php esc_attr_e('When enabled, you can manually delete this secret before it expires. When disabled, the secret can only be removed by expiration or reaching maximum views.', 'runthings-secrets'); ?>">?</span>
        </label>
    </div>
    <div>
        <input type="hidden" name="recaptcha_token" id="recaptcha_token">
        <input type="submit" value="<?php esc_attr_e('Submit', 'runthings-secrets'); ?>">
    </div>
</form>