<?php

// Generate the viewing URL.
$view_page_id = get_option('runthings_secrets_view_page');
$viewing_url = get_permalink($view_page_id) . '?secret=' . $uuid;
$viewing_link = '<a href="' . $viewing_url . '">' . $viewing_url . '</a>';
?>
<p><?php echo sprintf(__('Your secret sharing URL: %s', 'runthings-secrets'), $viewing_link); ?></p>
