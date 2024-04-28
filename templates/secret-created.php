<?php

/**
 * The template for displaying the 'secret created' message.
 *
 * This template can be overridden by copying it to yourtheme/runthings-secrets/secret-created.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the 
 * theme developer) will need to copy the new files to your theme to maintain 
 * compatibility. We try to do this as little as possible, but it does happen. 
 * When this occurs the version of the template file will be bumped and the 
 * readme will list any important changes.
 *
 * @version 1.1.0
 */

$viewing_snippet = sprintf(
    esc_html__("Get it from %1\$s (valid for %2\$s / %3\$s).", 'runthings-secrets'),
    esc_url($context->viewing_url),
    esc_html($context->secret->days_left),
    esc_html($context->secret->views_left)
);
?>
<p><?php esc_html_e('Your secret sharing link:', 'runthings-secrets'); ?></p>
<div class="rs-data-container">
    <input type="text" class="viewing-url rs-data-item" value="<?php echo esc_url($context->viewing_url); ?>" readonly>
    <button class="copy-to-clipboard" title="<?php esc_attr_e('Copy to clipboard', 'runthings-secrets'); ?>">
        <?php echo wp_kses($context->copy_to_clipboard_link_icon, $context->copy_to_clipboard_link_icon_allowed_html); ?>
    </button>
</div>
<p><?php esc_html_e('Your secret sharing snippet:', 'runthings-secrets'); ?></p>
<div class="rs-data-container">
    <textarea class="viewing-snippet rs-data-item" rows="2" readonly><?php echo esc_html($viewing_snippet); ?></textarea>
    <button class="copy-to-clipboard" title="<?php esc_attr_e('Copy to clipboard', 'runthings-secrets'); ?>">
        <?php echo wp_kses($context->copy_to_clipboard_snippet_icon, $context->copy_to_clipboard_snippet_icon_allowed_html); ?>
    </button>
</div>
<p><?php echo esc_html(sprintf(__('Expiration date: %s', 'runthings-secrets'), $context->secret->formatted_expiration)); ?></p>
<p><?php echo esc_html(sprintf(__('Views left: %s', 'runthings-secrets'), ($context->secret->max_views - $context->secret->views))); ?></p>