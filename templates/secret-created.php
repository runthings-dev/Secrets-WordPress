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
 * @version 1.0.0
 */

$viewing_snippet = sprintf(
    __("Get it from %1\$s (valid for %2\$s / %3\$s).", 'runthings-secrets'),
    $context->viewing_url,
    $context->secret->days_left,
    $context->secret->views_left
);
?>
<p><?php _e('Your secret sharing link:', 'runthings-secrets'); ?></p>
<div class="rs-data-container">
    <input type="text" class="viewing-url rs-data-item" value="<?php echo esc_attr($context->viewing_url); ?>" readonly>
    <button class="copy-to-clipboard" title="<?php esc_attr_e('Copy to clipboard', 'runthings-secrets'); ?>">
        <?php echo $context->copy_to_clipboard_link_icon; ?>
    </button>
</div>
<p><?php _e('Your secret sharing snippet:', 'runthings-secrets'); ?></p>
<div class="rs-data-container">
    <textarea class="viewing-snippet rs-data-item" rows="2" readonly><?php echo esc_html($viewing_snippet); ?></textarea>
    <button class="copy-to-clipboard" title="<?php esc_attr_e('Copy to clipboard', 'runthings-secrets'); ?>">
        <?php echo $context->copy_to_clipboard_snippet_icon; ?>
    </button>
</div>
<p><?php echo sprintf(__('Expiration date: %s', 'runthings-secrets'), $context->secret->formatted_expiration); ?></p>
<p><?php echo sprintf(__('Views left: %s', 'runthings-secrets'), ($context->secret->max_views - $context->secret->views)); ?></p>