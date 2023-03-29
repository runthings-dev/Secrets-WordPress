<?php if($secret->is_error) : ?>
    <p><strong><?php _e('ERROR:', 'runthings-secrets'); ?></strong> <?php echo $secret->error_message; ?></p>
<?php else: ?>
    <p><?php echo sprintf(__('Your secret is: %s', 'runthings-secrets'), $secret->secret); ?></p>
<?php endif; ?>
