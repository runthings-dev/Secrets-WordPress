<?php
  $default_expiration = date('Y-m-d', strtotime('+7 days'));
  $default_max_views = 5;
?>
<form method="post">
    <?php wp_nonce_field('runthings_secrets_add', 'runthings_secrets_add_nonce'); ?>
    <p>
        <label for="secret"><?php _e('Secret:', 'runthings-secrets'); ?></label>
        <textarea name="secret" required></textarea>
    </p>
    <p>
        <label for="expiration"><?php _e('Expiration date:', 'runthings-secrets'); ?></label>
        <input type="date" name="expiration" required value="<?php echo esc_attr($default_expiration); ?>">
    </p>
    <p>
        <label for="max_views"><?php _e('Maximum number of views:', 'runthings-secrets'); ?></label>
        <input type="number" name="max_views" min="1" max="10" required value="<?php echo esc_attr($default_max_views); ?>">
    </p>
    <p>
        <input type="submit" value="<?php _e('Submit', 'runthings-secrets'); ?>">
    </p>
</form>