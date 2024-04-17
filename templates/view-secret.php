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
 * @version 1.0.0
 */
?>
<p><?php _e('Your secret is:', 'runthings-secrets'); ?></p>
<div class="rs-data-container">
    <textarea class="view-secret rs-data-item" rows="2" readonly><?php echo esc_html($context->secret->secret); ?></textarea>
    <button class="copy-to-clipboard" title="<?php esc_attr_e('Copy to clipboard', 'runthings-secrets'); ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2c2c2c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 4h2a2 2 0 012 2v4M8 4H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2v-2" />
            <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
            <path d="M21 14H11" />
            <path d="M15 10l-4 4 4 4" />
        </svg>
    </button>
</div>
<p><?php echo sprintf(__('Expiration date: %s', 'runthings-secrets'), date('Y-m-d', strtotime($context->secret->expiration))); ?></p>
<p><?php echo sprintf(__('Views left: %s', 'runthings-secrets'), ($context->secret->max_views - $context->secret->views)); ?></p>