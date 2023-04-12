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
<p><?php echo sprintf(__('Your secret is: %s', 'runthings-secrets'), $context->secret->secret); ?></p>
<p><?php echo sprintf(__('Expiration date: %s', 'runthings-secrets'), date('Y-m-d', strtotime($context->secret->expiration))); ?></p>
<p><?php echo sprintf(__('Views left: %s', 'runthings-secrets'), ($context->secret->max_views - $context->secret->views)); ?></p>