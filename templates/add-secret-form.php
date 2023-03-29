<form method="post">
    <?php wp_nonce_field('runthings_secrets_add', 'runthings_secrets_add_nonce'); ?>
    <p>
        <label for="secret"><?php _e('Secret:', 'runthings-secrets'); ?></label>
        <textarea name="secret" id="secret" required></textarea>
    </p>
    <p>
        <label for="expiration"><?php _e('Expiration date:', 'runthings-secrets'); ?></label>
        <input type="date" name="expiration" id="expiration" required>
    </p>
    <p>
        <label for="max_views"><?php _e('Maximum number of views:', 'runthings-secrets'); ?></label>
        <input type="number" name="max_views" id="max_views" min="1" max="10" required>
    </p>
    <p>
        <input type="submit" value="<?php _e('Submit', 'runthings-secrets'); ?>">
    </p>
</form>