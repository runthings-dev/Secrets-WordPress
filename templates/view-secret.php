<?php if($secret->is_error) : ?>
    <p><strong>ERROR:</strong> <?php echo $secret->error_message; ?></p>
<?php else: ?>
    <p>Your secret is: <?php echo $secret->secret; ?></p>
<?php endif; ?>
