document.getElementById('copy-to-clipboard').addEventListener('click', function() {
    const viewingUrlInput = document.getElementById('viewing-url');
    viewingUrlInput.select();
    viewingUrlInput.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand('copy');
});

document.getElementById('viewing-url').addEventListener('click', function() {
    this.select();
});