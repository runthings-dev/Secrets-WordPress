<?php if($secret->is_error) : ?>
    <p><strong><?php _e('ERROR:', 'runthings-secrets'); ?></strong> <?php echo $secret->error_message; ?></p>
<?php else: ?>
    <p><?php echo sprintf(__('Your secret is: %s', 'runthings-secrets'), $secret->secret); ?></p>
    <p><?php echo sprintf(__('Expiration date: %s', 'runthings-secrets'), date('Y-m-d', strtotime($secret->expiration))); ?></p>
    <p><?php echo sprintf(__('Views left: %s', 'runthings-secrets'), ($secret->max_views - $secret->views)); ?></p>
<?php endif; ?>
