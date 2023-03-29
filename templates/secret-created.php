<?php

// Generate the viewing URL.
$viewing_url = site_url('view-secret/?secret=' . $uuid);
$viewing_link = '<a href="' . $viewing_url . '">' . $viewing_url . '</a>';

?>
<p><?php echo sprintf(__('Your secret sharing URL: %s', 'runthings-secrets'), $viewing_link); ?></p>
