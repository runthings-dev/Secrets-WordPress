<p><?php echo sprintf(__('Your secret is: %s', 'runthings-secrets'), $secret->secret); ?></p>
<p><?php echo sprintf(__('Expiration date: %s', 'runthings-secrets'), date('Y-m-d', strtotime($secret->expiration))); ?></p>
<p><?php echo sprintf(__('Views left: %s', 'runthings-secrets'), ($secret->max_views - $secret->views)); ?></p>