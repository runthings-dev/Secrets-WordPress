<?php

/**
 * The template for displaying the secret to the user.
 *
 * This template can be overridden by copying it to yourtheme/runthings-secrets/view-secret.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the 
 * theme developer) will need to copy the new files to your theme to maintain 
 * compatibility. We try to do this as little as possible, but it does happen. 
 * When this occurs the version of the template file will be bumped and the 
 * readme will list any important changes.
 *
 * @version 1.1.0
 */
?>
<p><?php esc_html_e('Your secret is:', 'runthings-secrets'); ?></p>
<div class="rs-data-container">
    <textarea class="view-secret rs-data-item" rows="2" readonly><?php echo esc_html($context->secret->secret); ?></textarea>
    <button class="copy-to-clipboard" title="<?php esc_attr_e('Copy to clipboard', 'runthings-secrets'); ?>">
        <?php echo $context->copy_to_clipboard_icon; ?>
    </button>
</div>
<p><?php echo esc_html(sprintf(__('Expiration date: %s', 'runthings-secrets'), $context->secret->formatted_expiration)); ?></p>
<p><?php echo esc_html(sprintf(__('Views left: %s', 'runthings-secrets'), ($context->secret->max_views - $context->secret->views))); ?></p>