const copyToClipboardButtons = document.querySelectorAll('.copy-to-clipboard');

copyToClipboardButtons.forEach((button) => {
  button.addEventListener('click', function () {
    const viewingUrlInput = button.previousElementSibling;

    if (navigator.clipboard && navigator.clipboard.writeText) {
      const textToCopy = viewingUrlInput.value;
      navigator.clipboard
        .writeText(textToCopy)
        .then(() => {
          console.log('Text copied to clipboard');
        })
        .catch((err) => {
          console.error('Failed to copy text: ', err);
        });
    } else {
      // Fallback for older browsers
      viewingUrlInput.select();
      viewingUrlInput.setSelectionRange(0, 99999); // For mobile devices

      try {
        const successful = document.execCommand('copy');
        if (successful) {
          console.log('Text copied to clipboard');
        } else {
          console.error('Failed to copy text');
        }
      } catch (err) {
        console.error('Failed to copy text: ', err);
      }
    }
  });
});

const viewingUrlInputs = document.querySelectorAll(
  '.viewing-url, .viewing-snippet, .view-secret'
);

viewingUrlInputs.forEach((input) => {
  input.addEventListener('click', function () {
    this.select();
  });
});
