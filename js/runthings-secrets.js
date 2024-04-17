const copyToClipboardButtons = document.querySelectorAll('.copy-to-clipboard');

copyToClipboardButtons.forEach((button) => {
  button.addEventListener('click', function () {
    const dataItemInput = button.previousElementSibling;

    if (navigator.clipboard && navigator.clipboard.writeText) {
      const textToCopy = dataItemInput.value;
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
      dataItemInput.select();
      dataItemInput.setSelectionRange(0, 99999); // For mobile devices

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

const dataItemInputs = document.querySelectorAll('.rs-data-item');

dataItemInputs.forEach((input) => {
  input.addEventListener('click', (event) => {
    event.target.select();
  });
});
