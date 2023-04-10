<?php
// Generate the viewing URL.
$view_page_id = get_option('runthings_secrets_view_page');
$viewing_url = get_permalink($view_page_id) . '?secret=' . $secret;
?>
<p><?php _e('Your secret sharing URL:', 'runthings-secrets'); ?></p>
<div class="url-container">
    <input type="text" id="viewing-url" value="<?php echo esc_attr($viewing_url); ?>" readonly>
    <button id="copy-to-clipboard" title="<?php esc_attr_e('Copy to clipboard', 'runthings-secrets'); ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 4h2a2 2 0 012 2v4M8 4H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2v-2" />
            <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
            <path d="M21 14H11" />
            <path d="M15 10l-4 4 4 4" />
        </svg>
    </button>
</div>
<style>
    .url-container {
        display: flex;
    }
    #viewing-url {
        flex-grow: 1;
        padding: 5px;
        cursor: pointer;
    }

    button#copy-to-clipboard {
        display: flex;
        flex-direction: column;
        justify-content: center;
        background-color: transparent;
        border: none;
        cursor: pointer;
    }
</style>
<script>
    document.getElementById('copy-to-clipboard').addEventListener('click', function() {
        const viewingUrlInput = document.getElementById('viewing-url');
        viewingUrlInput.select();
        viewingUrlInput.setSelectionRange(0, 99999); // For mobile devices
        document.execCommand('copy');
    });

    document.getElementById('viewing-url').addEventListener('click', function() {
        this.select();
    });
</script>